<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\BackendUser;
use Contao\Config;
use Contao\DataContainer;
use Contao\Date;
use Contao\FilesModel;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Neo\SuperAgentBundle\Chat\ChatHandler;
use Neo\SuperAgentBundle\Chat\ChatTools;
use Neo\SuperAgentBundle\Mcp\Connections;
use Neo\SuperAgentBundle\Mcp\TemplateCatalog;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Neo\SuperAgentBundle\Skill\InstalledSkills;
use Neo\SuperAgentBundle\Skill\SkillFile;
use Neo\SuperAgentBundle\Skill\SkillPackage;
use Neo\SuperAgentBundle\Skill\SkillSource;
use Neo\SuperAgentBundle\Skill\SkillSourceException;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Back end of the installed skills (tl_neo_agent_skill, administrators only):
 * "Load and check" fetches the SKILL.md and checks it, the full text and the
 * changes against the installed version are shown, and the text is taken over
 * only with the confirmation. Also the choice of skills at the assistant.
 */
final class SkillCallbacks
{
    private const TABLE = InstalledSkills::TABLE;

    public function __construct(
        private readonly SkillSource $source,
        private readonly InstalledSkills $skills,
        private readonly Connections $connections,
        private readonly Connection $db,
        private readonly Security $security,
        private readonly TemplateCatalog $templates,
        private readonly string $projectDir,
        private readonly RequestInput $input,
    ) {
    }

    /**
     * Sources a skill can be installed from; "template" only for a skill a
     * template brought along (installed with its connection).
     *
     * @return list<string>
     */
    public function sourceOptions(DataContainer|null $dc = null): array
    {
        $current = null !== $dc && $dc->id ? (string) $this->db->fetchOne('SELECT source FROM '.self::TABLE.' WHERE id = ?', [(int) $dc->id]) : '';

        return 'template' === $current ? ['template'] : ['github', 'file'];
    }

