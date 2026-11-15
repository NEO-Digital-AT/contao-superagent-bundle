<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\BackendUser;
use Contao\DataContainer;
use Contao\StringUtil;
use Neo\BasicBundle\Ai\ModelCatalog;
use Neo\SuperAgentBundle\Chat\TestedModels;
use Neo\SuperAgentBundle\Mcp\Connections;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Model\ConnectionModel;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The tools of the connections at the assistant (field mcp_tools, edition
 * Plus): a list per connection of the tools that are not locked, nothing
 * ticked by default, and notes on what will not work.
 */
final class AgentToolCallbacks
{
    public function __construct(
        private readonly Connections $connections,
        private readonly ModelCatalog $models,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    /**
     * Tools that are not locked, grouped by connection. A ticked tool that is
     * locked now or no longer listed stays in the list, marked, so the value is
     * not lost unnoticed; the chat does not use it.
     *
     * @return array<string, array<string, string>>
     */
    public function options(DataContainer|null $dc = null): array
    {
        $lang = $GLOBALS['TL_LANG']['tl_neo_agent'];
        $selected = [];

        if (null !== $dc && $dc->id) {
            $agent = AgentModel::findByPk($dc->id);
            $selected = null !== $agent ? array_map('strval', StringUtil::deserialize((string) $agent->mcp_tools, true)) : [];
        }

        $options = [];
        $known = [];

        foreach (ConnectionModel::findAll(['order' => 'name']) ?? [] as $connection) {
            $active = $this->connections->isActive($connection);
            $locked = $this->connections->locked($connection);
            $group = (string) $connection->name.($active ? '' : ' ('.($lang['mcp_inactive'] ?? '').')');

            foreach ($this->connections->tools($connection) as $name => $tool) {
                $value = $connection->id.':'.$name;
                $known[$value] = true;
                $isLocked = \in_array($name, $locked, true);

                if ($isLocked && !\in_array($value, $selected, true)) {
                    continue;
                }

                $description = mb_substr($tool->description ?: $tool->title, 0, 120);
                $options[$group][$value] = $name.('' !== $description ? ' – '.$description : '').($isLocked ? ' ('.($lang['mcp_locked_tool'] ?? '').')' : '');
            }
        }

        foreach ($selected as $value) {
            if (!isset($known[$value])) {
                $options[$lang['mcp_gone_group'] ?? ''][$value] = $value;
            }
        }

        return $options;
    }

    /** Notes below the list: no connection yet, privacy mode, a model without tool calling or not tested with tools. */
    public function note(DataContainer $dc): string
    {
        $lang = $GLOBALS['TL_LANG']['tl_neo_agent'];
        $agent = AgentModel::findByPk($dc->id);

        if (null === $agent) {
            return '';
        }

        $notes = [];

        if (null === ConnectionModel::findAll()) {
            $text = StringUtil::specialchars($lang['mcp_none'] ?? '');

            if ($this->isAdmin()) {
                $text .= sprintf(' <a href="%s">%s</a>', StringUtil::specialchars($this->router->generate('contao_backend', ['do' => 'neo_agent', 'table' => 'tl_neo_agent_connection'])), StringUtil::specialchars($lang['mcp_open_connections'] ?? ''));
            }

            $notes[] = ['tl_info', $text];
        }

        $allowed = $this->connections->allowedFor($agent);

        if ([] !== $allowed) {
            $personal = array_filter($allowed, static fn (array $entry): bool => $entry['tool']->asksForPersonalData());

            if ($agent->privacy_mode && [] !== $personal) {
                $notes[] = ['tl_error', StringUtil::specialchars(sprintf($lang['mcp_privacy'] ?? '%s', implode(', ', array_map(static fn (array $e): string => $e['tool']->name, $personal))))];
            }

            if (false === $this->models->capabilities((string) $agent->model)['tools']) {
                $notes[] = ['tl_error', StringUtil::specialchars($lang['mcp_model'] ?? '')];
            } else {
                // Tools of connections need a strong model; the tested ones are named.
                $tested = TestedModels::covers((string) $agent->model);
                $notes[] = [$tested ? 'tl_confirm' : 'tl_info', StringUtil::specialchars(sprintf($lang['mcp_models'] ?? '%s', TestedModels::names()).' '.($tested ? $lang['mcp_model_tested'] ?? '' : $lang['mcp_model_untested'] ?? ''))];
            }
        }

        $ticked = \count(StringUtil::deserialize((string) $agent->mcp_tools, true));

        if ($ticked > \count($allowed)) {
            $notes[] = ['tl_info', StringUtil::specialchars(sprintf($lang['mcp_unusable'] ?? '%d', $ticked - \count($allowed)))];
        }

        if ([] === $notes) {
            return '';
        }

        $html = '';

        foreach ($notes as [$class, $text]) {
            $html .= sprintf('<p class="%s">%s</p>', $class, $text);
        }

        return '<div class="widget clr"><div class="tl_message">'.$html.'</div></div>';
    }

    /** onload: the buttons "Connections", "Skills" and "Tool log" above the list only for administrators. */
    public function adminButtons(): void
    {
        if (!$this->isAdmin()) {
            unset(
                $GLOBALS['TL_DCA']['tl_neo_agent']['list']['global_operations']['connections'],
                $GLOBALS['TL_DCA']['tl_neo_agent']['list']['global_operations']['skill_list'],
                $GLOBALS['TL_DCA']['tl_neo_agent']['list']['global_operations']['tool_log'],
            );
        }
    }

    private function isAdmin(): bool
    {
        $user = $this->security->getUser();

        return $user instanceof BackendUser && $user->isAdmin;
    }
}
