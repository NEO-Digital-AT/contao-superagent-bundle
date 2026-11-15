<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Assistenten', 'Terug naar de lijst met assistenten.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Details', 'Details tonen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Tijd', 'Tijdstip van de aanroep.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Assistent', 'De assistent die de tool aanriep.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Verbinding', 'De verbinding van de tool.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Tool', 'Naam van de tool bij de server.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Resultaat', 'Hoe de aanroep afliep.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Opmerking', 'Aanvulling op het resultaat.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Duur', 'Duur van de aanroep.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'uitgevoerd',
    'error' => 'fout van de server',
    'failed' => 'geen resultaat',
    'refused' => 'niet uitgevoerd',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'vergrendeld',
    'auth' => 'aanmelding geweigerd',
    'unreachable' => 'server niet bereikbaar',
    'truncated' => 'resultaat ingekort',
    'personal' => 'persoonsgegevens niet verstuurd',
    'withheld' => 'resultaat met persoonsgegevens tegengehouden',
    'masked' => 'persoonsgegevens gemaskeerd',
    'schema' => 'geweigerd door de AI-dienst (beschrijving van de tool)',
];
