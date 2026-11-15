<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Neo\BasicBundle\Ai\ModelCatalog;
use Neo\SuperAgentBundle\Chat\TestedModels;
use Neo\SuperAgentBundle\Model\AgentModel;

/**
 * The model section of an assistant follows what the route says about the chosen
 * model: "Reasoning effort" only for models that reason, "Temperature" only for
 * models that do not (reasoning models reject other temperatures), and a line
 * with what the model can do.
 */
final class ModelCallbacks
{
    public static function adjust(DataContainer|null $dc = null): void
    {
        if (null === $dc || !$dc->id || null === ($agent = AgentModel::findByPk($dc->id))) {
            return;
        }

        $caps = System::getContainer()->get(ModelCatalog::class)->capabilities((string) $agent->model);
        $palette = &$GLOBALS['TL_DCA']['tl_neo_agent']['palettes']['default'];

        if (false === $caps['reasoning']) {
            $palette = str_replace(',reasoning_effort', '', $palette);
        }

        // A model that reasons never gets a temperature (it rejects other values than its default).
        if (true === $caps['reasoning']) {
            $palette = str_replace(',temperature', '', $palette);
        }
    }

    /** The transcription model as a list of the route's transcription models, when it lists them. */
    public static function transcriptionModels(DataContainer|null $dc = null): void
    {
        if (null === $dc || !$dc->id || null === ($agent = AgentModel::findByPk($dc->id))) {
            return;
        }

        $models = System::getContainer()->get(ModelCatalog::class)->transcriptionModels();

        if ([] === $models) {
            return;
        }

        $current = (string) $agent->transcription_model;

        if ('' !== $current && !isset($models[$current])) {
            $models = [$current => $current] + $models;
        }

        $field = &$GLOBALS['TL_DCA']['tl_neo_agent']['fields']['transcription_model'];
        $field['inputType'] = 'select';
        $field['options'] = $models;
        $field['eval']['chosen'] = true;
    }

    public static function info(DataContainer $dc): string
    {
        $agent = AgentModel::findByPk($dc->id);

        if (null === $agent || '' === (string) $agent->model) {
            return '';
        }

        $caps = System::getContainer()->get(ModelCatalog::class)->capabilities((string) $agent->model);
        $tl = &$GLOBALS['TL_LANG']['tl_neo_agent'];
        $bits = [];

        if (true === $caps['reasoning']) {
            $bits[] = $tl['caps_reasoning'];
        }

        if (true === $caps['tools']) {
            $bits[] = $tl['caps_tools'];
        }

        if (null !== $caps['maxOutput']) {
            $bits[] = \sprintf($tl['caps_max'], number_format($caps['maxOutput'], 0, ',', '.'));
        }

        $html = [] !== $bits ? '<p class="tl_help tl_tip">'.htmlspecialchars(implode(' · ', $bits)).'</p>' : '';

        if (false === $caps['tools']) {
            $html .= '<p class="tl_info">'.htmlspecialchars($tl['caps_no_tools']).'</p>';
        } elseif ([] !== StringUtil::deserialize((string) $agent->mcp_tools, true) && !TestedModels::covers((string) $agent->model)) {
            // Tools of connections are ticked: a model outside the tested ones may not manage them (AgentToolCallbacks::note).
            $html .= '<p class="tl_info">'.htmlspecialchars(sprintf($tl['caps_untested'], TestedModels::names())).'</p>';
        }

        return '' !== $html ? '<div class="widget clr">'.$html.'</div>' : '';
    }
}
