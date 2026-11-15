<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The default texts of the bundle (translations/neo_superagent.<lang>.yaml) in
 * the eleven shipped languages. Every other language gets the English ones,
 * never the default language of the installation.
 *
 * They fill a new language record (the operator then edits them) and stand in
 * at run time for fields left empty.
 */
final class DefaultTexts
{
    public const LANGUAGES = ['en', 'de', 'fr', 'nl', 'it', 'pl', 'cs', 'hu', 'sl', 'hr', 'es'];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /** Translation locale for a page or record language: its primary code when shipped, otherwise "en". */
    public static function locale(string $language): string
    {
        $code = strtolower((string) strtok(str_replace('_', '-', trim($language)), '-'));

        return \in_array($code, self::LANGUAGES, true) ? $code : 'en';
    }

    /** @param array<string, string> $params */
    public function text(string $key, string $language, array $params = []): string
    {
        return $this->translator->trans($key, $params, 'neo_superagent', self::locale($language));
    }

    /**
     * Suggestions for the text fields of a language record (column => text),
     * with the placeholders %name% and %to% left in place.
     *
     * @return array<string, string>
     */
    public function forRecord(string $language): array
    {
        $texts = [
            'greeting' => $this->text('widget.greeting', $language),
            'input_placeholder' => $this->text('widget.placeholder', $language),
        ];

        foreach (AgentLanguageModel::UI_KEYS as $key) {
            $texts['ui_'.$key] = $this->text('widget.'.$key, $language);
        }

        return $texts;
    }
}
