<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Asistentes', 'Volver a la lista de asistentes.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Detalles', 'Mostrar los detalles.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Hora', 'Momento de la llamada.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Asistente', 'El asistente que llamó a la herramienta.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Conexión', 'La conexión de la herramienta.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Herramienta', 'Nombre de la herramienta en el servidor.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Resultado', 'Cómo terminó la llamada.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Nota', 'Complemento del resultado.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Duración', 'Duración de la llamada.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'ejecutada',
    'error' => 'error del servidor',
    'failed' => 'sin resultado',
    'refused' => 'no ejecutada',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'bloqueada',
    'auth' => 'acceso rechazado',
    'unreachable' => 'servidor no accesible',
    'truncated' => 'resultado recortado',
    'personal' => 'datos personales no enviados',
    'withheld' => 'resultado con datos personales retenido',
    'masked' => 'datos personales enmascarados',
    'schema' => 'rechazado por el servicio de IA (descripción de la herramienta)',
];
