<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

use Neo\SuperAgentBundle\Model\ConnectionModel;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Proof that a visitor is the guest of a reservation, for connections whose
 * template describes it ("guest_check", see mcp-templates/README.md).
 *
 * A website visitor is anonymous. The tools of a booking system show and
 * change the data of whoever's reservation they are given, so the model must
 * never see a reservation before the visitor proved it is theirs, and never act
 * on another one afterwards. Rules in a skill do not stop a visitor who talks
 * the model into it; this class does:
 *
 *  - verify_guest (a tool of its own): the server looks the reservation up with
 *    the template's lookup tools and compares name and contact (e-mail or phone)
 *    with the persons stored on it; the model gets only "verified" or "not
 *    verified", never the stored data. The same person must match both. Five
 *    misses in 24 hours (per IP address and assistant) lock the check.
 *  - the protected tools of the template run only after that, and only for the
 *    verified records: every argument that names a record must name one of
 *    them (or one that came back with them, e.g. a folio of the reservation);
 *    a result that does not mention one of them is not passed on.
 *
 * A record a tool of "creates" makes in the conversation (e.g. a new booking)
 * counts as verified: the visitor gave the details themselves.
 *
 * What was verified travels with the conversation as a signed token (the
 * widget keeps it with the conversation and sends it back); it expires after
 * two hours. Nothing is stored on the server but the count of misses, under a
 * hash of IP address and assistant, for 24 hours.
 */
final class GuestCheck
{
    public const TOOL = 'verify_guest';

    private const TTL = 7200;

    private const MISSES = 5;

    private const LOCK = 86400;

    /** @var array<int, array{ids: list<string>, exp: int}> connection id => verified records and the ids that came back with them */
    private array $scope = [];

    private int $agentId = 0;

    private bool $changed = false;

    public function __construct(
        #[Autowire('%kernel.secret%')]
        private readonly string $secret,
        private readonly CacheItemPoolInterface $cache,
        private readonly RequestStack $requests,
    ) {
    }

    /** Takes over what the token of the conversation says was verified (nothing when it is not valid). */
    public function load(int $agentId, string $token): void
    {
        $this->agentId = $agentId;
        $this->scope = [];
        $this->changed = false;

        $parts = explode('.', $token, 2);

        if (2 !== \count($parts) || !hash_equals($this->sign($parts[0]), $parts[1])) {
            return;
        }

        $data = json_decode((string) base64_decode(strtr($parts[0], '-_', '+/'), true), true);

        if (!\is_array($data) || (int) ($data['a'] ?? 0) !== $agentId) {
            return;
        }

        foreach ((array) ($data['s'] ?? []) as $connection => $entry) {
            $exp = (int) ($entry['e'] ?? 0);

            if ($exp > time()) {
                $this->scope[(int) $connection] = ['ids' => array_values(array_map('strval', (array) ($entry['i'] ?? []))), 'exp' => $exp];
            }
        }
    }

    /** The token for the widget; '' when nothing is verified. */
    public function token(): string
    {
        if ([] === $this->scope) {
            return '';
        }

        $data = ['a' => $this->agentId, 's' => []];

        foreach ($this->scope as $connection => $entry) {
            $data['s'][$connection] = ['i' => $entry['ids'], 'e' => $entry['exp']];
        }

        $payload = rtrim(strtr(base64_encode((string) json_encode($data)), '+/', '-_'), '=');

        return $payload.'.'.$this->sign($payload);
    }

    /** Whether verify_guest, a protected call or a result changed what is verified. */
    public function changed(): bool
    {
        return $this->changed;
    }

    public function isProtected(McpTemplate $template, string $tool): bool
    {
        return self::matchesAny((array) ($template->guestCheck['protected'] ?? []), $tool);
    }

    /** Whether the tool makes a record the visitor then owns (guest_check.creates). */
    public function isCreating(McpTemplate $template, string $tool): bool
    {
        return self::matchesAny((array) ($template->guestCheck['creates'] ?? []), $tool);
    }

    /**
     * After a successful call of a creating tool: the records in its result
     * (ids of objects with the id key, and their group) count as verified.
     */
    public function created(ConnectionModel $connection, McpTemplate $template, string $text): void
    {
        $config = $template->guestCheck;
        $data = json_decode($text, true);

        if (!\is_array($data)) {
            return;
        }

        $ids = [];
        self::createdIds($data, $config, $ids);
        $ids = array_values(array_unique(array_filter($ids)));

        if ([] === $ids) {
            return;
        }

        $current = $this->scope[(int) $connection->id]['ids'] ?? [];
        $this->scope[(int) $connection->id] = ['ids' => array_values(array_unique([...$current, ...$ids])), 'exp' => time() + self::TTL];
        $this->changed = true;
    }

