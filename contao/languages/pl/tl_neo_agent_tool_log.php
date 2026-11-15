<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Asystenci', 'Powrót do listy asystentów.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Szczegóły', 'Pokaż szczegóły.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Czas', 'Czas wywołania.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Asystent', 'Asystent, który wywołał narzędzie.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Połączenie', 'Połączenie narzędzia.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Narzędzie', 'Nazwa narzędzia na serwerze.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Wynik', 'Jak zakończyło się wywołanie.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Uwaga', 'Uzupełnienie wyniku.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Czas trwania', 'Czas trwania wywołania.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'wykonane',
    'error' => 'błąd serwera',
    'failed' => 'brak wyniku',
    'refused' => 'niewykonane',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'zablokowane',
    'auth' => 'logowanie odrzucone',
    'unreachable' => 'serwer nieosiągalny',
    'truncated' => 'wynik skrócony',
    'personal' => 'dane osobowe nie zostały wysłane',
    'withheld' => 'wynik z danymi osobowymi wstrzymany',
    'masked' => 'dane osobowe zamaskowane',
    'schema' => 'odrzucone przez usługę AI (opis narzędzia)',
];
