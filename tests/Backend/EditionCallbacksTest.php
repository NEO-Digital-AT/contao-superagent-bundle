<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Backend;

use Neo\SuperAgentBundle\Backend\CookieConsentCheck;
use Neo\SuperAgentBundle\Backend\EditionCallbacks;
use Neo\SuperAgentBundle\Licence\FeatureResolver;
use Neo\SuperAgentBundle\Tests\Support\Licences;
use Neo\SuperAgentBundle\Tests\Support\TestDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** Settings of an edition the licence does not cover are not offered in the back end. */
#[CoversClass(EditionCallbacks::class)]
#[CoversClass(CookieConsentCheck::class)]
final class EditionCallbacksTest extends TestCase
{
    private const FIELDS = ['web_search', 'escalation_email', 'contact_page', 'voice_input', 'transcription_model', 'skills', 'mcp_tools'];

    protected function setUp(): void
    {
        $GLOBALS['TL_DCA']['tl_neo_agent'] = [
            'list' => ['global_operations' => ['connections' => [], 'skill_list' => [], 'languages' => []]],
            'palettes' => ['__selector__' => ['web_search', 'voice_input'], 'default' => '{title_legend},name,assistant_name;{contact_legend},escalation_email,contact_page;{search_legend},web_search;{voice_legend},voice_input;{plus_legend},skills,mcp_tools'],
            'subpalettes' => ['voice_input' => 'transcription_model'],
            'fields' => array_fill_keys([...self::FIELDS, 'name', 'assistant_name'], ['eval' => []]),
        ];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA']['tl_neo_agent'], $GLOBALS['BE_MOD']['neo'], $GLOBALS['TL_LANG']['tl_neo_agent']);
    }

    public function testWithoutLicenceOnlyTheChatSettingsRemain(): void
    {
        $this->hide('');

        $this->assertSame('{title_legend},name,assistant_name', $GLOBALS['TL_DCA']['tl_neo_agent']['palettes']['default']);
        $this->assertSame('', $GLOBALS['TL_DCA']['tl_neo_agent']['subpalettes']['voice_input']);
        $this->assertSame(['languages'], array_keys($GLOBALS['TL_DCA']['tl_neo_agent']['list']['global_operations']));
        $this->assertSame(self::FIELDS, $this->hidden());
    }

    public function testBasicOffersContactAndSearch(): void
    {
        $this->hide('basic');

        $this->assertSame('{title_legend},name,assistant_name;{contact_legend},escalation_email,contact_page;{search_legend},web_search', $GLOBALS['TL_DCA']['tl_neo_agent']['palettes']['default']);
        $this->assertSame(['voice_input', 'transcription_model', 'skills', 'mcp_tools'], $this->hidden());
    }

    public function testProOffersEverything(): void
    {
        $before = $GLOBALS['TL_DCA']['tl_neo_agent'];

        $this->hide('pro');

        $this->assertSame($before, $GLOBALS['TL_DCA']['tl_neo_agent']);
    }

    public function testTheTermsDoNotHideWhatTheLicenceCovers(): void
    {
        (new EditionCallbacks(new FeatureResolver(Licences::service('plus'), Licences::terms(''))))->hideUnlicensed();

        $this->assertSame(['voice_input', 'transcription_model'], $this->hidden());
    }

    public function testTheCookieConsentCategoryOfTheChat(): void
    {
        $db = TestDatabase::create();
        $check = new CookieConsentCheck($db, $this->createMock(Security::class), $this->createMock(UrlGeneratorInterface::class));
        $this->assertNull($check->offers('functional'), 'extension not installed');

        $GLOBALS['BE_MOD']['neo'][CookieConsentCheck::MODULE] = ['tables' => []];
        $this->assertNull($check->offers('functional'), 'tables not created yet');

        $db->executeStatement("CREATE TABLE tl_neo_cookieconsent_settings (id INTEGER PRIMARY KEY, active char(1) NOT NULL DEFAULT '1')");
        $db->executeStatement("CREATE TABLE tl_neo_cookieconsent_service (id INTEGER PRIMARY KEY, tstamp int NOT NULL DEFAULT 0, published char(1) NOT NULL DEFAULT '1', category varchar(16) NOT NULL DEFAULT '')");
        $this->assertFalse($check->offers('functional'), 'no settings yet: the banner is on by default, and no service asks for the category');

        $db->insert('tl_neo_cookieconsent_service', ['tstamp' => 0, 'category' => 'functional']);
        $db->insert('tl_neo_cookieconsent_service', ['tstamp' => 1, 'category' => 'functional', 'published' => '']);
        $db->insert('tl_neo_cookieconsent_service', ['tstamp' => 1, 'category' => 'marketing']);
        $this->assertFalse($check->offers('functional'), 'a draft and an unpublished service do not count');
        $this->assertTrue($check->offers('marketing'));

        $db->insert('tl_neo_cookieconsent_settings', ['active' => '']);
        $this->assertNull($check->offers('marketing'), 'banner switched off');
        $this->assertNull($check->offers(''));
    }

    public function testWhoCannotOpenTheCookieConsentIsToldToAskAnAdministrator(): void
    {
        $GLOBALS['BE_MOD']['neo'][CookieConsentCheck::MODULE] = ['tables' => [], 'add_service' => ['x', 'y']];
        $GLOBALS['TL_LANG']['tl_neo_agent'] = ['consent_unoffered' => 'No service in %s <asks>.', 'consent_ask_admin' => 'Ask who manages %s.'];
        $GLOBALS['TL_LANG']['MOD'][CookieConsentCheck::MODULE] = ['Cookie consent'];

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturn(false);
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects($this->never())->method('generate');

        $this->assertSame('No service in Cookie consent &lt;asks&gt;. Ask who manages Cookie consent.', (new CookieConsentCheck(TestDatabase::create(), $security, $router))->reason('functional'));
    }

    private function hide(string $edition): void
    {
        (new EditionCallbacks(new FeatureResolver(Licences::service($edition), Licences::terms())))->hideUnlicensed();
    }

    /** @return list<string> fields of the form above that are hidden */
    private function hidden(): array
    {
        $fields = array_intersect_key($GLOBALS['TL_DCA']['tl_neo_agent']['fields'], array_flip([...self::FIELDS, 'name', 'assistant_name']));

        return array_keys(array_filter($fields, static fn (array $f): bool => true === ($f['eval']['doNotShow'] ?? false)));
    }
}
