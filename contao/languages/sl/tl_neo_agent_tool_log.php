<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Asistenti', 'Nazaj na seznam asistentov.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Podrobnosti', 'Prikaži podrobnosti.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Čas', 'Čas klica.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Asistent', 'Asistent, ki je poklical orodje.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Povezava', 'Povezava orodja.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Orodje', 'Ime orodja na strežniku.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Rezultat', 'Kako se je klic končal.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Opomba', 'Dodatek k rezultatu.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Trajanje', 'Trajanje klica.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'izvedeno',
    'error' => 'napaka strežnika',
    'failed' => 'brez rezultata',
    'refused' => 'ni izvedeno',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'zaklenjeno',
    'auth' => 'prijava zavrnjena',
    'unreachable' => 'strežnik ni dosegljiv',
    'truncated' => 'rezultat skrajšan',
    'personal' => 'osebni podatki niso poslani',
    'withheld' => 'rezultat z osebnimi podatki zadržan',
    'masked' => 'osebni podatki maskirani',
    'schema' => 'zavrnila storitev UI (opis orodja)',
];
