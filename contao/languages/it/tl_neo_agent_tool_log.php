<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Assistenti', 'Torna all’elenco degli assistenti.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Dettagli', 'Mostra i dettagli.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Ora', 'Momento della chiamata.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Assistente', 'L’assistente che ha richiamato lo strumento.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Connessione', 'La connessione dello strumento.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Strumento', 'Nome dello strumento sul server.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Esito', 'Come è terminata la chiamata.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Nota', 'Aggiunta all’esito.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Durata', 'Durata della chiamata.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'eseguito',
    'error' => 'errore del server',
    'failed' => 'nessun risultato',
    'refused' => 'non eseguito',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'bloccato',
    'auth' => 'accesso rifiutato',
    'unreachable' => 'server non raggiungibile',
    'truncated' => 'risultato troncato',
    'personal' => 'dati personali non inviati',
    'withheld' => 'risultato con dati personali trattenuto',
    'masked' => 'dati personali mascherati',
    'schema' => 'rifiutato dal servizio di IA (descrizione dello strumento)',
];
