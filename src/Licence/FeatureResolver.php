<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Licence;

use Neo\BasicBundle\License\LicenseService;
use Neo\BasicBundle\Terms\TermsService;
use Neo\SuperAgentBundle\SuperAgent;

/**
 * Features that apply on a host (the website the visitor is on): those of the
 * licence, as far as the terms of its editions are confirmed on "Access keys
 * and licences". A licence for "plus" with only the terms up to "basic"
 * confirmed gives the features of "basic". An empty host skips the domain
 * check (back end).
 */
final class FeatureResolver
{
    public function __construct(
        private readonly LicenseService $licenses,
        private readonly TermsService $terms,
    ) {
    }

    public function for(string $host): Features
    {
        $edition = $this->licensedEdition($host);
        $confirmed = $this->terms->acceptedUpTo(SuperAgent::LICENSE_MODULE, Terms::PASSAGES, $edition);

        // The first passage applies to all editions; it alone unlocks no paid edition.
        return Features::forEdition(null === $confirmed || array_key_first(Terms::PASSAGES) === $confirmed ? '' : $confirmed);
    }

    /** Features of the licence alone, before the terms (back end: "confirm the terms" instead of "needs a licence"). */
    public function licensed(string $host): Features
    {
        return Features::forEdition($this->licensedEdition($host));
    }

    /** Whether the terms for all editions are confirmed; until then no assistant becomes visible to visitors. */
    public function canPublish(): bool
    {
        return null !== $this->terms->acceptedUpTo(SuperAgent::LICENSE_MODULE, Terms::PASSAGES, '');
    }

    /**
     * The first passage of the licensed edition that is not confirmed yet
     * ("all", "basic" …); null when everything is confirmed.
     */
    public function pendingPassage(string $host = ''): string|null
    {
        $edition = $this->licensedEdition($host);
        $required = array_keys(TermsService::required(Terms::PASSAGES, $edition));
        $confirmed = $this->terms->acceptedUpTo(SuperAgent::LICENSE_MODULE, Terms::PASSAGES, $edition);

        if (end($required) === $confirmed) {
            return null;
        }

        return null === $confirmed ? $required[0] : $required[array_search($confirmed, $required, true) + 1];
    }

    private function licensedEdition(string $host): string
    {
        $state = $this->licenses->state(SuperAgent::LICENSE_MODULE, $host);

        return $state->isLicensed() ? $state->edition() : '';
    }
}
