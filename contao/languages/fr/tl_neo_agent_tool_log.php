<?php

declare(strict_types=1);

// Log of the tool calls of the connections (30 days, no arguments or results).
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['back_to_agents'] = ['Assistants', 'Retour à la liste des assistants.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['show'] = ['Détails', 'Afficher les détails.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tstamp'] = ['Heure', 'Heure de l’appel.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['agent'] = ['Assistant', 'L’assistant qui a appelé l’outil.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['connection'] = ['Connexion', 'La connexion de l’outil.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['tool'] = ['Outil', 'Nom de l’outil sur le serveur.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status'] = ['Résultat', 'Issue de l’appel.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note'] = ['Remarque', 'Complément au résultat.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['duration'] = ['Durée', 'Durée de l’appel.'];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'] = [
    'ok' => 'exécuté',
    'error' => 'erreur du serveur',
    'failed' => 'aucun résultat',
    'refused' => 'non exécuté',
];
$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'] = [
    'locked' => 'verrouillé',
    'auth' => 'authentification refusée',
    'unreachable' => 'serveur injoignable',
    'truncated' => 'résultat tronqué',
    'personal' => 'données personnelles non envoyées',
    'withheld' => 'résultat avec données personnelles retenu',
    'masked' => 'données personnelles masquées',
    'schema' => 'refusé par le service d’IA (description de l’outil)',
];
