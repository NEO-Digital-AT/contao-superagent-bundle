<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Neo\SuperAgentBundle\Backend\ConnectionCallbacks;

/*
 * tl_neo_agent_tool_log — the tool calls of the connections (Mcp\ToolLog):
 * assistant, connection, tool, result and duration, no arguments and no
 * results. Read only, for administrators; kept 30 days.
 */
$GLOBALS['TL_DCA']['tl_neo_agent_tool_log'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'closed' => true,
        'notEditable' => true,
        'notCopyable' => true,
        'notDeletable' => true,
        'onload_callback' => [[ConnectionCallbacks::class, 'adminOnly']],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'tstamp' => 'index',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['tstamp DESC'],
            'flag' => DataContainer::SORT_DAY_DESC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['tstamp', 'agent', 'connection', 'tool', 'status', 'duration'],
            'showColumns' => true,
            'label_callback' => [ConnectionCallbacks::class, 'logLabel'],
        ],
        'global_operations' => [
            'back_to_agents' => [
                'href' => 'table=tl_neo_agent',
                // The class draws Contao's back icon; an own icon would show a second arrow.
                'class' => 'header_back',
                'primary' => true,
            ],
        ],
        'operations' => [
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],

    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'tstamp' => ['flag' => DataContainer::SORT_DAY_DESC, 'eval' => ['rgxp' => 'datim'], 'sql' => 'int(10) unsigned NOT NULL default 0'],
        'agent' => [
            'filter' => true,
            'foreignKey' => 'tl_neo_agent.title',
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'connection' => [
            'filter' => true,
            'foreignKey' => 'tl_neo_agent_connection.name',
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'tool' => ['search' => true, 'sql' => "varchar(128) NOT NULL default ''"],
        'status' => [
            'filter' => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['status_options'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'note' => [
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent_tool_log']['note_options'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'duration' => ['sql' => 'int(10) unsigned NOT NULL default 0'],
    ],
];
