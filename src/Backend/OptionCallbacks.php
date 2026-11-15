<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\CoreBundle\Intl\Countries;
use Contao\DataContainer;
use Neo\SuperAgentBundle\Chat\PhoneCodes;
use Neo\SuperAgentBundle\Model\AgentModel;

/**
 * Choices instead of free text for settings with known values: consent category,
 * temperature and answer length. A value stored earlier that is not in the list
 * stays selectable, so an update never loses it.
 */
final class OptionCallbacks
{
    public function __construct(private readonly Countries $countries)
    {
    }

    /**
     * Countries with their calling code, in the language of the back end, for the
     * country preselected in the phone field ('' = by the language of the page).
     *
     * @return array<string, string>
     */
    public function phoneCountry(): array
    {
        $options = [];

        foreach ($this->countries->getCountries() as $code => $name) {
            if (isset(PhoneCodes::DIAL[$code])) {
                $options[$code] = $name.' (+'.PhoneCodes::DIAL[$code].')';
            }
        }

        return $options;
    }

    /** Categories of common consent tools; '' = no consent needed. */
    public const CONSENT = ['', 'functional', 'statistics', 'marketing'];

    /** Answer lengths in tokens. */
    public const MAX_TOKENS = [300, 500, 800, 1200, 2000, 4000];

    /** @return array<string, string> */
    public function consent(DataContainer|null $dc = null): array
    {
        $lang = $GLOBALS['TL_LANG']['tl_neo_agent'];
        $options = [];

        foreach (self::CONSENT as $value) {
            $options[$value] = $lang['consent_options'][$value ?: 'none'] ?? $value;
        }

        $stored = $this->stored($dc, 'consent_category');

        if (null !== $stored && !isset($options[$stored])) {
            $options[$stored] = sprintf($lang['consent_custom'] ?? '%s', $stored);
        }

        return $options;
    }

    /** @return array<string, string> 0.0 to 1.0 in steps of 0.1 */
    public function temperature(DataContainer|null $dc = null): array
    {
        $notes = $GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] ?? [];
        $options = [];

        for ($i = 0; $i <= 10; ++$i) {
            $value = rtrim(rtrim(sprintf('%.1f', $i / 10), '0'), '.');
            $label = number_format($i / 10, 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'] ?? '.', '');
            $options[$value] = isset($notes[$value]) ? $label.' – '.$notes[$value] : $label;
        }

        $stored = $this->stored($dc, 'temperature');

        if (null !== $stored && '' !== $stored && !isset($options[$stored])) {
            $options[$stored] = $stored;
        }

        return $options;
    }

    /** @return array<string, string> */
    public function maxTokens(DataContainer|null $dc = null): array
    {
        $format = $GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] ?? '%s';
        $thousands = $GLOBALS['TL_LANG']['MSC']['thousandsSeparator'] ?? '.';
        $options = [];

        foreach (self::MAX_TOKENS as $tokens) {
            // About 0.7 words per token in German and English; rounded for a rough idea.
            $words = (int) (round($tokens * 0.7 / 50) * 50);
            $options[(string) $tokens] = sprintf($format, number_format($tokens, 0, '', $thousands), number_format($words, 0, '', $thousands));
        }

        $stored = $this->stored($dc, 'max_tokens');

        if (null !== $stored && '' !== $stored && !isset($options[$stored])) {
            $options[$stored] = $stored;
        }

        return $options;
    }

    private function stored(DataContainer|null $dc, string $field): string|null
    {
        if (null === $dc || !$dc->id || null === ($agent = AgentModel::findByPk($dc->id))) {
            return null;
        }

        return (string) $agent->{$field};
    }
}
