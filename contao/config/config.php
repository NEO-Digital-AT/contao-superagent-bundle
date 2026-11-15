<?php

declare(strict_types=1);

use Neo\SuperAgentBundle\Backend\LanguageCallbacks;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Model\ConnectionModel;

/*
 * Back-end menu group "neo" (named by the Basic bundle): the module "neo_agent"
 * manages the assistants (tl_neo_agent) and their per-language records
 * (tl_neo_agent_language). Keys and the licence are entered on "Access keys and
 * licences" of the Basic bundle (button of the same name above the list). The
 * connections to MCP servers (tl_neo_agent_connection), the log of their
 * tool calls (tl_neo_agent_tool_log) and the installed skills
 * (tl_neo_agent_skill) are opened by administrators with the buttons above
 * the list (edition Plus).
 */
$GLOBALS['BE_MOD']['neo']['neo_agent'] = [
    'tables' => ['tl_neo_agent', 'tl_neo_agent_language', 'tl_neo_agent_connection', 'tl_neo_agent_tool_log', 'tl_neo_agent_skill'],
    // Creates the record of a missing website language with suggestions (warning above the list).
    'neo_add_language' => [LanguageCallbacks::class, 'addLanguage'],
];

$GLOBALS['TL_MODELS']['tl_neo_agent'] = AgentModel::class;
$GLOBALS['TL_MODELS']['tl_neo_agent_language'] = AgentLanguageModel::class;
$GLOBALS['TL_MODELS']['tl_neo_agent_connection'] = ConnectionModel::class;
