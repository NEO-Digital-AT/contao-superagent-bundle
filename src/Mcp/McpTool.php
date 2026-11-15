<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Mcp;

/**
 * A tool as the MCP server lists it (tools/list): name, description and input
 * schema exactly as delivered, plus what the server says about its effect.
 *
 * Kind, from the annotations of the MCP specification: "read" (readOnlyHint),
 * "delete" (destructiveHint), "change" (neither), "unmarked" (no annotations;
 * by the specification such a tool may change or delete data). A template can
 * override the kind; the server's word is used otherwise.
 */
final class McpTool
{
    public const READ = 'read';

    public const CHANGE = 'change';

    public const DELETE = 'delete';

    public const UNMARKED = 'unmarked';

    /** Input names that always carry a person's data (hint for the privacy mode, no guarantee). */
    private const PERSONAL = '/^(e-?mail|mail|e-?mail_?address|phone|phone_?number|telephone|tel|mobile|first_?name|last_?name|full_?name|given_?name|family_?name|surname|birth_?date|date_?of_?birth|iban)$/i';

    /**
     * Input names that may belong to a person or to something else: the name of a
     * place, a product or a file, the address of a place. They count only when the
     * description of the input, or without one the description of the tool,
     * speaks of a person.
     */
    private const AMBIGUOUS = '/^(name|address|street|postal_?address|home_?address)$/i';

    /** Words for a person in a description (English, as MCP servers describe their tools). */
    private const PERSON = '/\b(person|persons|people|personal|guest|guests|customer|customers|client|clients|user|users|contact|contacts|booker|recipient|sender|member|members|employee|employees|patient|patients|owner|holder|attendee|attendees|applicant|traveller|traveler|visitor|visitors|first name|last name|full name|surname)\b/i';

    /**
     * @param array<string, mixed> $inputSchema
     * @param array<string, mixed> $annotations
     */
    public function __construct(
        public readonly string $name,
        public readonly string $title,
        public readonly string $description,
        public readonly array $inputSchema,
        public readonly array $annotations = [],
    ) {
    }

    /** @param array<string, mixed> $data one entry of tools/list */
    public static function fromArray(array $data): self|null
    {
        $name = trim((string) ($data['name'] ?? ''));

        if ('' === $name) {
            return null;
        }

        $schema = \is_array($data['inputSchema'] ?? null) ? $data['inputSchema'] : [];
        $schema += ['type' => 'object'];

        return new self(
            $name,
            trim((string) ($data['title'] ?? $data['annotations']['title'] ?? '')),
            trim((string) ($data['description'] ?? '')),
            $schema,
            \is_array($data['annotations'] ?? null) ? $data['annotations'] : [],
        );
    }

    public function kind(): string
    {
        if (true === ($this->annotations['readOnlyHint'] ?? null)) {
            return self::READ;
        }

        if (true === ($this->annotations['destructiveHint'] ?? null)) {
            return self::DELETE;
        }

        if (false === ($this->annotations['readOnlyHint'] ?? null) || false === ($this->annotations['destructiveHint'] ?? null)) {
            return self::CHANGE;
        }

        return self::UNMARKED;
    }

    /**
     * Whether an input asks for a person's data (top level of the schema): an
     * e-mail address, a phone number, a first or last name … always; "name",
     * "address" or "street" only when the description speaks of a person. The
     * name of a place or an address alone is no personal data.
     */
    public function asksForPersonalData(): bool
    {
        foreach ((array) ($this->inputSchema['properties'] ?? []) as $property => $definition) {
            $property = (string) $property;

            if (preg_match(self::PERSONAL, $property)) {
                return true;
            }

            if (!preg_match(self::AMBIGUOUS, $property)) {
                continue;
            }

            $description = trim(\is_array($definition) ? (string) ($definition['description'] ?? $definition['title'] ?? '') : '');

            if (preg_match(self::PERSON, '' !== $description ? $description : $this->description.' '.$this->title)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'inputSchema' => $this->inputSchema,
            'annotations' => $this->annotations,
        ];
    }
}
