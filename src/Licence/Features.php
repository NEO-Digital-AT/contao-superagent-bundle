<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Licence;

/**
 * What the assistant may do with the licence of this website. Without a licence
 * it is a chat that answers from the operator's own knowledge source; from the
 * edition "basic" on it may also open pages of the website, open the contact
 * form in the chat or the contact page, and search approved external sources;
 * the edition "plus" adds tools of connected MCP servers and installed skills;
 * the edition "pro" adds voice input. Each edition includes the ones below it.
 * Decided only by the signed certificate (FeatureResolver), never by a setting.
 */
final class Features
{
    public const PAID = ['basic', 'plus', 'pro'];

    private function __construct(
        public readonly string $edition,
        public readonly bool $navigation,
        public readonly bool $contact,
        public readonly bool $webSearch,
        public readonly bool $voice = false,
        public readonly bool $mcp = false,
        public readonly bool $skills = false,
    ) {
    }

    public static function forEdition(string $edition): self
    {
        $paid = \in_array($edition, self::PAID, true);

        $plus = \in_array($edition, ['plus', 'pro'], true);

        return new self($edition, $paid, $paid, $paid, 'pro' === $edition, $plus, $plus);
    }

    /** For the configuration of the chat script (it only shows or hides controls; the server decides). */
    public function toArray(): array
    {
        return ['navigation' => $this->navigation, 'contact' => $this->contact, 'webSearch' => $this->webSearch, 'voice' => $this->voice, 'mcp' => $this->mcp, 'skills' => $this->skills];
    }
}
