<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\DataContainer;
use Contao\Date;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Chat\ChatHandler;
use Neo\SuperAgentBundle\Chat\ChatTools;
use Neo\SuperAgentBundle\Mcp\Connections;
use Neo\SuperAgentBundle\Mcp\McpException;
use Neo\SuperAgentBundle\Mcp\McpTool;
use Neo\SuperAgentBundle\Mcp\OAuthFlow;
use Neo\SuperAgentBundle\Mcp\TemplateCatalog;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Neo\SuperAgentBundle\Skill\InstalledSkills;
use Neo\SuperAgentBundle\Skill\SkillFile;
use Neo\SuperAgentBundle\Skill\SkillPackage;
use Neo\SuperAgentBundle\Skill\SkillSource;
use Neo\SuperAgentBundle\Skill\SkillSourceException;
use Neo\SuperAgentBundle\Skill\TemplateSkills;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Back end of the connections to MCP servers (tl_neo_agent_connection) and of
 * the tool log: administrators only, credentials masked and encrypted, the
 * tool list with locks, the confirmation, reading the tools after saving and
 * the OAuth sign-in.
 */
final class ConnectionCallbacks
{
    private const MASK = '••••••••';

    private const TABLE = 'tl_neo_agent_connection';

    public function __construct(
        private readonly Connections $connections,
        private readonly OAuthFlow $oauth,
        private readonly SecretCipher $cipher,
        private readonly Connection $db,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $router,
        private readonly TemplateCatalog $templates,
        private readonly SkillSource $skillSource,
        private readonly RequestInput $input,
        private readonly TemplateSkills $templateSkills,
    ) {
    }

    /** @return array<string, string> id => name */
    public function templateOptions(): array
    {
        return array_map(static fn ($template): string => $template->name, $this->templates->all());
    }

    /** onload: no template field while there are no templates (and the connection has none). */
    public function hideTemplate(DataContainer|null $dc = null): void
    {
        if ([] !== $this->templates->all()) {
            return;
        }

        $current = null !== $dc && $dc->id ? (string) $this->db->fetchOne('SELECT template FROM '.self::TABLE.' WHERE id = ?', [(int) $dc->id]) : '';

        if ('' === $current) {
            $palette = &$GLOBALS['TL_DCA'][self::TABLE]['palettes']['default'];
            $palette = str_replace(',template,template_help,template_setup,', ',', $palette);
        }
    }

    /** The provider's guide of the chosen template, beside the choice. */
    public function templateHelp(DataContainer $dc): string
    {
        $connection = ConnectionModel::findByPk((int) $dc->id);
        $help = null !== $connection ? (string) $this->connections->template($connection)?->help : '';

        if ('' === $help) {
            return '';
        }

        return sprintf(
            '<div class="w50 widget"><h3>&nbsp;</h3><p style="margin:8px 0">%s %s</p></div>',
            StringUtil::specialchars($GLOBALS['TL_LANG'][self::TABLE]['template_help'] ?? ''),
            $this->link($help),
        );
    }

