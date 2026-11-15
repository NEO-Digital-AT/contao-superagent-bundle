<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Backend;

use Contao\DataContainer;
use Contao\Model\Registry;
use Neo\BasicBundle\Access\AccessSettings;
use Neo\BasicBundle\Security\SecretCipher;
use Neo\SuperAgentBundle\Backend\CookieConsentCheck;
use Neo\SuperAgentBundle\Backend\DependencyCallbacks;
use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Tests\Support\Licences;
use Neo\SuperAgentBundle\Tests\Support\Models;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** Which settings of an assistant have no effect, and why. */
#[CoversClass(DependencyCallbacks::class)]
final class DependencyCallbacksTest extends TestCase
{
    private const FIELDS = ['voice_input', 'transcription_model', 'voice_in_privacy', 'web_search_mode', 'escalation_name', 'form_fields', 'form_required', 'phone_country', 'form_consent', 'privacy_page', 'newsletter_module', 'consent_category', 'visibility'];

    protected function setUp(): void
    {
        $GLOBALS['TL_DCA']['tl_neo_agent'] = [
            'palettes' => ['__selector__' => ['privacy_mode'], 'default' => '{privacy_legend},privacy_mode,voice_in_privacy;{voice_legend},voice_input,transcription_model'],
            'fields' => array_fill_keys(self::FIELDS, ['inputType' => 'text', 'eval' => []]),
        ];
        $GLOBALS['TL_LANG']['tl_neo_agent'] = ['voice_privacy_off' => 'Off in privacy mode', 'serpapi_missing' => 'No SerpAPI key', 'forwarding_locked' => 'No escalation address', 'visibility_locked' => 'Confirm the terms first'];
        Registry::getInstance()->reset();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA']['tl_neo_agent'], $GLOBALS['TL_LANG']['tl_neo_agent'], $GLOBALS['TL_CSS']);
        Registry::getInstance()->reset();
    }

    public function testAFullyConfiguredAssistantHasNothingLocked(): void
    {
        $this->lock(['privacy_mode' => '1', 'voice_in_privacy' => '1', 'escalation_email' => 'team@example.com', 'web_search' => '1', 'web_search_mode' => 'serpapi'], true);

        $this->assertSame([], $this->marked());
    }

    public function testVoiceInputIsOffInPrivacyModeUnlessAllowed(): void
    {
        $this->lock(['privacy_mode' => '1', 'escalation_email' => 'team@example.com']);

        $this->assertSame(['voice_input' => 'Off in privacy mode', 'transcription_model' => 'Off in privacy mode'], $this->marked());
        $this->assertTrue($GLOBALS['TL_DCA']['tl_neo_agent']['fields']['voice_input']['eval']['disabled']);
    }

    public function testWithoutPrivacyModeItsVoiceSwitchIsHidden(): void
    {
        $this->lock(['privacy_mode' => '', 'escalation_email' => 'team@example.com']);

        $this->assertSame('{privacy_legend},privacy_mode;{voice_legend},voice_input,transcription_model', $GLOBALS['TL_DCA']['tl_neo_agent']['palettes']['default']);
    }

    public function testTheSearchModeWarnsWithoutKeyAndTheFormNeedsAnAddress(): void
    {
        $this->lock(['web_search' => '1', 'web_search_mode' => 'serpapi', 'escalation_email' => ' ']);

        $marked = $this->marked();
        $this->assertSame('No SerpAPI key', $marked['web_search_mode']);
        $this->assertArrayNotHasKey('disabled', $GLOBALS['TL_DCA']['tl_neo_agent']['fields']['web_search_mode']['eval'], 'another mode works without the key');
        $this->assertSame(['escalation_name', 'form_fields', 'form_required', 'phone_country', 'form_consent', 'privacy_page', 'newsletter_module'], array_keys(array_filter($marked, static fn (string $r): bool => 'No escalation address' === $r)));
    }

    public function testVisibleForAllOnlyAfterTheTerms(): void
    {
        $this->lock(['visibility' => 'preview', 'escalation_email' => 'team@example.com'], false, '');
        $this->assertSame(['visibility' => 'Confirm the terms first'], $this->marked());

        $this->setUp();
        $this->lock(['visibility' => 'public', 'escalation_email' => 'team@example.com'], false, '');
        $this->assertSame([], $this->marked(), 'an assistant that is visible already stays so');
    }

    public function testNothingWithoutARecord(): void
    {
        $callbacks = new DependencyCallbacks(new FeatureResolver(Licences::service(), Licences::terms()), new AccessSettings(TestDatabase::create(), new SecretCipher('test-secret')), new CookieConsentCheck(TestDatabase::create(), $this->createMock(Security::class), $this->createMock(UrlGeneratorInterface::class)));
        $callbacks->lock(null);
        $callbacks->lock($this->dc(0));

        $this->assertSame([], $this->marked());
    }

    /** @param array<string, mixed> $row */
    private function lock(array $row, bool $serpKey = false, string $terms = 'pro'): void
    {
        Models::register(AgentModel::class, $row + ['id' => 5]);
        $db = TestDatabase::create('tl_neo_access');
        $access = new AccessSettings($db, new SecretCipher('test-secret'));

        if ($serpKey) {
            $access->store(['superagent_serpapi_key' => 'serp'], ['superagent_serpapi_key']);
        }

        $callbacks = new DependencyCallbacks(new FeatureResolver(Licences::service(), Licences::terms($terms)), $access, new CookieConsentCheck(TestDatabase::create(), $this->createMock(Security::class), $this->createMock(UrlGeneratorInterface::class)));
        $callbacks->lock($this->dc(5));
    }

    private function dc(int $id): DataContainer
    {
        $dc = $this->createMock(DataContainer::class);
        $dc->method('__get')->willReturnCallback(static fn (string $key): mixed => 'id' === $key ? $id : null);

        return $dc;
    }

    /** @return array<string, string> field => reason */
    private function marked(): array
    {
        $marked = [];

        foreach ($GLOBALS['TL_DCA']['tl_neo_agent']['fields'] as $name => $field) {
            if (str_contains($field['eval']['tl_class'] ?? '', 'nsa-locked')) {
                $marked[$name] = $field['label'][1];
            }
        }

        return $marked;
    }
}
