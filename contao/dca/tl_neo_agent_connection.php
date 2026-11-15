<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Neo\SuperAgentBundle\Backend\ConnectionCallbacks;
use Neo\SuperAgentBundle\Model\ConnectionModel;

/*
 * tl_neo_agent_connection — connections to MCP servers (edition Plus). Only
 * administrators open this table (button "Connections" above the list of
 * assistants). After saving, the tools are read from the server; new tools that
 * change, delete or are not marked are locked. A connection is used only after
 * an administrator confirmed it; lifting a lock needs the same confirmation.
 * Credentials are stored encrypted and never shown again.
 */
$GLOBALS['TL_DCA']['tl_neo_agent_connection'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'onload_callback' => [
            [ConnectionCallbacks::class, 'adminOnly'],
            [ConnectionCallbacks::class, 'warning'],
            // The template field only when there are templates.
            [ConnectionCallbacks::class, 'hideTemplate'],
        ],
        'onsubmit_callback' => [[ConnectionCallbacks::class, 'submit']],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['name', 'url'],
            'format' => '%s <span style="color:#b3b3b3;padding-left:6px">%s</span>',
            'label_callback' => [ConnectionCallbacks::class, 'label'],
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
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],

    'palettes' => [
        '__selector__' => ['auth'],
        'default' => '{title_legend},warning,template,template_help,template_setup,name,url;'
            .'{auth_legend},auth;'
            .'{data_legend},personal_data;'
            .'{tools_legend},tools_status,tool_locks;'
            .'{confirm_legend},confirm',
    ],

    'subpalettes' => [
        'auth_'.ConnectionModel::AUTH_KEY => 'api_key',
        'auth_'.ConnectionModel::AUTH_HEADERS => 'headers',
        'auth_'.ConnectionModel::AUTH_OAUTH => 'oauth_status,oauth_client_id,oauth_client_secret',
    ],

    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'tstamp' => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'warning' => ['input_field_callback' => [ConnectionCallbacks::class, 'warningBox']],
        // A template fills address and sign-in and decides how the tools are treated.
        'template' => [
            'inputType' => 'select',
            'options_callback' => [ConnectionCallbacks::class, 'templateOptions'],
            'eval' => ['includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'save_callback' => [[ConnectionCallbacks::class, 'applyTemplate']],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'template_help' => ['input_field_callback' => [ConnectionCallbacks::class, 'templateHelp']],
        'template_setup' => ['input_field_callback' => [ConnectionCallbacks::class, 'templateSetup']],
        'name' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'url' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 512, 'tl_class' => 'w50'],
            'save_callback' => [[ConnectionCallbacks::class, 'checkUrl']],
            'sql' => "varchar(512) NOT NULL default ''",
        ],
        'auth' => [
            'inputType' => 'select',
            'options' => [ConnectionModel::AUTH_NONE, ConnectionModel::AUTH_KEY, ConnectionModel::AUTH_HEADERS, ConnectionModel::AUTH_OAUTH],
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'],
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'none'",
        ],
        'personal_data' => [
            'inputType' => 'select',
            'options' => [ConnectionModel::DATA_MASK, ConnectionModel::DATA_BLOCK, ConnectionModel::DATA_SEND],
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default 'mask'",
        ],
        'api_key' => [
            'inputType' => 'text',
            'eval' => ['hideInput' => true, 'decodeEntities' => true, 'maxlength' => 1024, 'doNotCopy' => true, 'tl_class' => 'w50 clr'],
            'load_callback' => [[ConnectionCallbacks::class, 'maskSecret']],
            'save_callback' => [[ConnectionCallbacks::class, 'saveSecret']],
            'sql' => 'text NULL',
        ],
        'headers' => [
            'inputType' => 'keyValueWizard',
            'eval' => ['decodeEntities' => true, 'doNotCopy' => true, 'tl_class' => 'clr'],
            'load_callback' => [[ConnectionCallbacks::class, 'maskHeaders']],
            'save_callback' => [[ConnectionCallbacks::class, 'saveHeaders']],
            'sql' => 'blob NULL',
        ],
        'oauth_status' => ['input_field_callback' => [ConnectionCallbacks::class, 'oauthStatus']],
        'oauth_client_id' => [
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'maxlength' => 255, 'doNotCopy' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'oauth_client_secret' => [
            'inputType' => 'text',
            'eval' => ['hideInput' => true, 'decodeEntities' => true, 'maxlength' => 1024, 'doNotCopy' => true, 'tl_class' => 'w50'],
            'load_callback' => [[ConnectionCallbacks::class, 'maskSecret']],
            'save_callback' => [[ConnectionCallbacks::class, 'saveSecret']],
            'sql' => 'text NULL',
        ],
        // Encrypted JSON (OAuthFlow): endpoints, registered client, tokens.
        'oauth_data' => ['eval' => ['doNotCopy' => true], 'sql' => 'text NULL'],
        'tools_status' => ['input_field_callback' => [ConnectionCallbacks::class, 'toolsStatus']],
        'tool_locks' => ['input_field_callback' => [ConnectionCallbacks::class, 'toolLocks']],
        // JSON as last read (Connections::readTools).
        'tools' => ['eval' => ['doNotCopy' => true], 'sql' => 'mediumtext NULL'],
        'locked' => ['eval' => ['doNotCopy' => true], 'sql' => 'blob NULL'],
        // Who lifted which lock when (JSON tool => {user, time}).
        'unlocks' => ['eval' => ['doNotCopy' => true], 'sql' => 'text NULL'],
        'confirm' => ['input_field_callback' => [ConnectionCallbacks::class, 'confirmField']],
        'confirmed_at' => ['eval' => ['doNotCopy' => true], 'sql' => 'int(10) unsigned NOT NULL default 0'],
        'confirmed_by' => ['eval' => ['doNotCopy' => true], 'sql' => "varchar(64) NOT NULL default ''"],
    ],
];
