<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

/*
 * tl_neo_agent_language — per-language conversational settings, as child records of
 * tl_neo_agent (parent view). One row per language holds the greeting and UI texts,
 * the starter questions and the questions & answers. Knowledge base, system
 * prompt, model and key live on the parent agent and are shared across languages.
 */
$GLOBALS['TL_DCA']['tl_neo_agent_language'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_neo_agent',
        'enableVersioning' => true,
        'onload_callback' => [
            // One language per assistant: no second record (new or copy).
            [\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'oneLanguage'],
            [\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'placeholders'],
            // Rows of question and answer: Contao's row wizard, before Contao has it the key-value wizard.
            [\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'pairWizards'],
        ],
        // A record without texts gets the suggestions of its language; "Load suggestions" fills empty fields.
        'onsubmit_callback' => [[\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'fillSuggestions']],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['language'],
            'headerFields' => ['title', 'assistant_name'],
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['language', 'greeting'],
            'format' => '<strong>%s</strong> – %s',
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'copy' => ['href' => 'act=copy', 'icon' => 'copy.svg'],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],

    'edit' => [
        'buttons_callback' => [[\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'suggestionButton']],
    ],
    'palettes' => [
        'default' =>
            '{language_legend},language;'
            .'{identity_legend},subtitle,greeting,fact_note,input_placeholder;'
            .'{ui_legend},starter_questions,canned;'
            // Every fixed text of the widget, so any language works without a
            // translation file; empty = the default shown as placeholder.
            .'{labels_legend:hide},'.implode(',', array_map(static fn (string $k): string => 'ui_'.$k, \Neo\SuperAgentBundle\Model\AgentLanguageModel::UI_KEYS)).';',
    ],

    'fields' => [
        'id' => ['sql' => 'int(10) unsigned NOT NULL auto_increment'],
        'pid' => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'tstamp' => ['sql' => 'int(10) unsigned NOT NULL default 0'],
        'sorting' => ['sql' => 'int(10) unsigned NOT NULL default 0'],

        // Choice of every language Contao knows (primary codes), never typed by hand.
        'language' => [
            'inputType' => 'select',
            'exclude' => true,
            'search' => true,
            'options_callback' => [\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'languages'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],

        'subtitle' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'greeting' => [
            'inputType' => 'textarea',
            'exclude' => true,
            'eval' => ['rows' => 2, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'fact_note' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 255, 'tl_class' => 'long clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'input_placeholder' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 128, 'tl_class' => 'long'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],

        // The questions a visitor can click at the start of a conversation, each with an optional answer the
        // assistant gives for it (in its own words); without an answer it answers on its own.
        'starter_questions' => [
            'inputType' => 'rowWizard',
            'exclude' => true,
            'fields' => [
                'question' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_neo_agent_language']['starter_q'],
                    'inputType' => 'text',
                ],
                'answer' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_neo_agent_language']['starter_a'],
                    'inputType' => 'text',
                ],
            ],
            'eval' => ['tl_class' => 'clr'],
            // Never stored as NULL after a save: NULL means "not converted yet" (StarterQuestionsMigration).
            'load_callback' => [[\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'loadPairs']],
            'save_callback' => [[\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'savePairs']],
            'sql' => 'text NULL',
        ],
        // Former suggested questions (one per row), read until StarterQuestionsMigration has turned them into
        // starter questions; kept as they are.
        'suggestions' => [
            'sql' => 'blob NULL',
        ],
        'canned' => [
            'inputType' => 'rowWizard',
            'exclude' => true,
            'fields' => [
                'question' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_neo_agent_language']['canned_q'],
                    'inputType' => 'text',
                ],
                'answer' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_neo_agent_language']['canned_a'],
                    'inputType' => 'text',
                ],
            ],
            'eval' => ['tl_class' => 'clr'],
            'load_callback' => [[\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'loadPairs']],
            'save_callback' => [[\Neo\SuperAgentBundle\Backend\LanguageCallbacks::class, 'savePairs']],
            'sql' => 'text NULL',
        ],
        // No longer used: the model words every reply. The column stays for existing installations.
        'fallback_text' => [
            'sql' => 'text NULL',
        ],

        // Fixed widget texts per language (see AgentLanguageModel::UI_KEYS).
        // Required notice that visitors write with an AI (EU AI Act, Art. 50); empty = default text, never blank.
        'ui_ai_notice' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        // Privacy mode: hint below the input and reply to a rejected message; no medical advice: the fixed reply.
        'ui_privacy_hint' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_privacy_reject' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_medical_refusal' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        // Button below a reply that opens a page (%title% = page title), and the button that starts a new conversation.
        'ui_open_page' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_new_chat' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_suggestions' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_fab' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_open' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_close' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_gate_title' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_gate_body' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_gate_button' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        // Instead of the consent request when the consent tool of the page never asks for the category.
        'ui_gate_unavailable' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_send' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_handoff' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_email' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_message' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_email_placeholder' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_message_placeholder' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_submit' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_back' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_done' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_error' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        // Voice input (edition "pro"): button, stop button, status while recording (%time%), error.
        'ui_esc_name' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_phone' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_country_code' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_country_search' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_email_invalid' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_email_suggest' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_company' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_consent' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_privacy_link' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_newsletter' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_missing' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_esc_phone_invalid' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_status_thinking' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_status_pages' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_status_form' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_status_search' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_mic' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_mic_stop' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_mic_recording' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_mic_error' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_mic_busy' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_title' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_found' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_body' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_send' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_edit' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_email' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_phone' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_iban' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_card' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_svnr' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_pii_birthdate' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_file_download' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
        'ui_file_used' => ['inputType' => 'text', 'exclude' => true, 'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'], 'sql' => "varchar(255) NOT NULL default ''"],
    ],
];
