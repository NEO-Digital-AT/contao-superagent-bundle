<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Support;

use Neo\BasicBundle\License\Certificate;
use Neo\BasicBundle\License\LicenseService;
use Neo\BasicBundle\License\LicenseState;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\BasicBundle\Terms\TermsService;
use Neo\SuperAgentBundle\Licence\Terms;
use Neo\SuperAgentBundle\SuperAgent;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The licence of this extension as the Basic bundle reports it. Certificates are
 * signed by the licence service with a key that is not available here, so the
 * state of a valid licence is handed to LicenseService as if it had verified it
 * (its cache per host); without one, LicenseService evaluates the empty table.
 */
final class Licences
{
    /** @param list<string> $hosts hosts the state is given for ('' = back end) */
    public static function service(string $edition = '', array $hosts = ['', 'www.example.com']): LicenseService
    {
        $service = new LicenseService(TestDatabase::create('tl_neo_license'), new SecretCipher('test-secret'), new MockHttpClient(), new RequestStack(), new NullLogger(), 'https://licence.example.com');

        if ('' === $edition) {
            return $service;
        }

        $certificate = (new \ReflectionClass(Certificate::class))->newInstanceWithoutConstructor();
        (new \ReflectionMethod(Certificate::class, '__construct'))->invoke($certificate, 'L-TEST', SuperAgent::LICENSE_MODULE, $edition, ['example.com'], time() + 86400, time() + 86400 * 30, 'active', time());

        $states = [];

        foreach ($hosts as $host) {
            $states[SuperAgent::LICENSE_MODULE.'|'.$host] = new LicenseState(SuperAgent::LICENSE_MODULE, LicenseState::OK, $certificate, null, '', time(), '');
        }

        (new \ReflectionProperty(LicenseService::class, 'states'))->setValue($service, $states);

        return $service;
    }

    /** Terms confirmed up to a passage ("all", "basic" …); '' = none. */
    public static function terms(string $upTo = 'pro'): TermsService
    {
        $terms = new TermsService(TestDatabase::create(TermsService::TABLE));
        $passages = [];

        foreach (Terms::PASSAGES as $passage => $version) {
            if ('' === $upTo) {
                break;
            }

            $passages[$passage] = $version;

            if ($passage === $upTo) {
                break;
            }
        }

        $terms->accept(SuperAgent::LICENSE_MODULE, $passages, 1, 'editor', 'en');

        return $terms;
    }
}
