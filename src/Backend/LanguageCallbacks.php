<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\Backend;
use Contao\CoreBundle\Intl\Locales;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Neo\SuperAgentBundle\Chat\DefaultTexts;
use Neo\SuperAgentBundle\Chat\StarterQuestions;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Back-end helpers of the per-language records: the language choice (every
 * language Contao knows, this site's languages first), the default texts as
 * suggestions (filled into a new record and with "Load suggestions"; English for
 * languages the bundle does not ship), a record per language of the website for
 * a new assistant, and the warning when a language of the website has no record.
 */
final class LanguageCallbacks
{
    public function __construct(
        private readonly Locales $locales,
        private readonly Connection $db,
        private readonly TranslatorInterface $translator,
        private readonly DefaultTexts $defaults,
        private readonly RequestInput $input,
    ) {
    }

    /** @return array<string, string> */
    public function languages(DataContainer|null $dc = null): array
    {
        $all = [];

        foreach ($this->locales->getLanguages() as $id => $name) {
            $code = self::primary((string) $id);

            if ('' !== $code && !isset($all[$code])) {
                $all[$code] = $name;
            }
        }

        $options = [];

        try {
            foreach ($this->db->fetchFirstColumn("SELECT DISTINCT language FROM tl_page WHERE type = 'root'") as $language) {
                $code = self::primary((string) $language);

                if ('' !== $code) {
                    $options[$code] = $all[$code] ?? strtoupper($code);
                }
            }
        } catch (\Throwable) {
        }

        $options += $all;

        // Keep a stored value selectable even if it is no longer in the list.
        $stored = self::primary((string) ($dc?->value ?? ''));

        if ('' !== $stored && !isset($options[$stored])) {
            $options[$stored] = strtoupper($stored);
        }

        return $options;
    }

    /** Placeholders = the defaults in the record's language, so editors see what an empty field shows. */
    public function placeholders(DataContainer|null $dc = null): void
    {
        $language = '';

        if (null !== $dc && $dc->id) {
            $language = (string) $this->db->fetchOne('SELECT language FROM tl_neo_agent_language WHERE id = ?', [(int) $dc->id]);
        }

        $locale = DefaultTexts::locale('' !== $language ? $language : $this->translator->getLocale());
        $params = ['%name%' => '…', '%to%' => '…'];

        foreach (AgentLanguageModel::UI_KEYS as $key) {
            $GLOBALS['TL_DCA']['tl_neo_agent_language']['fields']['ui_'.$key]['eval']['placeholder'] = $this->translator->trans('widget.'.$key, $params, 'neo_superagent', $locale);
        }
    }

    /**
     * Rows of question and answer (starter questions, questions & answers): Contao's
     * row wizard where Contao has it (5.7 and later); before, the key-value wizard
     * with the same two columns. Both store rows of question and answer
     * (loadPairs(), savePairs()).
     */
    public function pairWizards(): void
    {
        if (isset($GLOBALS['BE_FFL']['rowWizard'])) {
            return;
        }

        $labels = $GLOBALS['TL_LANG']['tl_neo_agent_language'] ?? [];

        foreach (['starter_questions' => 'starter', 'canned' => 'canned'] as $field => $prefix) {
            $GLOBALS['TL_DCA']['tl_neo_agent_language']['fields'][$field]['inputType'] = 'keyValueWizard';
            $GLOBALS['TL_DCA']['tl_neo_agent_language']['fields'][$field]['eval']['keyLabel'] = StringUtil::specialchars((string) ($labels[$prefix.'_q'] ?? 'Question'));
            $GLOBALS['TL_DCA']['tl_neo_agent_language']['fields'][$field]['eval']['valueLabel'] = StringUtil::specialchars((string) ($labels[$prefix.'_a'] ?? 'Answer'));
        }
    }

    /**
     * load_callback: the stored rows for the wizard of this Contao version (rows of
     * question and answer, or key and value). Starter questions never written yet
     * show the former suggested questions, which the chat still shows until the
     * migration has run; saving the form keeps them.
     */
    public function loadPairs(mixed $value, DataContainer $dc): string
    {
        $rows = 'starter_questions' === $dc->field && null === $value
            ? StarterQuestions::fromList(($dc->getCurrentRecord() ?? [])['suggestions'] ?? null)
            : StarterQuestions::rows($value);

        if (!isset($GLOBALS['BE_FFL']['rowWizard'])) {
            $rows = array_map(static fn (array $row): array => ['key' => $row['question'], 'value' => $row['answer']], $rows);
        }

        return serialize($rows);
    }

    /**
     * save_callback: rows of question and answer from either wizard, trimmed, rows
     * without a question left out. Never NULL, so that emptied starter questions
     * are not taken for ones that were never converted.
     */
    public function savePairs(mixed $value): string
    {
        return serialize(StarterQuestions::rows($value));
    }

    /**
     * onsubmit: a record without any text yet (new, or one that relied on the
     * placeholders) gets the suggestions of its language; "Load suggestions"
     * fills the empty fields again. Filled fields are never overwritten.
     */
    public function fillSuggestions(DataContainer $dc): void
    {
        $row = $this->db->fetchAssociative('SELECT * FROM tl_neo_agent_language WHERE id = ?', [(int) $dc->id]);

        if (false === $row || '' === (string) $row['language']) {
            return;
        }

        $suggestions = $this->defaults->forRecord((string) $row['language']);
        $untouched = [] === array_filter(array_keys($suggestions), static fn (string $column): bool => '' !== trim((string) $row[$column]));

        if (!$untouched && null === $this->input->post('neo_suggestions')) {
            return;
        }

        $set = array_filter($suggestions, static fn (string $text, string $column): bool => '' === trim((string) $row[$column]), ARRAY_FILTER_USE_BOTH);

        if ([] !== $set) {
            $this->db->update('tl_neo_agent_language', $set, ['id' => (int) $row['id']]);
        }

        // "Load suggestions" says what it did, right at the button (data-neo-action, Basic's actions.js).
        if (null !== $this->input->post('neo_suggestions')) {
            $lang = $GLOBALS['TL_LANG']['tl_neo_agent_language'] ?? [];

            [] !== $set
                ? Message::addConfirmation(sprintf($lang['suggestions_loaded'] ?? '%d', \count($set)))
                : Message::addInfo($lang['suggestions_none'] ?? '');
        }
    }

    /**
     * Button "Load suggestions" next to the save buttons.
     *
     * @param array<string, string> $buttons
     *
     * @return array<string, string>
     */
    public function suggestionButton(array $buttons): array
    {
        $buttons['neo_suggestions'] = sprintf(
            '<button type="submit" name="neo_suggestions" id="neo_suggestions" class="tl_submit" value="1" data-neo-action>%s</button>',
            StringUtil::specialchars($GLOBALS['TL_LANG']['tl_neo_agent_language']['load_suggestions'] ?? 'Load suggestions'),
        );

        return $buttons;
    }

    /**
     * input_field_callback of the assistant's form: the languages of the assistant
     * with their starter questions (read only) and a button per language that
     * opens its record, plus "Add language". The texts per language live in
     * child records, which an editor would otherwise not find from the form.
     */
    public function overview(DataContainer $dc): string
    {
        $agent = (int) $dc->id;
        $lang = $GLOBALS['TL_LANG']['tl_neo_agent'] ?? [];
        $names = $this->languages();
        $token = (string) System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        // SELECT *: before the database update the column starter_questions may not exist yet.
        $records = $agent > 0 ? $this->db->fetchAllAssociative('SELECT * FROM tl_neo_agent_language WHERE pid = ? ORDER BY sorting, language', [$agent]) : [];
        $items = '';

        foreach ($records as $record) {
            $code = (string) $record['language'];
            $questions = null === ($record['starter_questions'] ?? null) ? StarterQuestions::fromList($record['suggestions'] ?? null) : StarterQuestions::rows($record['starter_questions']);
            $list = '';

            foreach ($questions as $row) {
                $answer = '' !== $row['answer']
                    ? sprintf(' <span class="nsa-languages__answer">%s %s</span>', StringUtil::specialchars($lang['languages_answer'] ?? 'Answer:'), StringUtil::specialchars(mb_strimwidth($row['answer'], 0, 90, '…')))
                    : '';
                $list .= sprintf('<li>%s%s</li>', StringUtil::specialchars($row['question']), $answer);
            }

            $items .= sprintf(
                '<li class="nsa-languages__item"><h4>%s</h4>%s<p><a href="%s" class="tl_submit">%s</a></p></li>',
                StringUtil::specialchars(('' !== $code ? ($names[$code] ?? strtoupper($code)).' ('.$code.')' : '–')),
                '' !== $list ? '<ul class="nsa-languages__questions">'.$list.'</ul>' : '<p class="nsa-languages__none">'.StringUtil::specialchars($lang['starters_none'] ?? '').'</p>',
                StringUtil::specialchars(Backend::addToUrl('table=tl_neo_agent_language&act=edit&id='.(int) $record['id'].'&rt='.$token, true, ['act', 'id', 'table'])),
                StringUtil::specialchars($lang['languages_edit'] ?? 'Edit'),
            );
        }

        $GLOBALS['TL_CSS']['neo_superagent_backend'] = 'bundles/neosuperagent/backend.css|static';

        // One language per assistant (a website with several languages has an assistant for each): the button only while there is none.
        $add = [] === $records ? sprintf(
            '<p><a href="%s" class="tl_submit">%s</a></p>',
            StringUtil::specialchars(Backend::addToUrl('table=tl_neo_agent_language&act=create&mode=2&pid='.$agent.'&rt='.$token, true, ['act', 'id', 'table'])),
            StringUtil::specialchars($lang['languages_add'] ?? 'Add language'),
        ) : '';

        return sprintf(
            '<div class="widget clr nsa-languages"><p class="tl_help tl_tip">%s</p>%s%s</div>',
            StringUtil::specialchars($lang['languages_intro'] ?? ''),
            '' !== $items ? '<ul class="nsa-languages__list">'.$items.'</ul>' : '<p class="nsa-languages__none">'.StringUtil::specialchars($lang['languages_none'] ?? '').'</p>',
            $add,
        );
    }

    /**
     * onload of the language records: an assistant has one language, so no
     * record is created or copied where the assistant already has one (the
     * parent view shows the assistant in "id", a new record names it in "pid").
     */
    public function oneLanguage(DataContainer|null $dc = null): void
    {
        $act = (string) $this->input->query('act');
        $agent = match ($act) {
            'create' => (int) $this->input->query('pid'),
            'copy' => (int) $this->db->fetchOne('SELECT pid FROM tl_neo_agent_language WHERE id = ?', [(int) $this->input->query('id')]),
            '' => (int) $this->input->query('id'),
            default => 0,
        };

        if ($agent > 0 && (int) $this->db->fetchOne('SELECT COUNT(*) FROM tl_neo_agent_language WHERE pid = ?', [$agent]) > 0) {
            $GLOBALS['TL_DCA']['tl_neo_agent_language']['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA']['tl_neo_agent_language']['config']['notCopyable'] = true;
        }
    }

    /**
     * oncreate of an assistant: its record with suggestions in the first
     * language of the website. An assistant has one language; a website with
     * several languages has an assistant for each (copy it, change the language).
     */
    public function createForSiteLanguages(string $table, int|string $id): void
    {
        foreach ($this->siteLanguages() as $language => $root) {
            $this->create((int) $id, $language);

            return;
        }
    }

    /**
     * onload of the list of assistants: a language of the website without a
     * published assistant in that language is reported (an assistant for it is
     * created by copying one and changing its language).
     */
    public function missingLanguages(): void
    {
        if ($this->input->query('act') || $this->input->query('key')) {
            return;
        }

        try {
            $agents = $this->db->fetchAllAssociative("SELECT id, title FROM tl_neo_agent WHERE published = '1' ORDER BY id");
            $covered = array_flip($this->db->fetchFirstColumn("SELECT DISTINCT l.language FROM tl_neo_agent_language l JOIN tl_neo_agent a ON a.id = l.pid WHERE a.published = '1'"));
        } catch (\Throwable) {
            return;
        }

        if ([] === $agents) {
            return;
        }

        $lang = $GLOBALS['TL_LANG']['tl_neo_agent'];

        foreach ($this->siteLanguages() as $language => $root) {
            if (isset($covered[$language])) {
                continue;
            }

            Message::addRaw(sprintf(
                '<p class="tl_info">%s</p>',
                StringUtil::specialchars(sprintf($lang['language_missing'] ?? '%s %s', $this->languageName($language), $root)),
            ));
        }
    }

    /** key=neo_add_language: creates the record with suggestions and opens it. */
    public function addLanguage(): RedirectResponse
    {
        $agent = (int) $this->input->query('id');
        $language = self::primary((string) $this->input->query('language'));
        $id = $agent > 0 && '' !== $language ? $this->create($agent, $language) : 0;
        $router = System::getContainer()->get('router');

        if ($id < 1) {
            return new RedirectResponse($router->generate('contao_backend', ['do' => 'neo_agent']));
        }

        return new RedirectResponse($router->generate('contao_backend', [
            'do' => 'neo_agent',
            'table' => 'tl_neo_agent_language',
            'id' => $id,
            'act' => 'edit',
            'rt' => System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(),
        ]));
    }

    /** Creates a record with suggestions; the existing one when the language is already there. */
    private function create(int $agent, string $language): int
    {
        $existing = (int) $this->db->fetchOne('SELECT id FROM tl_neo_agent_language WHERE pid = ? AND language = ?', [$agent, $language]);

        if ($existing > 0) {
            return $existing;
        }

        $sorting = (int) $this->db->fetchOne('SELECT MAX(sorting) FROM tl_neo_agent_language WHERE pid = ?', [$agent]) + 128;
        $this->db->insert('tl_neo_agent_language', ['pid' => $agent, 'tstamp' => time(), 'sorting' => $sorting, 'language' => $language] + $this->defaults->forRecord($language));

        return (int) $this->db->lastInsertId();
    }

    /** @return array<string, string> language => title of its first start page */
    private function siteLanguages(): array
    {
        $languages = [];

        try {
            foreach ($this->db->fetchAllAssociative("SELECT title, language FROM tl_page WHERE type = 'root' AND published = '1' ORDER BY sorting") as $root) {
                $code = self::primary((string) $root['language']);

                if ('' !== $code && !isset($languages[$code])) {
                    $languages[$code] = (string) $root['title'];
                }
            }
        } catch (\Throwable) {
        }

        return $languages;
    }

    private function languageName(string $code): string
    {
        return $this->languages()[$code] ?? strtoupper($code);
    }

    private static function primary(string $locale): string
    {
        $primary = strtolower((string) strtok(str_replace('_', '-', trim($locale)), '-'));

        return 1 === preg_match('/^[a-z]{2,3}$/', $primary) ? $primary : '';
    }
}