    /**
     * The steps of the setup at the service the chosen template describes, in
     * the language of the back end; {redirect_uri} in a step becomes the redirect
     * address of this website with a button that copies it, addresses become
     * links, **text** is bold.
     */
    public function templateSetup(DataContainer $dc): string
    {
        $connection = ConnectionModel::findByPk((int) $dc->id);
        $template = null !== $connection ? $this->connections->template($connection) : null;
        $steps = null !== $template ? $template->setupSteps((string) ($GLOBALS['TL_LANGUAGE'] ?? 'en')) : [];

        if (null === $template || [] === $steps) {
            return '';
        }

        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $uri = StringUtil::specialchars($this->redirectUri());
        $copy = sprintf(
            '<span class="nsa-copy"><code>%s</code> <button type="button" class="tl_submit nsa-copy__button" data-nsa-copy="%s" data-nsa-copied="%s">%s</button><span class="nsa-copy__status" role="status"></span></span>',
            $uri,
            $uri,
            StringUtil::specialchars($lang['template_setup_copied'] ?? ''),
            StringUtil::specialchars($lang['template_setup_copy'] ?? ''),
        );
        $items = '';

        foreach ($steps as $step) {
            $html = StringUtil::specialchars($step);
            $html = (string) preg_replace('~(https://[^\s<]+[^\s<.,;:)])~', '<a href="$1" target="_blank" rel="noopener" style="text-decoration:underline">$1</a>', $html);
            $html = (string) preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
            $items .= '<li>'.str_replace('{redirect_uri}', $copy, $html).'</li>';
        }

        $GLOBALS['TL_CSS']['neo_superagent_backend'] = 'bundles/neosuperagent/backend.css|static';
        $GLOBALS['TL_JAVASCRIPT']['neo_superagent_backend'] = 'bundles/neosuperagent/backend.js|static';

        return sprintf(
            '<div class="widget clr nsa-setup"><h3>%s</h3><ol>%s</ol></div>',
            StringUtil::specialchars(sprintf($lang['template_setup'] ?? '%s', $template->name)),
            $items,
        );
    }

    /**
     * save_callback of the template: fills name, address and sign-in of the
     * template where the form leaves them empty (the fields follow in the form).
     */
    public function applyTemplate(mixed $value): string
    {
        $template = $this->templates->get((string) $value);

        if (null !== $template) {
            if ('' === trim((string) $this->input->post('url'))) {
                $this->input->setPost('url', $template->url);
                $this->input->setPost('auth', $template->auth);
            }

            if ('' === trim((string) $this->input->post('name'))) {
                $this->input->setPost('name', $template->name);
            }
        }

        return (string) $value;
    }

