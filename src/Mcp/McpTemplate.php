<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

use Neo\SuperAgentBundle\Model\ConnectionModel;

/**
 * A template for a connection to a known MCP server: address and sign-in, how
 * its tools are to be treated, the steps of the setup at the service, and
 * optionally skills that describe how to use those tools. A JSON file (see
 * mcp-templates/README.md); read and checked by TemplateCatalog.
 *
 * Tool rules match tool names exactly or with "*" as wildcard:
 *  - kinds: the kind of a tool when the server does not mark it (or marks it
 *    wrongly): read, change, delete;
 *  - locked: locked when the tool first appears, whatever its kind; wins over
 *    unlocked;
 *  - unlocked: free when it first appears although it changes something or
 *    shows customer data (what the tool may do at the service is decided there,
 *    e.g. by the permissions of the app); administrators can lock it in the list;
 *  - customer_data: shows customer data; locked when it first appears unless
 *    unlocked, marked in the list;
 *  - recommended: marked in the list.
 *
 * Skills: "skill" (one SKILL.md) or "skills" (several, each in a folder of its
 * own: <folder>/SKILL.md); installed with the connection.
 *
 * Setup: "setup" holds the steps an administrator follows at the service, per
 * language ({"en": [...], "de": [...]}; English for any other language); a step
 * may name the redirect address of the website as {redirect_uri}.
 *
 * Guest check: "guest_check" makes the visitor prove a reservation is theirs
 * before the protected tools run, and only for that reservation (GuestCheck).
 */
final class McpTemplate
{
    /**
     * @param array{authorization_endpoint: string, token_endpoint: string, registration: bool, scope: string, resource: bool, app_help: string} $oauth
     * @param array<string, string>       $kinds        pattern => kind
     * @param list<string>                $locked       patterns
     * @param list<string>                $customerData patterns
     * @param list<string>                $recommended  patterns
     * @param list<string>                $unlocked     patterns
     * @param array<string, string>       $skillFiles   reference of the installed skill => path of its SKILL.md
     * @param array<string, list<string>> $setup        language => steps
     * @param array<string, mixed>        $guestCheck   GuestCheck::config(), [] without
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $url,
        public readonly string $auth,
        public readonly string $help,
        public readonly array $oauth,
        public readonly array $kinds,
        public readonly array $locked,
        public readonly array $customerData,
        public readonly array $recommended,
        public readonly string $skillFile,
        public readonly array $unlocked = [],
        public readonly array $skillFiles = [],
        public readonly array $setup = [],
        public readonly array $guestCheck = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException with the reason
     */
    public static function fromArray(string $id, array $data, string $dir): self
    {
        $allowed = ['name', 'url', 'auth', 'help', 'oauth', 'tools', 'skill', 'skills', 'setup', 'guest_check'];
        $unknown = array_diff(array_keys($data), $allowed);

        if ([] !== $unknown) {
            throw new \InvalidArgumentException('unknown keys: '.implode(', ', $unknown));
        }

        $name = trim((string) ($data['name'] ?? ''));
        $url = trim((string) ($data['url'] ?? ''));
        $auth = (string) ($data['auth'] ?? ConnectionModel::AUTH_NONE);

        if ('' === $name || 'https' !== parse_url($url, PHP_URL_SCHEME)) {
            throw new \InvalidArgumentException('name and an https url are required');
        }

        if (!\in_array($auth, [ConnectionModel::AUTH_NONE, ConnectionModel::AUTH_KEY, ConnectionModel::AUTH_HEADERS, ConnectionModel::AUTH_OAUTH], true)) {
            throw new \InvalidArgumentException('auth must be none, key, headers or oauth');
        }

        $oauth = (array) ($data['oauth'] ?? []);
        $authorize = trim((string) ($oauth['authorization_endpoint'] ?? ''));
        $token = trim((string) ($oauth['token_endpoint'] ?? ''));

        if (('' === $authorize) !== ('' === $token)) {
            throw new \InvalidArgumentException('oauth needs both endpoints or none');
        }

        foreach ([$authorize, $token] as $endpoint) {
            if ('' !== $endpoint && 'https' !== parse_url($endpoint, PHP_URL_SCHEME)) {
                throw new \InvalidArgumentException('oauth endpoints must be https');
            }
        }

        $tools = (array) ($data['tools'] ?? []);
        $kinds = [];

        foreach ((array) ($tools['kinds'] ?? []) as $pattern => $kind) {
            if (!\in_array($kind, [McpTool::READ, McpTool::CHANGE, McpTool::DELETE], true)) {
                throw new \InvalidArgumentException('tools.kinds: '.$pattern.' has the unknown kind '.json_encode($kind));
            }

            $kinds[(string) $pattern] = (string) $kind;
        }

        // One skill (its reference is the template id, as before) or several (the
        // reference is "<template id>/<folder>", the folder being the skill's name).
        $skillFiles = [];

        if (isset($data['skill'])) {
            $skillFiles[$id] = self::skillPath($dir, (string) $data['skill']);
        }

        foreach ((array) ($data['skills'] ?? []) as $path) {
            $file = self::skillPath($dir, (string) $path);
            $skillFiles[$id.'/'.basename(\dirname($file))] = $file;
        }

        $setup = [];

        foreach ((array) ($data['setup'] ?? []) as $language => $steps) {
            if (1 !== preg_match('/^[a-z]{2}$/', (string) $language) || !\is_array($steps)) {
                throw new \InvalidArgumentException('setup: a two-letter language with a list of steps, e.g. {"en": ["…"]}');
            }

            $setup[(string) $language] = self::patterns($steps);
        }

        return new self(
            $id,
            $name,
            $url,
            $auth,
            trim((string) ($data['help'] ?? '')),
            [
                'authorization_endpoint' => $authorize,
                'token_endpoint' => $token,
                'registration' => (bool) ($oauth['registration'] ?? true),
                'scope' => trim((string) ($oauth['scope'] ?? '')),
                'resource' => (bool) ($oauth['resource'] ?? true),
                'app_help' => trim((string) ($oauth['app_help'] ?? '')),
            ],
            $kinds,
            self::patterns($tools['locked'] ?? []),
            self::patterns($tools['customer_data'] ?? []),
            self::patterns($tools['recommended'] ?? []),
            (string) (reset($skillFiles) ?: ''),
            self::patterns($tools['unlocked'] ?? []),
            $skillFiles,
            $setup,
            isset($data['guest_check']) ? GuestCheck::config((array) $data['guest_check']) : [],
        );
    }

