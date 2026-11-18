<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

/**
 * Definitions of the tools the application runs (OpenAI-compatible function
 * format). A description says what the tool does, what it needs and what it
 * returns; when and how to use it is the business of the skills.
 */
final class ChatTools
{
    public const LOAD_MODULE = 'load_module';

    public const LOAD_TOOLS = 'load_tools';

    /** Tools a skill can name in the manifest. */
    public const NAMES = ['request_handoff', 'open_page', 'find_pages', 'web_search'];

    /** @return array<string, mixed> */
    public static function definition(string $name): array
    {
        return match ($name) {
            'open_page' => self::fn('open_page', 'Opens a page of this website for the visitor: now=true opens it right after your reply, now=false shows a button below your reply. Only for the page the visitor asks for or a page that answers the question, never in place of a page this website does not have (not the start page either). Returns the page title.', [
                'page_id' => ['type' => 'integer', 'description' => 'Id of the page from PAGES or from find_pages.'],
                'now' => ['type' => 'boolean', 'description' => 'true: open at once; false: show a button.'],
            ], ['page_id', 'now']),
            'find_pages' => self::fn('find_pages', 'Searches the pages of this website by words in title, address and description. Returns up to eight pages with id, title, path and description.', [
                'query' => ['type' => 'string', 'description' => 'A few words in the language of the website.'],
            ], ['query']),
            'request_handoff' => self::fn('request_handoff', 'Opens the forwarding form below the chat, prefilled with a summary of the request. It sends nothing; the visitor sends the form.', [
                'summary' => ['type' => 'string', 'description' => 'The request in one to three sentences, in the visitor\'s language, from the visitor\'s point of view, without e-mail addresses.'],
            ], ['summary']),
            'web_search' => self::fn('web_search', 'Searches the web sources approved by the operator. Returns matching text with its source, or no result.', [
                'query' => ['type' => 'string', 'description' => 'The search terms in a few words.'],
            ], ['query']),
            default => throw new \InvalidArgumentException('Unknown tool '.$name),
        };
    }

    /**
     * @param list<string> $modules skills that can still be loaded
     *
     * @return array<string, mixed>
     */
    public static function loadModule(array $modules): array
    {
        return self::fn(self::LOAD_MODULE, 'Loads skills from CATALOG: their rules, their dependencies and their tools.', [
            'modules' => ['type' => 'array', 'items' => ['type' => 'string', 'enum' => $modules], 'description' => 'Ids of the skills to load.'],
        ], ['modules']);
    }

    /**
     * @param list<string> $tools tools that can still be loaded
     *
     * @return array<string, mixed>
     */
    public static function loadTools(array $tools): array
    {
        return self::fn(self::LOAD_TOOLS, 'Makes tools listed in CATALOG callable, together with the skill each belongs to. Not for functions you are offered already: call those directly.', [
            'tools' => ['type' => 'array', 'items' => ['type' => 'string', 'enum' => $tools], 'description' => 'Names of the tools to load.'],
        ], ['tools']);
    }

    /** First sentence of a tool's description, for the catalogue. */
    public static function summary(string $name): string
    {
        $description = (string) (self::definition($name)['function']['description'] ?? '');

        return preg_replace('/(?<=[.!?])\s.*$/s', '', $description) ?? $description;
    }

    /**
     * @param array<string, array<string, mixed>> $properties
     * @param list<string>                        $required
     *
     * @return array<string, mixed>
     */
    private static function fn(string $name, string $description, array $properties, array $required): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $description,
                'parameters' => ['type' => 'object', 'properties' => $properties, 'required' => $required],
            ],
        ];
    }
}
