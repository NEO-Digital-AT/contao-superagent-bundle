<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Licence;

/**
 * The SuperAgent's terms on "Access keys and licences" (Basic bundle, TermsService):
 * one passage for all editions and one per edition, in order. The texts are in
 * translations/neo_superagent_terms.<lang>.yaml; the German text is binding.
 * Raise the version of a passage when its text changes in substance, so that it
 * is confirmed again.
 */
final class Terms
{
    public const DOMAIN = 'neo_superagent_terms';

    /** passage => version; "all" applies without a licence too. */
    public const PASSAGES = ['all' => 1, 'basic' => 1, 'plus' => 1, 'pro' => 1];
}