    /** The SKILL.md of an installed skill of this template (its reference), or ''. */
    public function skillFileFor(string $reference): string
    {
        return $this->skillFiles[$reference] ?? '';
    }

    /**
     * The setup steps in a language ("de" for "de-AT"); English, else the first
     * language given, when the template has none in that language.
     *
     * @return list<string>
     */
    public function setupSteps(string $language): array
    {
        $code = strtolower((string) strtok(str_replace('_', '-', $language), '-'));

        return $this->setup[$code] ?? $this->setup['en'] ?? (reset($this->setup) ?: []);
    }

    /** The kind of a tool: the template's rule first, else what the server says. */
    public function kind(McpTool $tool): string
    {
        foreach ($this->kinds as $pattern => $kind) {
            if (self::matches($pattern, $tool->name)) {
                return $kind;
            }
        }

        return $tool->kind();
    }

    /** Locked when it first appears by the template's rules: locked, or customer data not unlocked. */
    public function isLocked(string $tool): bool
    {
        return self::any($this->locked, $tool) || (self::any($this->customerData, $tool) && !$this->isUnlocked($tool));
    }

    /** Free when it first appears although it is not read only; never when it is locked. */
    public function isUnlocked(string $tool): bool
    {
        return self::any($this->unlocked, $tool) && !self::any($this->locked, $tool);
    }

    public function isCustomerData(string $tool): bool
    {
        return self::any($this->customerData, $tool);
    }

    public function isRecommended(string $tool): bool
    {
        return self::any($this->recommended, $tool);
    }

    private static function skillPath(string $dir, string $path): string
    {
        $file = realpath($dir.'/'.$path) ?: '';

        if ('' === $file || !str_starts_with($file, realpath($dir).\DIRECTORY_SEPARATOR) || 'SKILL.md' !== basename($file)) {
            throw new \InvalidArgumentException('skill must be a SKILL.md inside the template folder');
        }

        return $file;
    }

    /** @param list<string> $patterns */
    private static function any(array $patterns, string $tool): bool
    {
        foreach ($patterns as $pattern) {
            if (self::matches($pattern, $tool)) {
                return true;
            }
        }

        return false;
    }

    private static function matches(string $pattern, string $tool): bool
    {
        return fnmatch($pattern, $tool, FNM_NOESCAPE);
    }

    /** @return list<string> */
    private static function patterns(mixed $value): array
    {
        return array_values(array_filter(array_map(static fn ($v): string => \is_scalar($v) ? trim((string) $v) : '', (array) $value)));
    }
}
