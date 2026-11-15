<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Assistants', 'Back to the list of assistants.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Details', 'Show the details.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Time', 'Time of the call.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Assistant', 'The assistant that called the tool.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Connection', 'The connection of the tool.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Tool', 'Name of the tool at the server.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Result', 'How the call ended.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Note', 'Addition to the result.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Duration', 'Duration of the call.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'run',
    'error' => 'error of the server',
    'failed' => 'no result',
    'refused' => 'not run',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'locked',
    'auth' => 'sign-in refused',
    'unreachable' => 'server not reachable',
    'truncated' => 'result cut',
    'personal' => 'personal data not sent',
    'withheld' => 'result with personal data held back',
    'masked' => 'personal data masked',
    'schema' => 'refused by the AI service (description of the tool)',
];
