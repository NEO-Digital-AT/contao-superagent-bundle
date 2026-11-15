<?php

declare(strict_types=1);

use Neo\SuperAgentBundle\Model\AgentModel;

/*
 * Back-end palette for the "neo_agent" FRONTEND MODULE
 * (Neo\SuperAgentBundle\Controller\FrontendModule\AgentModuleController).
 *
 * Pick which assistant the widget shows. Leave empty to use the first published
 * agent. The visitor-facing language (greeting, canned Q&A, …) is resolved from
 * the agent's per-language records by the current page language.
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['neo_agent'] =
    '{title_legend},name,headline,type;'
    .'{config_legend},agent,nsa_ga_event;'
    .'{template_legend:hide},customTpl;'
    .'{protected_legend:hide},protected;'
    .'{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['agent'] = [
    'inputType' => 'select',
    'exclude' => true,
    'options_callback' => static function (): array {
        $agents = AgentModel::findAll(['order' => 'tl_neo_agent.title ASC']);
        $options = [];

        if (null !== $agents) {
            foreach ($agents as $agent) {
                $options[(int) $agent->id] = $agent->title.' ['.$agent->assistant_name.']';
            }
        }

        return $options;
    },
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

// GA4 key event fired when the visitor sends the first chat message (e.g. "chat_ai").
// Empty = no event. Fires only when gtag exists, i.e. after statistics consent.
$GLOBALS['TL_DCA']['tl_module']['fields']['nsa_ga_event'] = [
    'inputType' => 'text',
    'exclude' => true,
    'eval' => ['maxlength' => 40, 'rgxp' => 'fieldname', 'tl_class' => 'w50'],
    'sql' => "varchar(40) NOT NULL default ''",
];