    /** onload: the shipped skills above the list (they cannot be changed). */
    public function shipped(): void
    {
        if ('' !== (string) $this->input->query('act')) {
            return;
        }

        $ids = array_keys(SkillPackage::load(ChatHandler::skillDir(), ChatTools::NAMES)->modules());
        Message::addRaw(sprintf('<p class="tl_info">%s</p>', StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][self::TABLE]['shipped'] ?? '%s', 'core, '.implode(', ', $ids)))));
    }

    /** @param array<string, mixed> $row */
    public function label(array $row, string $label): string
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];

        if ('' === (string) ($row['body'] ?? '')) {
            $state = $lang['badge_not_installed'] ?? '';
            $label = '<strong>'.StringUtil::specialchars($this->sourceLabel($row)).'</strong>';
        } else {
            $state = sprintf($lang['badge_installed'] ?? '%s %s', $lang['source_options'][(string) $row['source']] ?? '', $this->date((int) $row['version_at']));
        }

        // A new version waiting for the confirmation (a new skill is "not taken over yet" anyway).
        if ('' !== (string) ($row['body'] ?? '') && '' !== (string) ($row['pending'] ?? '')) {
            $state .= ' · '.($lang['badge_pending'] ?? '');
        }

        return $label.' <span class="label-info" style="margin-left:6px">'.StringUtil::specialchars($state).'</span>';
    }

    public function checkUrl(mixed $value): string
    {
        $value = trim((string) $value);

        if ('' !== $value && '' === SkillSource::rawUrl($value)) {
            throw new \RuntimeException($GLOBALS['TL_LANG'][self::TABLE]['source_github_url'] ?? 'GitHub only.');
        }

        return $value;
    }

    /** Buttons, result of the check, the text, the changes and the confirmation. */
    public function checkField(DataContainer $dc): string
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $row = $this->row((int) $dc->id);

        if (null === $row) {
            return '';
        }

        $installed = '' !== (string) $row['body'];
        $pending = json_decode((string) $row['pending'], true);
        $html = '';

        if ($installed) {
            $html .= sprintf('<p class="tl_confirm">%s</p>', StringUtil::specialchars(sprintf($lang['installed'] ?? '%s %s %s', (string) $row['name'], (string) $row['installed_by'], $this->date((int) $row['version_at']))));
        }

        $buttons = sprintf(
            '<p style="margin:8px 0"><button type="submit" name="neo_skill_fetch" value="1" class="tl_submit" data-neo-action>%s</button></p>',
            StringUtil::specialchars($installed ? ($lang['check_update'] ?? '') : ($lang['check_load'] ?? '')),
        );

        if (!\is_array($pending)) {
            $body = $installed ? $this->textBlock($lang['text_installed'] ?? '', (string) $row['body'], false) : '';

            return '<div class="widget clr">'.('' !== $html ? '<div class="tl_message">'.$html.'</div>' : '').$buttons.$body.'</div>';
        }

        $file = (array) ($pending['file'] ?? []);
        $check = (array) ($pending['check'] ?? []);
        $lines = [];

        foreach ((array) ($check['errors'] ?? []) as $error) {
            $lines[] = ['tl_error', $this->message((array) $error)];
        }

        if ([] === (array) ($check['errors'] ?? [])) {
            $lines[] = ['tl_confirm', sprintf($lang['check_ok'] ?? '%s %s %s', (string) ($file['name'] ?? ''), System::getFormattedNumber(mb_strlen((string) ($file['body'] ?? '')), 0), System::getFormattedNumber(SkillPackage::MAX_SKILL, 0))];
        }

        foreach ((array) ($check['warnings'] ?? []) as $warning) {
            $lines[] = ['tl_info', $this->message((array) $warning)];
        }

        if ([] !== (array) ($pending['files'] ?? [])) {
            $lines[] = ['tl_info', sprintf($lang['files_ignored'] ?? '%s', implode(', ', (array) $pending['files']))];
        }

        $html .= sprintf('<p class="tl_info">%s</p>', StringUtil::specialchars(sprintf($lang['fetched'] ?? '%s', $this->date((int) ($pending['fetched_at'] ?? 0)))));

        foreach ($lines as [$class, $text]) {
            $html .= sprintf('<p class="%s">%s</p>', $class, StringUtil::specialchars($text));
        }

        $out = '<div class="widget clr"><div class="tl_message">'.$html.'</div>'.$buttons;

        if ($installed) {
            $out .= $this->diffBlock($lang['changes'] ?? '', (string) $row['body'], (string) ($file['body'] ?? ''));
        }

        $out .= $this->textBlock($lang['text_pending'] ?? '', sprintf("name: %s\ndescription: %s\n\n%s", $file['name'] ?? '', $file['description'] ?? '', $file['body'] ?? ''), !$installed);

        if ([] === (array) ($check['errors'] ?? [])) {
            $out .= sprintf(
                '<div class="tl_checkbox_single_container" style="margin-top:12px"><input type="checkbox" class="tl_checkbox" name="neo_skill_confirm" id="neo_skill_confirm" value="1" style="margin-right:8px"><label for="neo_skill_confirm">%s</label></div><p class="tl_help tl_tip">%s</p>',
                StringUtil::specialchars($lang['confirm_check'] ?? ''),
                StringUtil::specialchars($lang['confirm_help'] ?? ''),
            );
        }

        return $out.'</div>';
    }

    /**
     * onsubmit: load and check, or take over the checked text with the
     * confirmation. A new record is loaded on its first save.
     */
    public function submit(DataContainer $dc): void
    {
        $user = $this->security->getUser();
        $row = $this->row((int) $dc->id);

        // Changing the source reloads the form; that is no save.
        if (null === $row || !$user instanceof BackendUser || 'auto' === $this->input->post('SUBMIT_TYPE')) {
            return;
        }

        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $pending = json_decode((string) $row['pending'], true);

        if ('1' === $this->input->post('neo_skill_confirm') && \is_array($pending)) {
            $this->takeOver($row, $pending, (string) $user->username);

            return;
        }

        $sourceSet = 'github' === $row['source'] ? '' !== (string) $row['url'] : null !== $row['file'];

        if ('1' === $this->input->post('neo_skill_fetch') || ('' === (string) $row['body'] && !\is_array($pending) && $sourceSet)) {
            $this->fetch($row);
        } elseif (\is_array($pending) && '1' !== $this->input->post('neo_skill_confirm')) {
            Message::addInfo($lang['confirm_missing'] ?? '');
        }
    }

    /**
     * Installed skills for the choice at the assistant.
     *
     * @return array<string, string>
     */
    public function agentOptions(): array
    {
        $options = [];

        foreach ($this->skills->all() as $name => $skill) {
            $options[$name] = $name.' – '.self::shorten((string) $skill['description'], 140);
        }

        return $options;
    }

    /**
     * The text cut to at most $max characters at the last space before it, with "…"
     * when something was cut; the text itself when it fits.
     */
    public static function shorten(string $text, int $max): string
    {
        $text = trim($text);

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');

        if (false !== $space && $space > $max / 2) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " ,;:.-–").'…';
    }

    /** @param array<string, mixed> $row */
    private function fetch(array $row): void
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];

        try {
            if ('github' === $row['source']) {
                $fetched = $this->source->fromGithub((string) $row['url']);
            } elseif ('template' === $row['source']) {
                // Reference: the template id, or "<template id>/<skill>" for one of several skills.
                $file = (string) $this->templates->get((string) strtok((string) $row['url'], '/'))?->skillFileFor((string) $row['url']);

                if ('' === $file) {
                    throw new SkillSourceException('template_missing', (string) $row['url']);
                }

                $fetched = $this->source->fromFile($file);
            } else {
                $file = null !== $row['file'] ? FilesModel::findByUuid($row['file']) : null;
                $fetched = $this->source->fromFile(null !== $file ? $this->projectDir.'/'.$file->path : '');
            }
        } catch (SkillSourceException $e) {
            $this->db->update(self::TABLE, ['pending' => null], ['id' => (int) $row['id']]);
            Message::addError(self::sourceMessage($e));

            return;
        }

        $hash = hash('sha256', $fetched['content']);

        if ('' !== (string) $row['body'] && $hash === (string) $row['version_hash']) {
            $this->db->update(self::TABLE, ['pending' => null], ['id' => (int) $row['id']]);
            Message::addConfirmation($lang['no_update'] ?? '');

            return;
        }

        $file = SkillFile::parse($fetched['content']);
        $installed = $this->skills->all();
        $shipped = array_keys(SkillPackage::load(ChatHandler::skillDir(), ChatTools::NAMES)->modules());
        $others = array_values(array_diff(array_keys($installed), [(string) $row['name']]));
        $knownTools = [];

        foreach (ConnectionModel::findAll() ?? [] as $connection) {
            $knownTools = [...$knownTools, ...array_keys($this->connections->tools($connection))];
        }

        $check = $file->check(['core', ...$shipped, ...$others], [...$shipped, ...array_keys($installed)], $knownTools);

        if ('' !== (string) $row['name'] && '' === $file->parseError && $file->name !== (string) $row['name']) {
            $check['warnings'][] = ['renamed', (string) $row['name'], $file->name];
        }

        $this->db->update(self::TABLE, ['pending' => json_encode([
            'file' => $file->toArray(),
            'hash' => $hash,
            'fetched_at' => time(),
            'files' => $fetched['files'],
            'check' => $check,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)], ['id' => (int) $row['id']]);

        [] === $check['errors']
            ? Message::addInfo($lang['fetched_ok'] ?? '')
            : Message::addError($lang['fetched_errors'] ?? '');
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $pending
     */
    private function takeOver(array $row, array $pending, string $username): void
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];

        if ([] !== (array) ($pending['check']['errors'] ?? [])) {
            Message::addError($lang['fetched_errors'] ?? '');

            return;
        }

        $file = (array) ($pending['file'] ?? []);

        // The name may have been taken by another skill since the check.
        $taken = (int) $this->db->fetchOne('SELECT COUNT(*) FROM '.self::TABLE.' WHERE name = ? AND id != ? AND body != \'\'', [(string) $file['name'], (int) $row['id']]);

        if ($taken > 0) {
            Message::addError(sprintf($lang['check_name_taken'] ?? '%s', (string) $file['name']));

            return;
        }

        $this->db->update(self::TABLE, [
            'name' => (string) $file['name'],
            'description' => (string) $file['description'],
            'body' => (string) $file['body'],
            'tools' => serialize(array_values((array) ($file['tools'] ?? []))),
            'depends' => serialize(array_values((array) ($file['depends'] ?? []))),
            'version_hash' => (string) $pending['hash'],
            'version_at' => time(),
            'installed_by' => mb_substr($username, 0, 64),
            'pending' => null,
            'tstamp' => time(),
        ], ['id' => (int) $row['id']]);

        Message::addConfirmation(sprintf($lang['taken_over'] ?? '%s', (string) $file['name']));
    }

    /** @return array<string, mixed>|null */
    private function row(int $id): array|null
    {
        return $this->db->fetchAssociative('SELECT * FROM '.self::TABLE.' WHERE id = ?', [$id]) ?: null;
    }

    /** @param array<int, int|string> $entry [code, values …] */
    private function message(array $entry): string
    {
        return self::checkMessage($entry);
    }

    /**
     * A finding of the check as text, also for other tables (the skill of a
     * template is checked when its connection is saved).
     *
     * @param array<int, int|string> $entry [code, values …]
     */
    public static function checkMessage(array $entry): string
    {
        System::loadLanguageFile(self::TABLE);
        $code = (string) array_shift($entry);

        // Numbers as the back end writes them (1.913, 2,800 …).
        return vsprintf($GLOBALS['TL_LANG'][self::TABLE]['check_'.$code] ?? $code, array_map(static fn (int|string $v): string => \is_int($v) ? System::getFormattedNumber($v, 0) : $v, $entry));
    }

    /** Why a SKILL.md could not be fetched, as text. */
    public static function sourceMessage(SkillSourceException $e): string
    {
        System::loadLanguageFile(self::TABLE);

        return sprintf($GLOBALS['TL_LANG'][self::TABLE]['source_'.$e->reason] ?? '%s', $e->detail);
    }

    private function textBlock(string $title, string $text, bool $open): string
    {
        return sprintf(
            '<details style="margin:10px 0"%s><summary><strong>%s</strong></summary><pre style="white-space:pre-wrap;margin:6px 0 0;padding:10px;border:1px solid var(--border, #ddd);max-height:420px;overflow:auto">%s</pre></details>',
            $open ? ' open' : '',
            StringUtil::specialchars($title),
            StringUtil::specialchars($text),
        );
    }

    /** Line by line: removed lines with "−", added lines with "+". */
    private function diffBlock(string $title, string $old, string $new): string
    {
        $a = explode("\n", $old);
        $b = explode("\n", $new);
        $n = \count($a);
        $m = \count($b);
        $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));

        for ($i = $n - 1; $i >= 0; --$i) {
            for ($j = $m - 1; $j >= 0; --$j) {
                $lcs[$i][$j] = $a[$i] === $b[$j] ? $lcs[$i + 1][$j + 1] + 1 : max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
            }
        }

        $out = '';
        $i = 0;
        $j = 0;

        while ($i < $n || $j < $m) {
            if ($i < $n && $j < $m && $a[$i] === $b[$j]) {
                $out .= '  '.StringUtil::specialchars($a[$i])."\n";
                ++$i;
                ++$j;
            } elseif ($i < $n && ($j >= $m || $lcs[$i + 1][$j] >= $lcs[$i][$j + 1])) {
                // Removed lines first, then the added ones.
                $out .= '<del style="text-decoration:none;font:inherit;background:#fbe3e3">− '.StringUtil::specialchars($a[$i])."</del>\n";
                ++$i;
            } else {
                $out .= '<ins style="text-decoration:none;font:inherit;background:#e6f4e1">+ '.StringUtil::specialchars($b[$j])."</ins>\n";
                ++$j;
            }
        }

        return sprintf(
            '<details style="margin:10px 0" open><summary><strong>%s</strong></summary><pre style="white-space:pre-wrap;margin:6px 0 0;padding:10px;border:1px solid var(--border, #ddd);max-height:420px;overflow:auto">%s</pre></details>',
            StringUtil::specialchars($title),
            $out,
        );
    }

    /** @param array<string, mixed> $row */
    private function sourceLabel(array $row): string
    {
        if ('github' === $row['source']) {
            return '' !== (string) $row['url'] ? (string) $row['url'] : '–';
        }

        if ('template' === $row['source']) {
            return (string) ($this->templates->get((string) strtok((string) $row['url'], '/'))?->name ?? $row['url']);
        }

        $file = null !== $row['file'] ? FilesModel::findByUuid($row['file']) : null;

        return null !== $file ? (string) $file->path : '–';
    }

    private function date(int $timestamp): string
    {
        return $timestamp > 0 ? Date::parse((string) Config::get('datimFormat'), $timestamp) : '–';
    }
}
