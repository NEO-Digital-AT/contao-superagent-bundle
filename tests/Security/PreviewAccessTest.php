<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Security;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Security\PreviewAccess;
use Neo\SuperAgentBundle\Tests\Support\Models;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/** "Preview only": never for visitors. */
#[CoversClass(PreviewAccess::class)]
final class PreviewAccessTest extends TestCase
{
    public function testAPublicAssistantIsSeenByEverybody(): void
    {
        $access = $this->access(null, false);

        $this->assertTrue($access->canSee(Models::make(AgentModel::class, ['visibility' => 'public']), true));
        $this->assertTrue($access->canSee(Models::make(AgentModel::class, ['visibility' => '']), false));
    }

    public function testAVisitorNeverSeesAPreviewAssistant(): void
    {
        $access = $this->access(null, true);
        $agent = Models::make(AgentModel::class, ['visibility' => PreviewAccess::PREVIEW]);

        $this->assertFalse($access->canSee($agent, true));
        $this->assertFalse($access->canSee($agent, false));
        $this->assertFalse($access->adminViewing(false));
    }

    public function testThePageShowsItOnlyInTheFrontEndPreview(): void
    {
        $tokens = $this->createMock(TokenChecker::class);
        $tokens->expects($this->never())->method('getBackendUsername');

        $stack = new RequestStack();
        $stack->push(new Request());

        $this->assertFalse((new PreviewAccess($tokens, $stack))->canSee(Models::make(AgentModel::class, ['visibility' => PreviewAccess::PREVIEW]), true), 'outside the preview the user is not even looked up');
    }

    private function access(string|null $username, bool $preview): PreviewAccess
    {
        $tokens = $this->createMock(TokenChecker::class);
        $tokens->method('getBackendUsername')->willReturn($username);

        $request = new Request();
        $request->attributes->set('_preview', $preview);
        $stack = new RequestStack();
        $stack->push($request);

        return new PreviewAccess($tokens, $stack);
    }
}
