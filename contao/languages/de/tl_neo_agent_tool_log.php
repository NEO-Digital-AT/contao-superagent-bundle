<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Assistenten', 'Zurück zur Liste der Assistenten.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Details', 'Details anzeigen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Zeit', 'Zeitpunkt des Aufrufs.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Assistent', 'Der Assistent, der das Werkzeug aufgerufen hat.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Verbindung', 'Die Verbindung des Werkzeugs.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Werkzeug', 'Name des Werkzeugs beim Server.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Ergebnis', 'Wie der Aufruf ausging.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Hinweis', 'Zusatz zum Ergebnis.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Dauer', 'Dauer des Aufrufs.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'ausgeführt',
    'error' => 'Fehler des Servers',
    'failed' => 'kein Ergebnis',
    'refused' => 'nicht ausgeführt',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'gesperrt',
    'auth' => 'Anmeldung abgelehnt',
    'unreachable' => 'Server nicht erreichbar',
    'truncated' => 'Ergebnis gekürzt',
    'personal' => 'persönliche Daten nicht gesendet',
    'withheld' => 'Ergebnis mit persönlichen Daten zurückgehalten',
    'masked' => 'persönliche Daten maskiert',
    'schema' => 'vom KI-Dienst abgelehnt (Beschreibung des Werkzeugs)',
];