    /** @return array<string, mixed> definition of verify_guest for the provider */
    public function definition(string $function, string $connectionName): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $function,
                'description' => mb_substr(sprintf('[%s] Checks that the visitor is the guest of a reservation before anything of it is shown or changed: the server compares the name and the e-mail address or phone number the visitor gave with the persons stored on the reservation. Answers only whether it matches; never shows stored data.', $connectionName), 0, 1024),
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'reference' => ['type' => 'string', 'description' => 'Reservation or booking number exactly as the visitor gave it.'],
                        'name' => ['type' => 'string', 'description' => 'Full name or surname exactly as the visitor gave it.'],
                        'contact' => ['type' => 'string', 'description' => 'E-mail address or phone number exactly as the visitor gave it.'],
                    ],
                    'required' => ['reference', 'name', 'contact'],
                ],
            ],
        ];
    }

    /**
     * verify_guest: looks the reservation up and compares; the answer is the
     * fact for the model.
     *
     * @param array<string, mixed>                                $arguments
     * @param callable(string, array<string, mixed>): McpResult   $call      a tool of the connection
     */
    public function verify(ConnectionModel $connection, McpTemplate $template, array $arguments, callable $call): string
    {
        $config = $template->guestCheck;
        $reference = trim((string) ($arguments['reference'] ?? ''));
        $name = self::normalName((string) ($arguments['name'] ?? ''));
        $contact = trim((string) ($arguments['contact'] ?? ''));

        if ($this->isLocked()) {
            return 'LOCKED: too many failed checks in the last 24 hours. Tell the visitor the check is not possible right now, reveal nothing about any reservation, and offer to forward the request to the team.';
        }

        if ('' === $reference || '' === $name || '' === $contact) {
            return 'NOT RUN: reference, name and contact are all needed. Ask the visitor for what is missing.';
        }

        $records = [];

        foreach ((array) ($config['lookups'] ?? []) as $lookup) {
            try {
                $result = $call((string) $lookup['tool'], self::fill((array) ($lookup['arguments'] ?? []), $reference));
            } catch (McpException) {
                return 'CHECK FAILED: the booking system did not answer. Nothing is verified; offer to forward the request to the team.';
            }

            if ($result->isError) {
                continue;
            }

            $records = self::records(json_decode($result->text, true), $config);

            if ([] !== $records) {
                break;
            }
        }

        foreach ($records as $record) {
            $person = $this->matchingPerson($record, $config, $name, $contact);

            if (null === $person) {
                continue;
            }

            $id = (string) ($record[$config['id']] ?? '');
            $ids = [$id];
            $group = '' !== $config['group'] ? (string) ($record[$config['group']] ?? '') : '';

            // The person of the group (e.g. the booker) also covers the other records of it.
            if ('' !== $group && $person === $config['group_person']) {
                $ids[] = $group;

                foreach ($records as $other) {
                    if ($group === (string) ($other[$config['group']] ?? '')) {
                        $ids[] = (string) ($other[$config['id']] ?? '');
                    }
                }
            }

            $ids = array_values(array_unique(array_filter($ids)));
            $current = $this->scope[(int) $connection->id]['ids'] ?? [];
            $this->scope[(int) $connection->id] = ['ids' => array_values(array_unique([...$current, ...$ids])), 'exp' => time() + self::TTL];
            $this->changed = true;

            return sprintf(
                'VERIFIED: the visitor is the %s of %s. The protected tools now work for %s and what belongs to it (folios, invoices) in this conversation; do not ask for the proof again. They work for nothing else.',
                $person,
                implode(', ', $ids),
                1 === \count($ids) ? 'this record' : 'these records',
            );
        }

        $this->countMiss();

        return 'NOT VERIFIED: name and contact do not match a reservation with this number. Reveal nothing, not even whether the number exists. Ask the visitor to check the number, the name and the e-mail address or phone number given when booking; after several failed attempts the check locks for 24 hours.';
    }

    /**
     * Before a protected call: why it must not run, or null. Every argument that
     * names a record, at any depth (e.g. body.folioId), must name a verified one
     * or one that came with it; at least one must. With $declared (the arguments
     * of the tool's input schema) an argument the tool does not know is refused:
     * it could not limit what the tool returns.
     *
     * @param array<string, mixed> $arguments
     * @param list<string>         $declared
     */
    public function refuse(ConnectionModel $connection, McpTemplate $template, array $arguments, array $declared = []): string|null
    {
        $allowed = $this->scope[(int) $connection->id]['ids'] ?? [];

        if ([] === $allowed) {
            return 'TOOL NOT RUN: this tool shows or changes the data of a reservation. First prove that the visitor is its guest with verify_guest.';
        }

        // An argument the tool does not know could not limit what it returns.
        if ([] !== $declared && [] !== ($unknown = array_diff(array_map('strval', array_keys($arguments)), $declared))) {
            return 'TOOL NOT RUN: '.implode(', ', $unknown).' '.(1 === \count($unknown) ? 'is not an argument' : 'are not arguments').' of this tool.';
        }

        $named = [];
        self::named($arguments, (array) ($template->guestCheck['arguments'] ?? []), $named);

        if ([] === $named) {
            return 'TOOL NOT RUN: name the verified record (or a folio or invoice of it) in the arguments; searches across reservations are not possible for visitors.';
        }

        foreach ($named as $value) {
            if (!\in_array($value, $allowed, true)) {
                return 'TOOL NOT RUN: '.('' !== $value ? $value : 'this value').' does not belong to the verified reservation. Visitors can only see and change their own reservation.';
            }
        }

        return null;
    }

    /**
     * After a protected call: why the result is not passed on, or null. Every
     * record in the result (an object with the id key and a person, e.g. a
     * reservation) must be a verified one; ids of what it shows with them (a
     * folio, an invoice) become usable in this conversation.
     */
    public function withhold(ConnectionModel $connection, McpTemplate $template, string $text): string|null
    {
        $entry = $this->scope[(int) $connection->id] ?? null;

        if (null === $entry) {
            return 'TOOL RESULT WITHHELD: the visitor is not verified.';
        }

        $data = json_decode($text, true);

        if (!\is_array($data)) {
            return 'TOOL RESULT WITHHELD: the result does not belong to the verified reservation.';
        }

        $learned = [];
        self::learn($data, $entry['ids'], (string) ($template->guestCheck['id'] ?? 'id'), $learned);

        if (self::hasForeignRecord($data, [...$entry['ids'], ...$learned], $template->guestCheck)) {
            return 'TOOL RESULT WITHHELD: the result does not belong to the verified reservation.';
        }

        $new = array_values(array_diff(array_unique($learned), $entry['ids']));

        if ([] !== $new) {
            $this->scope[(int) $connection->id]['ids'] = [...$entry['ids'], ...$new];
            $this->changed = true;
        }

        return null;
    }

    /**
     * The guest_check of a template with its defaults, checked.
     *
     * @param array<string, mixed> $data
     *
     * @return array{lookups: list<array{tool: string, arguments: array<string, mixed>}>, id: string, group: string, persons: list<string>, group_person: string, name: list<string>, first_name: string, contact: list<string>, arguments: list<string>, protected: list<string>, creates: list<string>}
     *
     * @throws \InvalidArgumentException with the reason
     */
    public static function config(array $data): array
    {
        $strings = static fn (mixed $value): array => array_values(array_filter(array_map(static fn ($v): string => \is_scalar($v) ? trim((string) $v) : '', (array) $value)));
        $lookups = [];

        foreach ((array) ($data['lookups'] ?? []) as $lookup) {
            $tool = \is_array($lookup) ? trim((string) ($lookup['tool'] ?? '')) : '';

            if ('' === $tool) {
                throw new \InvalidArgumentException('guest_check.lookups: every lookup needs a tool');
            }

            $lookups[] = ['tool' => $tool, 'arguments' => (array) ($lookup['arguments'] ?? [])];
        }

        $config = [
            'lookups' => $lookups,
            'id' => trim((string) ($data['id'] ?? 'id')),
            'group' => trim((string) ($data['group'] ?? '')),
            'persons' => $strings($data['persons'] ?? []),
            'group_person' => trim((string) ($data['group_person'] ?? '')),
            'name' => $strings($data['name'] ?? ['lastName']),
            'first_name' => trim((string) ($data['first_name'] ?? 'firstName')),
            'contact' => $strings($data['contact'] ?? ['email', 'phone']),
            'arguments' => $strings($data['arguments'] ?? []),
            'protected' => $strings($data['protected'] ?? []),
            'creates' => $strings($data['creates'] ?? []),
        ];

        if ([] === $config['lookups'] || [] === $config['persons'] || [] === $config['arguments'] || [] === $config['protected']) {
            throw new \InvalidArgumentException('guest_check needs lookups, persons, arguments and protected');
        }

        return $config;
    }

    /**
     * The records of a lookup result: every object with the id key and at least
     * one person (a single record, a list, or a list inside the answer).
     *
     * @param array<string, mixed> $config
     *
     * @return list<array<string, mixed>>
     */
    private static function records(mixed $data, array $config): array
    {
        if (!\is_array($data)) {
            return [];
        }

        $found = [];

        if (isset($data[$config['id']]) && [] !== array_intersect($config['persons'], array_keys($data))) {
            $found[] = $data;
        }

        foreach ($data as $value) {
            if (\is_array($value)) {
                $found = [...$found, ...self::records($value, $config)];
            }
        }

        return $found;
    }

    /**
     * The key of the first person on the record whose name and contact both
     * match, or null.
     *
     * @param array<string, mixed> $record
     * @param array<string, mixed> $config
     */
    private function matchingPerson(array $record, array $config, string $name, string $contact): string|null
    {
        foreach ($config['persons'] as $key) {
            $value = $record[$key] ?? null;
            $persons = \is_array($value) && array_is_list($value) ? $value : [$value];

            foreach ($persons as $person) {
                if (!\is_array($person)) {
                    continue;
                }

                if (self::nameMatches($person, $config, $name) && self::contactMatches($person, $config, $contact)) {
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * The name given matches when it is the surname, or first name and surname.
     *
     * @param array<string, mixed> $person
     * @param array<string, mixed> $config
     */
    private static function nameMatches(array $person, array $config, string $given): bool
    {
        $first = self::normalName((string) ($person[$config['first_name']] ?? ''));

        foreach ($config['name'] as $field) {
            $last = self::normalName((string) ($person[$field] ?? ''));

            if ('' !== $last && ($given === $last || ('' !== $first && $given === $first.' '.$last) || str_ends_with($given, ' '.$last))) {
                return true;
            }
        }

        return false;
    }

    /**
     * E-mail addresses compare without case; phone numbers by their last nine
     * digits (country code or leading 0 do not matter), at least seven digits.
     *
     * @param array<string, mixed> $person
     * @param array<string, mixed> $config
     */
    private static function contactMatches(array $person, array $config, string $given): bool
    {
        $email = str_contains($given, '@') ? mb_strtolower(trim($given)) : '';
        $digits = preg_replace('/\D+/', '', $given) ?? '';

        foreach ($config['contact'] as $field) {
            $stored = trim((string) ($person[$field] ?? ''));

            if ('' === $stored) {
                continue;
            }

            if ('' !== $email && str_contains($stored, '@')) {
                if ($email === mb_strtolower($stored)) {
                    return true;
                }

                continue;
            }

            $storedDigits = preg_replace('/\D+/', '', $stored) ?? '';

            if ('' === $email && \strlen($digits) >= 7 && \strlen($storedDigits) >= 7 && substr($digits, -9) === substr($storedDigits, -9)) {
                return true;
            }
        }

        return false;
    }

    private static function normalName(string $name): string
    {
        $name = mb_strtolower(trim($name));

        if (class_exists(\Normalizer::class)) {
            $name = (string) preg_replace('/\p{Mn}+/u', '', (string) \Normalizer::normalize($name, \Normalizer::FORM_D));
        }

        return trim((string) preg_replace('/[^\p{L}\p{N}]+/u', ' ', $name));
    }

    /**
     * The arguments of a lookup with the reference in place of "{reference}".
     *
     * @param array<string|int, mixed> $arguments
     *
     * @return array<string|int, mixed>
     */
    private static function fill(array $arguments, string $reference): array
    {
        foreach ($arguments as $key => $value) {
            $arguments[$key] = \is_array($value) ? self::fill($value, $reference) : (\is_string($value) ? str_replace('{reference}', $reference, $value) : $value);
        }

        return $arguments;
    }

    /**
     * Values of the arguments that name a record, at any depth.
     *
     * @param array<mixed>  $arguments
     * @param list<string>  $keys
     * @param list<string>  $named
     */
    private static function named(array $arguments, array $keys, array &$named): void
    {
        foreach ($arguments as $key => $value) {
            if (\is_string($key) && \in_array($key, $keys, true)) {
                foreach ((array) $value as $item) {
                    $named[] = \is_scalar($item) ? (string) $item : '';
                }

                continue;
            }

            if (\is_array($value)) {
                self::named($value, $keys, $named);
            }
        }
    }

    /**
     * Whether the data holds a record (the id key and a person) that is not one
     * of the ids.
     *
     * @param array<mixed>         $data
     * @param list<string>         $ids
     * @param array<string, mixed> $config
     */
    private static function hasForeignRecord(array $data, array $ids, array $config): bool
    {
        $idKey = (string) ($config['id'] ?? 'id');

        if (isset($data[$idKey]) && \is_scalar($data[$idKey]) && [] !== array_intersect(array_keys($data), (array) ($config['persons'] ?? [])) && !\in_array((string) $data[$idKey], $ids, true)) {
            return true;
        }

        foreach ($data as $value) {
            if (\is_array($value) && self::hasForeignRecord($value, $ids, $config)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<mixed>  $data
     * @param list<string>  $ids
     */
    private static function mentions(array $data, array $ids): bool
    {
        foreach ($data as $value) {
            if (\is_array($value) ? self::mentions($value, $ids) : (\is_scalar($value) && \in_array((string) $value, $ids, true))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ids of the objects in a result that belong to a verified record: those
     * that mention one of the ids (a folio of the reservation, an invoice of the
     * folio). Repeated until nothing new turns up.
     *
     * @param array<mixed>  $data
     * @param list<string>  $ids
     * @param list<string>  $learned
     */
    private static function learn(array $data, array $ids, string $idKey, array &$learned): void
    {
        do {
            $before = \count($learned);
            $known = [...$ids, ...$learned];
            self::collect($data, $known, $idKey, $learned);
            $learned = array_values(array_unique($learned));
        } while (\count($learned) > $before);
    }

    /**
     * @param array<mixed>  $data
     * @param list<string>  $known
     * @param list<string>  $learned
     */
    private static function collect(array $data, array $known, string $idKey, array &$learned): void
    {
        if (isset($data[$idKey]) && \is_scalar($data[$idKey])) {
            $rest = $data;
            unset($rest[$idKey]);

            if (self::mentions($rest, $known)) {
                $learned[] = (string) $data[$idKey];
            }
        }

        foreach ($data as $value) {
            if (\is_array($value)) {
                self::collect($value, $known, $idKey, $learned);
            }
        }
    }

    /**
     * Ids in the result of a creating tool: every value of the id key, the group
     * key and of keys that list them (e.g. "reservationIds").
     *
     * @param array<mixed>         $data
     * @param array<string, mixed> $config
     * @param list<string>         $ids
     */
    private static function createdIds(array $data, array $config, array &$ids): void
    {
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                self::createdIds($value, $config, $ids);

                if (\is_string($key) && \in_array($key, $config['arguments'], true) && array_is_list($value)) {
                    foreach ($value as $item) {
                        if (\is_scalar($item)) {
                            $ids[] = (string) $item;
                        }
                    }
                }

                continue;
            }

            if (\is_scalar($value) && \is_string($key) && ($key === $config['id'] || ('' !== $config['group'] && $key === $config['group']))) {
                $ids[] = (string) $value;
            }
        }
    }

    /** @param list<string>|array<mixed> $patterns */
    private static function matchesAny(array $patterns, string $tool): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch((string) $pattern, $tool, FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }

    private function isLocked(): bool
    {
        return (int) $this->cache->getItem($this->missKey())->get() >= self::MISSES;
    }

    private function countMiss(): void
    {
        $item = $this->cache->getItem($this->missKey());
        $item->set((int) $item->get() + 1);
        $item->expiresAfter(self::LOCK);
        $this->cache->save($item);
    }

    private function missKey(): string
    {
        $ip = (string) $this->requests->getMainRequest()?->getClientIp();

        return 'nsa_guest_'.hash('sha256', $this->secret.'|'.$ip.'|'.$this->agentId);
    }

    private function sign(string $payload): string
    {
        return rtrim(strtr(base64_encode(hash_hmac('sha256', 'guest|'.$payload, $this->secret, true)), '+/', '-_'), '=');
    }
}