    /** onload: the connections and the log are for administrators only. */
    public function adminOnly(): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin) {
            throw new AccessDeniedException('Only administrators may open the connections of the SuperAgent.');
        }
    }

    /**
     * onload: the warning above the list of connections. In the edit form it is
     * the first block of the form (warningBox): a long message in the message
     * area ends up in the sticky header after saving and is cut off there.
     */
    public function warning(): void
    {
        if ('' !== (string) $this->input->query('act')) {
            return;
        }

        Message::addRaw(sprintf('<p class="tl_info">%s</p>', StringUtil::specialchars($GLOBALS['TL_LANG'][self::TABLE]['warning'] ?? '')));
    }

    /** The warning as the first block of the edit form. */
    public function warningBox(): string
    {
        return sprintf('<div class="widget clr"><div class="tl_message"><p class="tl_info">%s</p></div></div>', StringUtil::specialchars($GLOBALS['TL_LANG'][self::TABLE]['warning'] ?? ''));
    }

    /** @param array<string, mixed> $row */
    public function label(array $row, string $label): string
    {
        $connection = ConnectionModel::findByPk((int) $row['id']);

        if (null === $connection) {
            return $label;
        }

        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $tools = $this->connections->tools($connection);
        $free = \count(array_diff(array_keys($tools), $this->connections->locked($connection)));

        $state = match (true) {
            $this->connections->isActive($connection) => sprintf($lang['badge_active'] ?? '%d/%d', $free, \count($tools)),
            0 === $this->connections->toolData($connection)['read_at'] => $lang['badge_unread'] ?? '',
            default => $lang['badge_unconfirmed'] ?? '',
        };

        return $label.' <span class="label-info" style="margin-left:6px">'.StringUtil::specialchars($state).'</span>';
    }

    /** save_callback of the address: HTTPS only (private networks are refused when connecting). */
    public function checkUrl(mixed $value): string
    {
        $value = trim((string) $value);

        if ('https' !== strtolower((string) parse_url($value, PHP_URL_SCHEME)) || '' === (string) parse_url($value, PHP_URL_HOST)) {
            throw new \RuntimeException($GLOBALS['TL_LANG'][self::TABLE]['url_https'] ?? 'HTTPS only.');
        }

        return $value;
    }

    public function maskSecret(mixed $value): string
    {
        return '' !== (string) $value ? self::MASK : '';
    }

    /** An empty field or the unchanged mask keeps the stored value; a new value is stored encrypted. */
    public function saveSecret(mixed $value, DataContainer $dc): string
    {
        $value = ltrim(trim((string) $value), '•');

        if ('' === $value) {
            return (string) $this->db->fetchOne('SELECT '.$this->db->getDatabasePlatform()->quoteSingleIdentifier((string) $dc->field).' FROM '.self::TABLE.' WHERE id = ?', [(int) $dc->id]);
        }

        return $this->cipher->encrypt($value);
    }

    /** Header values masked like secrets; the names stay readable. */
    public function maskHeaders(mixed $value): string
    {
        $rows = StringUtil::deserialize((string) $value, true);

        foreach ($rows as &$row) {
            if (\is_array($row) && '' !== (string) ($row['value'] ?? '')) {
                $row['value'] = self::MASK;
            }
        }

        return serialize($rows);
    }

    public function saveHeaders(mixed $value, DataContainer $dc): string
    {
        $stored = [];

        foreach (StringUtil::deserialize((string) $this->db->fetchOne('SELECT headers FROM '.self::TABLE.' WHERE id = ?', [(int) $dc->id]), true) as $row) {
            if (\is_array($row)) {
                $stored[strtolower(trim((string) ($row['key'] ?? '')))] = (string) ($row['value'] ?? '');
            }
        }

        $rows = [];

        foreach (StringUtil::deserialize((string) $value, true) as $row) {
            $name = trim((string) ($row['key'] ?? ''));

            if ('' === $name) {
                continue;
            }

            if (!preg_match('/^[A-Za-z0-9-]{1,64}$/', $name)) {
                throw new \RuntimeException(sprintf($GLOBALS['TL_LANG'][self::TABLE]['header_name'] ?? '%s', $name));
            }

            $plain = ltrim(trim((string) ($row['value'] ?? '')), '•');
            $rows[] = ['key' => $name, 'value' => '' === $plain ? ($stored[strtolower($name)] ?? '') : $this->cipher->encrypt($plain)];
        }

        return serialize($rows);
    }

    public function oauthStatus(DataContainer $dc): string
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $connection = ConnectionModel::findByPk((int) $dc->id);
        $status = null !== $connection ? $this->oauth->status($connection) : ['connected' => false];

        if ($status['connected']) {
            $state = sprintf('<p class="tl_confirm">%s</p>', StringUtil::specialchars(sprintf($lang['oauth_connected'] ?? '%s %s', $status['connected_by'], $this->date($status['connected_at']))));
            $buttons = $this->button('neo_mcp_connect', $lang['oauth_reconnect'] ?? '', true).' '.$this->button('neo_mcp_disconnect', $lang['oauth_disconnect'] ?? '');
        } else {
            $state = sprintf('<p class="tl_error">%s</p>', StringUtil::specialchars($lang['oauth_not_connected'] ?? ''));
            $buttons = $this->button('neo_mcp_connect', $lang['oauth_connect'] ?? '', true);
        }

        // A template that needs an own app at the service: say so before "Connect".
        if (null !== $connection && $this->oauth->needsOwnApp($connection)) {
            $help = (string) $this->connections->template($connection)?->oauth['app_help'];
            $state .= sprintf('<p class="tl_info">%s%s</p>', StringUtil::specialchars($lang['oauth_own_app'] ?? ''), '' !== $help ? ' '.$this->link($help) : '');
        }

        return sprintf(
            '<div class="widget clr"><h3>%s</h3><div class="tl_message">%s</div><p style="margin:8px 0">%s</p><p class="tl_help tl_tip">%s <code>%s</code></p></div>',
            StringUtil::specialchars($lang['oauth_heading'] ?? ''),
            $state,
            $buttons,
            StringUtil::specialchars($lang['oauth_redirect'] ?? ''),
            StringUtil::specialchars($this->redirectUri()),
        );
    }

    public function toolsStatus(DataContainer $dc): string
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $connection = ConnectionModel::findByPk((int) $dc->id);
        $data = null !== $connection ? $this->connections->toolData($connection) : ['read_at' => 0, 'url' => '', 'server' => '', 'tools' => []];

        if (0 === $data['read_at']) {
            $state = sprintf('<p class="tl_info">%s</p>', StringUtil::specialchars($lang['tools_unread'] ?? ''));
        } elseif (null !== $connection && $data['url'] !== trim((string) $connection->url)) {
            $state = sprintf('<p class="tl_error">%s</p>', StringUtil::specialchars($lang['tools_url_changed'] ?? ''));
        } else {
            $state = sprintf('<p class="tl_confirm">%s</p>', StringUtil::specialchars(sprintf($lang['tools_read'] ?? '%d %s %s', \count($data['tools']), $data['server'] ?: '–', $this->date($data['read_at']))));
        }

        return sprintf('<div class="widget clr"><div class="tl_message">%s</div><p style="margin:8px 0">%s</p></div>', $state, $this->button('neo_mcp_read', $lang['tools_reread'] ?? ''));
    }

    /** The tools of the server with their kind and a lock checkbox each. */
    public function toolLocks(DataContainer $dc): string
    {
        $connection = ConnectionModel::findByPk((int) $dc->id);

        if (null === $connection) {
            return '';
        }

        $tools = $this->connections->tools($connection);

        if ([] === $tools) {
            return '';
        }

        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $locked = $this->connections->locked($connection);
        $new = $this->connections->toolData($connection)['new'];
        $template = $this->connections->template($connection);
        $rows = '';
        $i = 0;

        foreach ($tools as $name => $tool) {
            $id = 'neo_mcp_lock_'.++$i;
            $notes = [];

            if (\in_array($name, $new, true)) {
                $notes[] = '<strong>'.StringUtil::specialchars($lang['tool_new'] ?? '').'</strong>';
            }

            if ($template?->isRecommended($name)) {
                $notes[] = '★ '.StringUtil::specialchars($lang['tool_recommended'] ?? '');
            }

            if ($template?->isCustomerData($name)) {
                $notes[] = '<strong>'.StringUtil::specialchars($lang['tool_customer_data'] ?? '').'</strong>';
            }

            if ($tool->asksForPersonalData()) {
                $notes[] = StringUtil::specialchars($lang['tool_personal'] ?? '');
            }

            $description = mb_substr($tool->description ?: $tool->title, 0, 180);
            $kind = $this->connections->kindOf($connection, $tool);

            $rows .= sprintf(
                '<tr><td class="tl_file_list" style="width:1%%;white-space:nowrap"><input type="checkbox" class="tl_checkbox" name="neo_mcp_lock[]" id="%1$s" value="%2$s"%3$s> <label for="%1$s">%4$s</label></td>'
                .'<td class="tl_file_list"><strong>%5$s</strong><br><span style="color:#6a6a6a">%6$s</span></td>'
                .'<td class="tl_file_list" style="white-space:nowrap">%7$s</td><td class="tl_file_list">%8$s</td></tr>',
                $id,
                StringUtil::specialchars($name),
                \in_array($name, $locked, true) ? ' checked' : '',
                StringUtil::specialchars($lang['tool_locked'] ?? ''),
                StringUtil::specialchars($name),
                StringUtil::specialchars($description),
                StringUtil::specialchars($lang['kind_options'][$kind] ?? $kind),
                implode(' · ', $notes),
            );
        }

        return sprintf(
            '<div class="widget clr"><h3>%s</h3>'
            .'<div class="tl_checkbox_single_container" style="margin:8px 0"><input type="checkbox" class="tl_checkbox" name="neo_mcp_lock_all" id="neo_mcp_lock_all" value="1" style="margin-right:8px"><label for="neo_mcp_lock_all">%s</label></div>'
            .'<input type="hidden" name="neo_mcp_locks" value="1">'
            .'<table class="tl_listing" style="width:100%%"><thead><tr><th class="tl_folder_tlist">%s</th><th class="tl_folder_tlist">%s</th><th class="tl_folder_tlist">%s</th><th class="tl_folder_tlist">%s</th></tr></thead><tbody>%s</tbody></table>'
            .'<p class="tl_help tl_tip">%s</p></div>',
            StringUtil::specialchars($lang['locks_heading'] ?? ''),
            StringUtil::specialchars($lang['lock_all'] ?? ''),
            StringUtil::specialchars($lang['col_locked'] ?? ''),
            StringUtil::specialchars($lang['col_tool'] ?? ''),
            StringUtil::specialchars($lang['col_kind'] ?? ''),
            StringUtil::specialchars($lang['col_note'] ?? ''),
            $rows,
            StringUtil::specialchars($lang['locks_help'] ?? ''),
        );
    }

    public function confirmField(DataContainer $dc): string
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $connection = ConnectionModel::findByPk((int) $dc->id);
        $confirmed = null !== $connection && (int) $connection->confirmed_at > 0;

        $state = $confirmed
            ? sprintf('<p class="tl_confirm">%s</p>', StringUtil::specialchars(sprintf($lang['confirmed'] ?? '%s %s', (string) $connection->confirmed_by, $this->date((int) $connection->confirmed_at))))
            : sprintf('<p class="tl_error">%s</p>', StringUtil::specialchars($lang['not_confirmed'] ?? ''));

        return sprintf(
            '<div class="widget clr"><div class="tl_message">%s</div><div class="tl_checkbox_single_container" style="margin-top:10px"><input type="checkbox" class="tl_checkbox" name="neo_mcp_confirm" id="neo_mcp_confirm" value="1" style="margin-right:8px"><label for="neo_mcp_confirm">%s</label></div><p class="tl_help tl_tip">%s</p></div>',
            $state,
            StringUtil::specialchars($lang['confirm_check'] ?? ''),
            StringUtil::specialchars($lang['confirm_help'] ?? ''),
        );
    }

    /**
     * onsubmit: locks and confirmation, sign-out and sign-in, reading the tools.
     * The tools are read again when the address changed or on "Read tools again".
     */
    public function submit(DataContainer $dc): void
    {
        $connection = ConnectionModel::findByPk((int) $dc->id);
        $user = $this->security->getUser();

        // A choice that reloads the form (template, sign-in) is no save: no
        // reading, no sign-in, no skill.
        if (null === $connection || !$user instanceof BackendUser || 'auto' === $this->input->post('SUBMIT_TYPE')) {
            return;
        }

        // The form fields loaded the record before it was saved: read the saved values.
        $connection->refresh();
        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $data = $this->connections->toolData($connection);
        $url = trim((string) $connection->url);

        // Another server: the sign-in of the old one does not apply.
        if ('' !== $data['url'] && $data['url'] !== $url) {
            $this->oauth->forget($connection);
        }

        if ('1' === $this->input->post('neo_mcp_disconnect')) {
            $this->oauth->forget($connection);
            Message::addConfirmation($lang['oauth_forgotten'] ?? '');
        }

        $confirm = '1' === $this->input->post('neo_mcp_confirm');

        if ('1' === $this->input->post('neo_mcp_locks')) {
            $this->applyLocks($connection, $confirm, (string) $user->username);
        }

        if ($confirm) {
            $this->db->update(self::TABLE, ['confirmed_at' => time(), 'confirmed_by' => mb_substr((string) $user->username, 0, 64)], ['id' => (int) $connection->id]);
        }

        if ('1' === $this->input->post('neo_mcp_connect') && ConnectionModel::AUTH_OAUTH === (string) $connection->auth) {
            try {
                throw new RedirectResponseException($this->oauth->start($connection, $this->redirectUri()));
            } catch (McpException $e) {
                Message::addError(sprintf($lang['oauth_failed'] ?? '%s', $e->getMessage()));

                return;
            }
        }

        $connection->refresh();

        if (('1' === $this->input->post('neo_mcp_read') || $this->connections->toolData($connection)['url'] !== $url) && $this->ready($connection)) {
            $this->read($connection);
        }

        $this->installTemplateSkill($connection, (string) $user->username);

        // Installed skills of templates take over a new text of their file (also done by a migration after updates).
        System::loadLanguageFile('tl_neo_agent_skill');

        foreach ($this->templateSkills->refresh() as $name) {
            Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_neo_agent_skill']['taken_over'] ?? '%s', $name));
        }
    }

    /**
     * The skills a template brings are installed with the connection (each once):
     * they come from the bundle or the project like the template, so they need no
     * confirmation; editors tick them at the assistant like any installed skill.
     * A skill may depend on another skill of the same template.
     */
    private function installTemplateSkill(ConnectionModel $connection, string $username): void
    {
        $template = $this->connections->template($connection);

        if (null === $template || [] === $template->skillFiles) {
            return;
        }

        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $files = [];

        foreach ($template->skillFiles as $reference => $path) {
            if (false !== $this->db->fetchOne('SELECT id FROM '.InstalledSkills::TABLE." WHERE source = 'template' AND url = ?", [$reference])) {
                continue;
            }

            try {
                $content = $this->skillSource->fromFile($path)['content'];
            } catch (SkillSourceException $e) {
                Message::addError(sprintf($lang['template_skill_failed'] ?? '%s %s', $template->name, SkillCallbacks::sourceMessage($e)));

                continue;
            }

            $files[$reference] = [SkillFile::parse($content), $content];
        }

        if ([] === $files) {
            return;
        }

        $shipped = array_keys(SkillPackage::load(ChatHandler::skillDir(), ChatTools::NAMES)->modules());
        $installed = array_map('strval', $this->db->fetchFirstColumn('SELECT name FROM '.InstalledSkills::TABLE." WHERE name != ''"));
        $own = array_map(static fn (array $entry): string => $entry[0]->name, array_values($files));

        foreach ($files as $reference => [$file, $content]) {
            // Taken: every other name (also the other skills of this template); a
            // dependency may be one of those other skills.
            $others = array_values(array_diff($own, [$file->name]));
            $check = $file->check(['core', ...$shipped, ...$installed, ...$others], [...$shipped, ...$installed, ...$others], []);

            if ([] !== $check['errors']) {
                Message::addError(sprintf($lang['template_skill_failed'] ?? '%s %s', $template->name, implode(' ', array_map(SkillCallbacks::checkMessage(...), $check['errors']))));

                continue;
            }

            $this->db->insert(InstalledSkills::TABLE, [
                'tstamp' => time(),
                'source' => 'template',
                'url' => $reference,
                'name' => $file->name,
                'description' => $file->description,
                'body' => $file->body,
                'tools' => serialize($file->tools),
                'depends' => serialize($file->depends),
                'version_hash' => hash('sha256', $content),
                'version_at' => time(),
                'installed_by' => mb_substr($username, 0, 64),
            ]);

            Message::addConfirmation(sprintf($lang['template_skill_installed'] ?? '%s', $file->name));
        }
    }

    private function link(string $url): string
    {
        // Underlined: in the back end a link has the colour of the text (WCAG 1.4.1).
        return sprintf('<a href="%s" target="_blank" rel="noopener" style="text-decoration:underline">%s</a>', StringUtil::specialchars($url), StringUtil::specialchars($url));
    }

    /** Reads the tools and reports the outcome (also after the OAuth sign-in). */
    public function read(ConnectionModel $connection): void
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];

        try {
            $tools = $this->connections->readTools($connection);
            $locked = \count(array_intersect(array_map(static fn (McpTool $t): string => $t->name, $tools), $this->connections->locked($connection)));
            Message::addConfirmation(sprintf($lang['tools_done'] ?? '%d %d', \count($tools), $locked));
        } catch (McpException $e) {
            Message::addError(sprintf($lang['tools_failed'] ?? '%s', $e->getMessage()));
        }
    }

    /**
     * label_callback of the tool log (columns).
     *
     * @param array<string, mixed> $row
     * @param list<string>         $args
     *
     * @return list<string>
     */
    public function logLabel(array $row, string $label, DataContainer $dc, array $args): array
    {
        $lang = $GLOBALS['TL_LANG']['tl_neo_agent_tool_log'];
        $args[0] = $this->date((int) $row['tstamp']);
        $status = $lang['status_options'][(string) $row['status']] ?? (string) $row['status'];
        $note = (string) ($row['note'] ?? '');
        $args[4] = '' !== $note ? $status.' ('.($lang['note_options'][$note] ?? $note).')' : $status;
        $args[5] = System::getFormattedNumber(((int) $row['duration']) / 1000, 1).' s';

        return $args;
    }

    public function redirectUri(): string
    {
        return $this->router->generate('neo_superagent_oauth_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Locks as ticked in the list. Lifting a lock needs the confirmation in the
     * same save; without it the tool stays locked. Who lifted which lock when is
     * kept.
     */
    private function applyLocks(ConnectionModel $connection, bool $confirm, string $username): void
    {
        $lang = $GLOBALS['TL_LANG'][self::TABLE];
        $tools = $this->connections->tools($connection);
        $before = $this->connections->locked($connection);
        $wanted = array_values(array_intersect(array_map('strval', (array) $this->input->post('neo_mcp_lock')), array_keys($tools)));

        if ('1' === $this->input->post('neo_mcp_lock_all')) {
            foreach ($tools as $name => $tool) {
                if ($this->connections->lockedByDefault($connection, $tool)) {
                    $wanted[] = $name;
                }
            }
        }

        $unlocked = array_values(array_diff($before, $wanted));

        if ([] !== $unlocked && !$confirm) {
            $wanted = [...$wanted, ...$unlocked];
            Message::addError(sprintf($lang['unlock_confirm'] ?? '%s', implode(', ', $unlocked)));
            $unlocked = [];
        }

        $log = json_decode((string) $connection->unlocks, true);
        $log = \is_array($log) ? $log : [];

        foreach ($unlocked as $name) {
            $log[$name] = ['user' => $username, 'time' => time()];
        }

        $this->db->update(self::TABLE, [
            'locked' => serialize(array_values(array_unique($wanted))),
            'unlocks' => json_encode($log, JSON_UNESCAPED_UNICODE),
        ], ['id' => (int) $connection->id]);
    }

    /** Whether the credentials of the chosen authentication are there. */
    private function ready(ConnectionModel $connection): bool
    {
        return match ((string) $connection->auth) {
            ConnectionModel::AUTH_KEY => '' !== (string) $connection->api_key,
            ConnectionModel::AUTH_HEADERS => [] !== $this->connections->headerRows($connection),
            ConnectionModel::AUTH_OAUTH => $this->oauth->status($connection)['connected'],
            default => true,
        };
    }

    /**
     * A submit button; $leave for buttons that lead to another site (no Turbo, the browser follows the redirect).
     * data-neo-action (Basic's actions.js): a spinner while it runs, the button keeps its place and its message
     * stands right below it.
     */
    private function button(string $name, string $label, bool $leave = false): string
    {
        return sprintf('<button type="submit" name="%s" value="1" class="tl_submit" data-neo-action%s>%s</button>', $name, $leave ? ' data-turbo="false"' : '', StringUtil::specialchars($label));
    }

    private function date(int $timestamp): string
    {
        return $timestamp > 0 ? Date::parse((string) Config::get('datimFormat'), $timestamp) : '–';
    }
}
