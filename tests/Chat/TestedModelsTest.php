<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\TestedModels;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestedModels::class)]
final class TestedModelsTest extends TestCase
{
    public function testAModelOfARouteIsFoundWithoutProviderAndRegion(): void
    {
        $this->assertTrue(TestedModels::covers('azure/gpt-6-luna@swedencentral'));
        $this->assertTrue(TestedModels::covers('GPT-6-LUNA'));
        $this->assertTrue(TestedModels::covers('bedrock/claude-sonnet-4-5@eu-central-1'));
        $this->assertTrue(TestedModels::covers('mistral/mistral-large-4'));
        $this->assertFalse(TestedModels::covers('azure/gpt-4o-mini@swedencentral'), 'too weak for several steps with tools');
        $this->assertFalse(TestedModels::covers(''));
    }

    public function testTheNamesShownFollowTheList(): void
    {
        $this->assertSame(implode(', ', TestedModels::MODELS), TestedModels::names());
        $this->assertStringContainsString('GPT-6 Luna', TestedModels::names());
    }
}
