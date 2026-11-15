<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Asistenti', 'Natrag na popis asistenata.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Pojedinosti', 'Prikaži pojedinosti.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Vrijeme', 'Vrijeme poziva.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Asistent', 'Asistent koji je pozvao alat.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Veza', 'Veza alata.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Alat', 'Naziv alata na poslužitelju.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Rezultat', 'Kako je poziv završio.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Napomena', 'Dodatak rezultatu.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Trajanje', 'Trajanje poziva.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'izvršeno',
    'error' => 'pogreška poslužitelja',
    'failed' => 'bez rezultata',
    'refused' => 'nije izvršeno',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'zaključano',
    'auth' => 'prijava odbijena',
    'unreachable' => 'poslužitelj nedostupan',
    'truncated' => 'rezultat skraćen',
    'personal' => 'osobni podaci nisu poslani',
    'withheld' => 'rezultat s osobnim podacima zadržan',
    'masked' => 'osobni podaci maskirani',
    'schema' => 'odbila AI usluga (opis alata)',
];
