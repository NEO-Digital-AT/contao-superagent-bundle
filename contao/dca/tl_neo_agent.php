<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\StringUtil;
use Contao\System;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Neo\BasicBundle\Access\AccessPage;
use Neo\BasicBundle\Access\AccessSettings;
use Neo\BasicBundle\Ai\AiConnection;
use Neo\BasicBundle\Ai\ModelCatalog;
use Neo\SuperAgentBundle\Backend\AgentToolCallbacks;
use Neo\SuperAgentBundle\Backend\DependencyCallbacks;
use Neo\SuperAgentBundle\Backend\EditionCallbacks;
use Neo\SuperAgentBundle\Backend\FormCallbacks;
use Neo\SuperAgentBundle\Backend\LanguageCallbacks;
use Neo\SuperAgentBundle\Backend\ModelCallbacks;
use Neo\SuperAgentBundle\Backend\OptionCallbacks;
use Neo\SuperAgentBundle\Backend\PublishCallbacks;
use Neo\SuperAgentBundle\Backend\SkillCallbacks;
use Neo\SuperAgentBundle\Chat\ContactForm;
use Neo\SuperAgentBundle\Model\AgentModel;

/*
 * tl_neo_agent — one configurable AI assistant per row. Everything an operator
 * needs to run the front-end chat is editable here: identity, model, sampling,
 * system prompt, grounding source, escalation and the UI strings. Route and key
 * of the AI connection and the SerpAPI key are entered on "Access keys and
 * licences" (Basic bundle, contao/dca/tl_neo_access.php of this bundle).
 * Settings of an edition the licence does not include are not shown
 * (EditionCallbacks); their stored values stay as they are.
 */
