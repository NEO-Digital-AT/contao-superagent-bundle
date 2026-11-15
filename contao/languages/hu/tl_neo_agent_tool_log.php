<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Asszisztensek', 'Vissza az asszisztensek listájához.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Részletek', 'A részletek megjelenítése.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Idő', 'A hívás időpontja.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Asszisztens', 'Az eszközt meghívó asszisztens.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Kapcsolat', 'Az eszköz kapcsolata.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Eszköz', 'Az eszköz neve a szerveren.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Eredmény', 'Hogyan végződött a hívás.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Megjegyzés', 'Kiegészítés az eredményhez.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Időtartam', 'A hívás időtartama.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'végrehajtva',
    'error' => 'szerverhiba',
    'failed' => 'nincs eredmény',
    'refused' => 'nem lett végrehajtva',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'zárolva',
    'auth' => 'bejelentkezés elutasítva',
    'unreachable' => 'a szerver nem érhető el',
    'truncated' => 'eredmény lerövidítve',
    'personal' => 'személyes adatok nem lettek elküldve',
    'withheld' => 'személyes adatokat tartalmazó eredmény visszatartva',
    'masked' => 'személyes adatok maszkolva',
    'schema' => 'az MI-szolgáltatás elutasította (az eszköz leírása)',
];
