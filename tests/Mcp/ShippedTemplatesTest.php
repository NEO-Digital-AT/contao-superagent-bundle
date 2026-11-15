<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Mcp;

use Neo\SuperAgentBundle\Chat\ChatHandler;
use Neo\SuperAgentBundle\Chat\ChatTools;
use Neo\SuperAgentBundle\Mcp\GuestCheck;
use Neo\SuperAgentBundle\Mcp\McpTemplate;
use Neo\SuperAgentBundle\Mcp\McpTool;
use Neo\SuperAgentBundle\Mcp\TemplateCatalog;
use Neo\SuperAgentBundle\Skill\SkillFile;
use Neo\SuperAgentBundle\Skill\SkillPackage;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The templates shipped in mcp-templates/: they load, their skills install, and
 * their rules fit the skills (every tool a skill names can run; every tool that
 * shows or changes a reservation needs the guest check).
 */
#[CoversNothing]
final class ShippedTemplatesTest extends TestCase
{
    /** Tools of the apaleo template that show or change the data of a reservation. */
    private const APALEO_GUEST_DATA = [
        'GetReservation', 'ListReservations', 'GetBooking', 'ListBookings', 'GetReservationOffers', 'GetReservationServiceOffers', 'GetReservationServices',
        'ListFolios', 'GetFolio', 'ListFolioPayments', 'GetFolioPayment', 'ListInvoices', 'GetInvoice', 'GetInvoicePdf', 'ListInvoicePreview', 'ListInvoicePreviewPdf',
        'UpdateReservation', 'UpdateBooking', 'AmendReservation', 'BookService', 'CancelReservation', 'CreateFolioCancellationFee', 'CreatePaymentLink', 'CancelPayment',
        'CreatePaymentRefund', 'UpdateFolio', 'CreateInvoice', 'MoveFolioCharges', 'CreateFolioCharge', 'CancelInvoice', 'CheckOut',
    ];

    public function testEveryShippedTemplateLoadsWithItsSkills(): void
    {
        $templates = (new TemplateCatalog(sys_get_temp_dir().'/neo-no-project', new NullLogger()))->all();

        $this->assertArrayHasKey('apaleo', $templates);

        foreach ($templates as $template) {
            $this->assertNotSame([], $template->skillFiles, $template->id);
            $names = array_map(static fn (string $path): string => SkillFile::parse((string) file_get_contents($path))->name, $template->skillFiles);
            $shipped = array_keys(SkillPackage::load(ChatHandler::skillDir(), ChatTools::NAMES)->modules());

            foreach ($template->skillFiles as $path) {
                $file = SkillFile::parse((string) file_get_contents($path));
                $others = array_values(array_diff($names, [$file->name]));
                $check = $file->check(['core', ...$shipped, ...$others], [...$shipped, ...$others], []);

                $this->assertSame([], $check['errors'], $path);
                $this->assertSame(basename(\dirname($path)), $file->name, 'the folder is the name of the skill');
            }
        }
    }

    public function testEveryToolTheApaleoSkillsNameCanRunAndGuestDataNeedsTheGuestCheck(): void
    {
        $template = (new TemplateCatalog(sys_get_temp_dir().'/neo-no-project', new NullLogger()))->get('apaleo');
        $this->assertInstanceOf(McpTemplate::class, $template);
        $check = new GuestCheck('secret', new ArrayAdapter(), new RequestStack());

        foreach ($template->skillFiles as $path) {
            foreach (SkillFile::parse((string) file_get_contents($path))->tools as $name) {
                if (GuestCheck::TOOL === $name) {
                    continue;
                }

                $tool = new McpTool($name, '', '', ['type' => 'object']);
                $this->assertFalse($template->isLocked($name), $name.' in '.basename(\dirname($path)).' is locked');
                $this->assertTrue(McpTool::READ === $template->kind($tool) || $template->isUnlocked($name), $name.' changes something and is not unlocked');
            }
        }

        foreach (self::APALEO_GUEST_DATA as $name) {
            $this->assertTrue($check->isProtected($template, $name), $name.' shows or changes a reservation without the guest check');
        }

        foreach (['ListOffers', 'ListServiceOffers', 'GetProperty', 'ListUnitGroups', 'ListRatePlans', 'CreateBooking', 'ListCancellationPolicies'] as $public) {
            $this->assertFalse($check->isProtected($template, $public), $public.' is for every visitor');
        }

        foreach (['ListPersons', 'GetPerson', 'ListProfileReservations', 'ExecuteDataQuery', 'DeleteBooking', 'RunNightAudit', 'ListReservationLogs', 'ListUsers', 'CreateAccountPayment', 'ListPaymentAccounts'] as $closed) {
            $this->assertTrue($template->isLocked($closed) || (McpTool::READ !== $template->kind(new McpTool($closed, '', '', [])) && !$template->isUnlocked($closed)), $closed.' must be locked');
        }

        // apaleo's MCP server has its own sign-in (mcp.apaleo.com, automatic client
        // registration); a token of the apaleo account login is refused with 401.
        $this->assertSame('', $template->oauth['authorization_endpoint'], 'endpoints are found from the MCP server');
        $this->assertTrue($template->oauth['registration'], 'no app of your own');
        $this->assertTrue($template->oauth['resource']);
        // mcp:tools alone lists the tools, but every call then fails: each tool needs the
        // scope of its apaleo API (offers.read for ListOffers, …), granted where the role allows.
        foreach (['mcp:tools', 'offline_access', 'offers.read', 'availability.read', 'properties.read', 'setup.read', 'reservations.read', 'reservations.create', 'reservations.manage', 'folios.read', 'folios.manage', 'invoices.read', 'invoices.create'] as $scope) {
            $this->assertContains($scope, explode(' ', $template->oauth['scope']), $scope);
        }

        foreach ($template->setup as $language => $steps) {
            $this->assertStringNotContainsString('store app', implode(' ', $steps), $language.': no store app is needed');
        }

        $this->assertTrue($check->isCreating($template, 'CreateBooking'));
        $this->assertCount(11, $template->setup, 'setup steps in the eleven languages of the back end');
        $this->assertCount(1, array_unique(array_map('count', $template->setup)), 'the same number of steps in every language');
    }
}