$GLOBALS['TL_DCA']['tl_neo_agent'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        // The texts per language: copied with the assistant ("Duplicate") and deleted with it.
        'ctable' => ['tl_neo_agent_language'],
        'enableVersioning' => true,
        // A new assistant gets one record with suggestions per language of the website.
        'oncreate_callback' => [[LanguageCallbacks::class, 'createForSiteLanguages']],
        'onload_callback' => [
            // A language of the website without texts: say so, with a button that creates them.
            [LanguageCallbacks::class, 'missingLanguages'],
            // Terms not confirmed yet: no assistant for visitors, or the features of an edition locked.
            [PublishCallbacks::class, 'pendingMessage'],
            // Settings of an edition the licence does not include are left out; their values stay stored.
            [EditionCallbacks::class, 'hideUnlicensed'],
            // Settings without effect because of another setting, a missing key or the terms: disabled, the reason at the field.
            [DependencyCallbacks::class, 'lock'],
            /*
             * Model selection instead of typing: with a key stored on "Access keys
             * and licences" the model field becomes a searchable list of the route's
             * models (cached for an hour). Without a key, or when the route is
             * silent, it stays a text field so nobody locks themselves out.
             */
            static function (DataContainer|null $dc = null): void {
                if (null === $dc || !$dc->id) {
                    return;
                }

                $agent = AgentModel::findByPk($dc->id);

                if (null === $agent) {
                    return;
                }

                $models = System::getContainer()->get(ModelCatalog::class)->optionsWith((string) $agent->model);

                if ([] === $models) {
                    return;
                }

                $field = &$GLOBALS['TL_DCA']['tl_neo_agent']['fields']['model'];
                $field['inputType'] = 'select';
                $field['options'] = $models;
                $field['eval']['chosen'] = true;
                $field['eval']['includeBlankOption'] = true;
            },
            // Reasoning effort and temperature follow what the model can do.
            [ModelCallbacks::class, 'adjust'],
            // The approved transcription models of the route as a list (Requesty: GET /models/transcription).
            [ModelCallbacks::class, 'transcriptionModels'],
            // The button above the list only for users who may open "Access keys and licences".
            static function (): void {
                if (!System::getContainer()->get('security.helper')->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, AccessPage::MODULE)) {
                    unset($GLOBALS['TL_DCA']['tl_neo_agent']['list']['global_operations']['settings']);
                }
            },
            // "Connections" and "Tool log" above the list only for administrators.
            [AgentToolCallbacks::class, 'adminButtons'],
            // Without an AI key the chat is not shown on the website: say so, with a link.
            static function (): void {
                if (!System::getContainer()->get(AiConnection::class)->isConfigured()) {
                    AccessPage::addMissingMessage($GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] ?? '', AccessPage::AI_LEGEND);
                }
            },
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['title'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['title', 'assistant_name'],
            'format' => '%s <span style="color:#b3b3b3;padding-left:6px">[%s]</span>',
            // Marks assistants in "preview only".
            'label_callback' => [PublishCallbacks::class, 'label'],
        ],
        'global_operations' => [
            'settings' => [
                // With the table, so that the table of the current list (e.g. after the breadcrumb) is not carried over.
                'href' => 'do='.AccessPage::MODULE.'&table='.AccessPage::TABLE,
                'icon' => 'settings.svg',
                'class' => 'header_settings',
                'primary' => true,
            ],
            // Connections to MCP servers, installed skills and the log of the tool calls (administrators;
            // "Connections" and "Skills" only with a licence for Plus, see EditionCallbacks).
            'connections' => [
                'href' => 'table=tl_neo_agent_connection',
                'icon' => 'modules.svg',
                'class' => 'header_connections',
                'primary' => true,
            ],
            'skill_list' => [
                'href' => 'table=tl_neo_agent_skill',
                'icon' => 'theme_import.svg',
                'class' => 'header_skills',
                'primary' => true,
            ],
            'tool_log' => [
                'href' => 'table=tl_neo_agent_tool_log',
                'icon' => 'diff.svg',
                'class' => 'header_tool_log',
                'primary' => true,
            ],
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'languages' => [
                'href' => 'table=tl_neo_agent_language',
                'icon' => 'mgroup.svg',
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        '__selector__' => ['grounding_source', 'web_search', 'web_search_mode', 'voice_input', 'form_consent'],
        'default' =>
            '{title_legend},title;'
            // Active, visible to visitors or only in the preview (new assistants: preview), and after which consent.
            .'{publish_legend},published,visibility,visibility_note,consent_category;'
            .'{identity_legend},assistant_name,avatar,widget_side;'
            // Texts per language with the starter questions (child records): shown here, with a button each.
            .'{languages_legend},languages_overview;'
            // Route and key: "Access keys and licences" (Basic bundle); the model is chosen per assistant.
            .'{provider_legend},model,model_info,reasoning_effort,temperature,max_tokens;'
            // Persona picked from lists, no prompt engineering needed.
            .'{persona_legend},tone_address,answer_length,tone_style;'
            .'{grounding_legend},grounding_source;'
            // Safety: no medical advice, no personal data (both on for new assistants).
            .'{safety_legend},no_medical,privacy_mode;'
            // Voice input (edition "pro"); with privacy mode only when voice_in_privacy allows it.
            .'{voice_legend},voice_input,voice_in_privacy;'
            // Web search: approved sources only; the checkbox shows the other fields.
            .'{research_legend},web_search;'
            // Whom does the assistant refer to? Never to third-party vendors.
            .'{referral_legend},referral_company,preferred_solution,preferred_solution_note;'
            .'{escalation_legend},escalation_email,escalation_name,contact_page,form_fields,form_required,phone_country,form_consent,newsletter_module,newsletter_note;'
            .'{skills_legend},skills;'
            .'{mcp_legend},mcp_tools,mcp_note;'
            .'{expert_legend:hide},system_prompt;',
    ],

    'subpalettes' => [
        'grounding_source_text' => 'grounding_text',
        'grounding_source_file' => 'grounding_file',
        'grounding_source_llms_text' => 'grounding_text',
        'web_search' => 'web_search_mode,web_search_sources,web_search_max',
        'web_search_mode_serpapi' => 'web_search_key_info',
        'voice_input' => 'transcription_model',
        'form_consent' => 'privacy_page',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'title' => [
            'inputType' => 'text',
            'exclude' => true,
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'published' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'toggle' => true,
            'eval' => ['isBoolean' => true, 'tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],

        // "preview": only back-end administrators see the chat, in the front-end preview.
        // New assistants start there; the column default keeps existing ones visible.
        'visibility' => [
            'inputType' => 'radio',
            'exclude' => true,
            'options' => ['preview', 'public'],
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'],
            'default' => 'preview',
            'eval' => ['tl_class' => 'clr'],
            'save_callback' => [[PublishCallbacks::class, 'checkVisibility']],
            'sql' => "varchar(16) NOT NULL default 'public'",
        ],
        'visibility_note' => ['input_field_callback' => [PublishCallbacks::class, 'note']],
        // The languages with their starter questions (read only) and the buttons to edit or add one.
        'languages_overview' => ['input_field_callback' => [LanguageCallbacks::class, 'overview']],

        // Forwarding form in the chat: e-mail and message always (required); these on demand.
        'form_fields' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'options' => array_keys(ContactForm::OPTIONAL),
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'],
            'default' => ['name'],
            'eval' => ['multiple' => true, 'tl_class' => 'w50 clr'],
            'sql' => 'blob NULL',
        ],
        'form_required' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'options' => array_keys(ContactForm::OPTIONAL),
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'],
            'eval' => ['multiple' => true, 'tl_class' => 'w50'],
            'sql' => 'blob NULL',
        ],
        // Country code preselected in the phone field ('' = by the language of the page).
        'phone_country' => [
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => [OptionCallbacks::class, 'phoneCountry'],
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'], 'chosen' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        // Required checkbox "I agree to be contacted" (on for new assistants), linking the privacy policy.
        'form_consent' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'default' => '1',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 clr m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'privacy_page' => [
            'inputType' => 'pageTree',
            'exclude' => true,
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        // A newsletter module of the site (Brevo or Contao): its lists or channels and confirmation are used.
        'newsletter_module' => [
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => [FormCallbacks::class, 'newsletterOptions'],
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'], 'chosen' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'newsletter_note' => ['input_field_callback' => [FormCallbacks::class, 'newsletterNote']],

        // --- Identity (the per-language texts live in tl_neo_agent_language) -
        'assistant_name' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],

        // --- Model ---------------------------------------------------------
        // Columns of the former per-assistant connection, kept for existing installations
        // and the one-time takeover (AdoptAiConnectionMigration). Not shown in the form.
        'provider' => [
            'sql' => "varchar(32) NOT NULL default 'requesty_eu'",
        ],
        'base_url' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        // A text field until an AI key is stored; then the onload_callback turns it
        // into a searchable list of the models the route offers.
        'model' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'api_key' => [
            'sql' => "varchar(512) NOT NULL default ''",
        ],
        // What the route says about the model (reasons, uses tools, longest answer).
        'model_info' => [
            'input_field_callback' => [ModelCallbacks::class, 'info'],
        ],
        // Only for models that reason; "low" suits a chat on a website.
        'reasoning_effort' => [
            'inputType' => 'select',
            'exclude' => true,
            'options' => ['off', 'low', 'medium', 'high'],
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default 'low'",
        ],
        'temperature' => [
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => [OptionCallbacks::class, 'temperature'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(4) NOT NULL default '0.4'",
        ],
        'max_tokens' => [
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => [OptionCallbacks::class, 'maxTokens'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(6) NOT NULL default '800'",
        ],

        // --- Web search (approved sources only) ------------------------------
        'web_search' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => ['isBoolean' => true, 'submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'web_search_mode' => [
            'inputType' => 'select',
            'exclude' => true,
            'options' => ['sources', 'serpapi'],
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'],
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => false, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'sources'",
        ],
        'web_search_max' => [
            'inputType' => 'select',
            'exclude' => true,
            'options' => ['1', '2', '3', '4', '5'],
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'],
            'eval' => ['includeBlankOption' => false, 'tl_class' => 'w50'],
            'sql' => "varchar(2) NOT NULL default '3'",
        ],
        // One address per line: a partner or vendor site, or its llms.txt directly.
        // This list is also the hard limit: nothing outside it is fetched.
        'web_search_sources' => [
            'inputType' => 'listWizard',
            'exclude' => true,
            'eval' => ['tl_class' => 'clr', 'allowHtml' => false],
            'sql' => 'blob NULL',
        ],
        // Image instead of the bot icon (upload in the picker), and the side of the screen.
        'avatar' => ['inputType' => 'fileTree', 'exclude' => true, 'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'w50 clr'], 'sql' => 'binary(16) NULL'],
        'widget_side' => ['inputType' => 'select', 'exclude' => true, 'options' => ['right', 'left'], 'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'], 'eval' => ['tl_class' => 'w50'], 'sql' => "varchar(5) NOT NULL default 'right'"],
        // Contact page the assistant may open (edition "basic" on), besides the form in the chat.
        'contact_page' => ['inputType' => 'pageTree', 'exclude' => true, 'foreignKey' => 'tl_page.title', 'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50 clr'], 'sql' => 'int(10) unsigned NOT NULL default 0', 'relation' => ['type' => 'hasOne', 'load' => 'lazy']],
        'no_medical' => ['inputType' => 'checkbox', 'exclude' => true, 'eval' => ['tl_class' => 'w50 m12'], 'sql' => "char(1) NOT NULL default '1'"],
        // submitOnChange: the voice settings depend on it (DependencyCallbacks).
        'privacy_mode' => ['inputType' => 'checkbox', 'exclude' => true, 'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 m12'], 'sql' => "char(1) NOT NULL default '1'"],
        // Skills installed by administrators (edition Plus): nothing ticked by default.
        'skills' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'options_callback' => [SkillCallbacks::class, 'agentOptions'],
            'eval' => ['multiple' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        // Tools of the connections to MCP servers (edition Plus): nothing ticked by default.
        'mcp_tools' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'options_callback' => [AgentToolCallbacks::class, 'options'],
            'eval' => ['multiple' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'mcp_note' => ['input_field_callback' => [AgentToolCallbacks::class, 'note']],
        'voice_input' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        // A list of the route's approved transcription models (onload_callback); a text field without a key.
        'transcription_model' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'placeholder' => 'mistral/voxtral-mini-latest'],
            'sql' => "varchar(128) NOT NULL default 'mistral/voxtral-mini-latest'",
        ],
        // Voice input while privacy mode is on: the recording goes to the transcription
        // service unfiltered, the transcript is filtered like a typed message (ChatController).
        'voice_in_privacy' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        // The SerpAPI key is entered on "Access keys and licences"; this line says whether it is there.
        'web_search_key_info' => [
            'input_field_callback' => static function (): string {
                $stored = System::getContainer()->get(AccessSettings::class)->hasValue('superagent_serpapi_key');
                $lang = $GLOBALS['TL_LANG']['tl_neo_agent'];

                $link = AccessPage::canOpen() ? sprintf(
                    ' <a href="%s">%s</a>',
                    StringUtil::specialchars(AccessPage::url('superagent_search_legend')),
                    StringUtil::specialchars($stored ? ($lang['serpapi_link'] ?? '') : ($lang['serpapi_link_missing'] ?? '')),
                ) : '';

                return sprintf(
                    '<div class="widget clr"><div class="tl_message"><p class="%s">%s%s</p></div></div>',
                    $stored ? 'tl_confirm' : 'tl_error',
                    StringUtil::specialchars($stored ? ($lang['serpapi_stored'] ?? '') : ($lang['serpapi_missing'] ?? '')),
                    $link,
                );
            },
        ],
        // Former per-assistant SerpAPI key, kept for the one-time takeover (AdoptSerpApiKeyMigration).
        'web_search_key' => [
            'sql' => "varchar(512) NOT NULL default ''",
        ],

        // --- Referral: whom does the assistant refer to? ----------------------
        'referral_company' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'preferred_solution' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'preferred_solution_note' => [
            'inputType' => 'textarea',
            'exclude' => true,
            'eval' => ['rows' => 3, 'autogrow' => false, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],

        // --- System prompt --------------------------------------------------
        'tone_address' => [
            'label' => &$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'],
            'inputType' => 'select',
            'options' => array_keys(\Neo\SuperAgentBundle\Chat\PersonaOptions::ADDRESS),
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'],
            'eval' => ['includeBlankOption' => false, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default 'sie'",
        ],
        'answer_length' => [
            'label' => &$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'],
            'inputType' => 'select',
            'options' => array_keys(\Neo\SuperAgentBundle\Chat\PersonaOptions::LENGTH),
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'],
            'eval' => ['includeBlankOption' => false, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default 'short'",
        ],
        'tone_style' => [
            'label' => &$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'],
            'inputType' => 'select',
            'options' => array_keys(\Neo\SuperAgentBundle\Chat\PersonaOptions::STYLE),
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'],
            'eval' => ['includeBlankOption' => false, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'professional'",
        ],
        // autogrow => false is needed for `rows` to take effect at all: otherwise Contao
        // renders rows="1" and lets the autogrow controller compute the height from the
        // content, so an empty field stays one line high (TextArea::generate()).
        'system_prompt' => [
            'inputType' => 'textarea',
            'exclude' => true,
            'eval' => ['rows' => 20, 'autogrow' => false, 'tl_class' => 'clr', 'class' => 'monospace'],
            'sql' => 'text NULL',
        ],

        // --- Grounding ------------------------------------------------------
        'grounding_source' => [
            'inputType' => 'select',
            'exclude' => true,
            'options' => ['none', 'llms', 'llms_text', 'text', 'file'],
            'reference' => &$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'],
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'none'",
        ],
        'grounding_text' => [
            'inputType' => 'textarea',
            'exclude' => true,
            'eval' => ['rows' => 12, 'tl_class' => 'clr', 'class' => 'monospace'],
            'sql' => 'mediumtext NULL',
        ],
        'grounding_file' => [
            'inputType' => 'fileTree',
            'exclude' => true,
            'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'txt,md,markdown,json', 'tl_class' => 'clr'],
            'sql' => 'binary(16) NULL',
        ],

        // --- Escalation / consent ------------------------------------------
        'escalation_email' => [
            'inputType' => 'text',
            'exclude' => true,
            // submitOnChange: the fields of the forwarding form depend on it (DependencyCallbacks).
            'eval' => ['rgxp' => 'email', 'maxlength' => 255, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        // Name on the forward buttons in the chat ("Forward to <name>").
        // Empty = "To the team", so the bundle works for any project.
        'escalation_name' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        // Categories of common consent tools; a stored own category stays selectable. Reloads the form:
        // a category the cookie consent extension never asks for is reported at the field.
        'consent_category' => [
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => [OptionCallbacks::class, 'consent'],
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(32) NOT NULL default 'functional'",
        ],
    ],
];
