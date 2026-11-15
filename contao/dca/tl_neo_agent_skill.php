<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Neo\SuperAgentBundle\Backend\ConnectionCallbacks;
use Neo\SuperAgentBundle\Backend\SkillCallbacks;

/*
 * tl_neo_agent_skill — skills installed by administrators (edition Plus), from
 * a GitHub repository or a file of the file manager (SKILL.md or ZIP). "Load
 * and check" fetches the SKILL.md and checks it; the text is taken over only
 * after the administrator confirmed it, and stays fixed until an administrator
 * takes over a new version. The chat uses the stored text only.
 */
$GLOBALS['TL_DCA']['tl_neo_agent_skill'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'onload_callback' => [
            [ConnectionCallbacks::class, 'adminOnly'],
            [SkillCallbacks::class, 'shipped'],
        ],
        'onsubmit_callback' => [[SkillCallbacks::class, 'submit']],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'name' => 'index',
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
            'fields' => ['name', 'description'],
            'format' => '<strong>%s</strong> <span style="color:#6a6a6a;padding-left:6px">%s</span>',
            'label_callback' => [SkillCallbacks::class, 'label'],
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
        '__selector__' => ['source'],
        'default' => '{source_legend},source;{check_legend},check',
    ],

    'subpalettes' => [
        'source_github' => 'url',
        'source_file' => 'file',
    ],

    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'tstamp' => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'source' => [
            'inputType' => 'radio',
            'options_callback' => [SkillCallbacks::class, 'sourceOptions'],
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent_skill']['source_options'],
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "varchar(8) NOT NULL default 'github'",
        ],
        'url' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 512, 'tl_class' => 'long clr'],
            'save_callback' => [[SkillCallbacks::class, 'checkUrl']],
            'sql' => "varchar(512) NOT NULL default ''",
        ],
        'file' => [
            'inputType' => 'fileTree',
            'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => 'md,zip', 'tl_class' => 'clr'],
            'sql' => 'binary(16) NULL',
        ],
        'check' => ['input_field_callback' => [SkillCallbacks::class, 'checkField']],
        // Taken over from the SKILL.md after the confirmation (SkillCallbacks::submit).
        'name' => ['search' => true, 'sql' => "varchar(64) NOT NULL default ''"],
        'description' => ['search' => true, 'sql' => "varchar(512) NOT NULL default ''"],
        'body' => ['sql' => 'text NULL'],
        'tools' => ['sql' => 'blob NULL'],
        'depends' => ['sql' => 'blob NULL'],
        'version_hash' => ['sql' => "varchar(64) NOT NULL default ''"],
        'version_at' => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'installed_by' => ['sql' => "varchar(64) NOT NULL default ''"],
        // Fetched and checked, not taken over yet (JSON).
        'pending' => ['sql' => 'mediumtext NULL'],
    ],
];
