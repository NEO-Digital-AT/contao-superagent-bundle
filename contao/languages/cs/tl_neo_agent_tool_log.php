<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Asistenti', 'Zpět na seznam asistentů.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Podrobnosti', 'Zobrazit podrobnosti.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Čas', 'Čas volání.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Asistent', 'Asistent, který nástroj zavolal.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Připojení', 'Připojení nástroje.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Nástroj', 'Název nástroje na serveru.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Výsledek', 'Jak volání skončilo.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Poznámka', 'Doplněk k výsledku.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Doba', 'Doba volání.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'provedeno',
    'error' => 'chyba serveru',
    'failed' => 'bez výsledku',
    'refused' => 'neprovedeno',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'zablokováno',
    'auth' => 'přihlášení odmítnuto',
    'unreachable' => 'server nedostupný',
    'truncated' => 'výsledek zkrácen',
    'personal' => 'osobní údaje neodeslány',
    'withheld' => 'výsledek s osobními údaji zadržen',
    'masked' => 'osobní údaje maskovány',
    'schema' => 'odmítnuto službou AI (popis nástroje)',
];
