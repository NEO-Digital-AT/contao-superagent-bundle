<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * SuperAgent — a Contao bundle that adds a configurable, consent-aware AI chat
 * assistant to the front end. Everything is editable per installation in the
 * Contao back end (assistant name, model, temperature, system prompt, grounding
 * source and escalation address); route and key of the AI connection come from
 * the Basic bundle.
 *
 * The bundle root holds the Contao resources (contao/, config/, public/),
 * which is why getPath() points one level above this src/ directory.
 */
final class NeoSuperAgentBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
