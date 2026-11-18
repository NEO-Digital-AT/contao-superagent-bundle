<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\ChatHandler;
use Neo\SuperAgentBundle\Chat\ChatTools;
use Neo\SuperAgentBundle\Chat\PersonaOptions;
use Neo\SuperAgentBundle\Chat\SystemPromptBuilder;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Skill\SkillPackage;
use Neo\SuperAgentBundle\Tests\Support\Models;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SystemPromptBuilder::class)]
final class SystemPromptBuilderTest extends TestCase
{
    public function testTheSectionsInTheirOrder(): void
    {
        $prompt = $this->build([], [
            'loaded' => ['navigation'],
            'pages' => [12 => ['title' => 'Contact', 'path' => '/contact', 'description' => 'How to reach us'], 14 => ['title' => 'Imprint', 'path' => '/imprint', 'description' => '']],
            'qna' => ['When are you open?' => 'Monday to Friday.'],
            'knowledge' => "  We sell bicycles.\n",
        ]);

        $sections = [];
        preg_match_all('/^=== ([A-Z ]+?)(?: \(.*\))? ===$/m', $prompt, $sections);
        $this->assertSame(['SETTINGS', 'STATE', 'LOADED SKILLS', 'PAGES', 'QUESTIONS AND ANSWERS', 'KNOWLEDGE'], $sections[1], 'nothing left to load: no catalogue');
        $this->assertStringStartsWith($this->skills()->core(), $prompt);

        $this->assertStringContainsString("12 | Contact | /contact | How to reach us\n14 | Imprint | /imprint", $prompt);
        $this->assertStringContainsString("Q: When are you open?\nA: Monday to Friday.", $prompt);
        $this->assertStringEndsWith("=== KNOWLEDGE ===\nWe sell bicycles.", $prompt);
        $this->assertStringContainsString("--- SKILL navigation ---\n".$this->skills()->modules()['navigation']['body'], $prompt);
    }

    public function testTheCatalogListsWhatIsNotLoadedAndTheTools(): void
    {
        $prompt = $this->build([], ['available' => ['navigation', 'contact'], 'loaded' => ['navigation'], 'tools' => ['open_page', 'shop_orders'], 'toolSummaries' => ['shop_orders' => 'Lists orders.']]);

        $this->assertStringContainsString("Skills not loaded (load with load_module):\n- contact: ".$this->skills()->modules()['contact']['description'], $prompt);
        $this->assertStringNotContainsString('- navigation: ', $prompt);
        $this->assertStringContainsString('- open_page: '.ChatTools::summary('open_page').' (skill navigation)', $prompt);
        $this->assertStringContainsString('- shop_orders: Lists orders.', $prompt);

        $this->assertStringNotContainsString('=== CATALOG ===', $this->build([], ['available' => ['contact'], 'tools' => ['open_page'], 'toolsMode' => false]), 'without tools no catalogue');
    }

    public function testTheSettingsOfTheAssistant(): void
    {
        $prompt = $this->build([
            'assistant_name' => 'Bot', 'tone_address' => 'du', 'answer_length' => 'long', 'tone_style' => 'unknown',
            'referral_company' => 'Example Ltd.', 'preferred_solution' => 'Our app', 'preferred_solution_note' => 'free trial',
            'no_medical' => '1', 'privacy_mode' => '1', 'system_prompt' => ' {name} answers in short sentences. ',
        ], ['canForward' => true, 'forwardTo' => 'the team', 'submitLabel' => 'Send', 'contactPage' => ['id' => 7, 'title' => 'Contact'], 'navigation' => true, 'webSearch' => false, 'medicalGuidance' => 'No diagnoses.', 'privacyGuidance' => 'No personal data.']);

        $this->assertStringContainsString(implode("\n", [
            'Assistant name: Bot',
            'Form of address: '.PersonaOptions::ADDRESS['du'],
            'Answer length: '.PersonaOptions::LENGTH['long'],
            'Tone: '.PersonaOptions::STYLE['professional'],
            'Forwarding: available, to "the team"; send button of the form: "Send"',
            'Navigation: available',
            'Contact page: id 7 "Contact"',
            'Web search: not available',
            'Referral company: Example Ltd.',
            'Preferred solution: Our app — free trial',
            'Medical rule: on',
            'MEDICAL GUIDANCE: No diagnoses.',
            'Privacy mode: on',
            'PRIVACY GUIDANCE: No personal data.',
            "OPERATOR INSTRUCTIONS:\nBot answers in short sentences.",
        ]), $prompt);
    }

    public function testDefaultsWithoutSettings(): void
    {
        $prompt = $this->build();

        $this->assertStringContainsString("Assistant name: Assistant\nForm of address: ".PersonaOptions::ADDRESS['sie']."\nAnswer length: ".PersonaOptions::LENGTH['short'], $prompt);
        $this->assertStringContainsString("Forwarding: not available\nNavigation: not available; link a page of this website only with an address from the knowledge\nContact page: none", $prompt);
        $this->assertStringNotContainsString('Medical rule: on', $prompt);
        $this->assertStringNotContainsString("OPERATOR INSTRUCTIONS:\n", $prompt);
        $this->assertStringNotContainsString('=== PAGES (', $prompt);
    }

    public function testTheStateOfTheRequest(): void
    {
        $prompt = $this->build([], ['locale' => 'de-AT', 'page' => ['url' => '/shop', 'title' => 'Shop']]);

        $this->assertMatchesRegularExpression('/^Today: [A-Z][a-z]+day, \d{4}-\d{2}-\d{2} \(.+\)$/m', $prompt);
        $this->assertStringContainsString('Next days: '.date('D Y-m-d', strtotime('+1 day')).', ', $prompt, 'weekdays to look up');
        $this->assertStringContainsString(date('D Y-m-d', strtotime('+14 days')), $prompt);
        $this->assertStringContainsString("Page language: German (Austria)\nCurrent page: \"Shop\" /shop", $prompt);
        $this->assertStringContainsString('Page language: the language of the website', $this->build([], ['locale' => '']));
    }

    public function testForwardingNeedsAnAddress(): void
    {
        $this->assertTrue(SystemPromptBuilder::canForward(Models::make(AgentModel::class, ['escalation_email' => 'team@example.com'])));
        $this->assertFalse(SystemPromptBuilder::canForward(Models::make(AgentModel::class, ['escalation_email' => '  '])));
    }

    /**
     * @param array<string, mixed> $agent
     * @param array<string, mixed> $run
     */
    private function build(array $agent = [], array $run = []): string
    {
        return (new SystemPromptBuilder())->build($this->skills(), Models::make(AgentModel::class, $agent), $run + [
            'locale' => 'en',
            'page' => null,
            'canForward' => false,
            'forwardTo' => '',
            'submitLabel' => '',
            'contactPage' => null,
            'navigation' => false,
            'webSearch' => false,
            'medicalGuidance' => '',
            'privacyGuidance' => '',
            'knowledge' => '',
            'qna' => [],
            'pages' => [],
            'available' => [],
            'loaded' => [],
            'tools' => [],
            'toolsMode' => true,
        ]);
    }

    private function skills(): SkillPackage
    {
        return SkillPackage::load(ChatHandler::skillDir(), ChatTools::NAMES);
    }
}
