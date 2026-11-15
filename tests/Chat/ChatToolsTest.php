<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\ChatTools;
use Neo\SuperAgentBundle\Chat\DefaultTexts;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Neo\SuperAgentBundle\Tests\Support\Translations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChatTools::class)]
#[CoversClass(DefaultTexts::class)]
final class ChatToolsTest extends TestCase
{
    public function testEveryToolIsAFunctionWithItsRequiredArguments(): void
    {
        foreach (ChatTools::NAMES as $name) {
            $definition = ChatTools::definition($name);

            $this->assertSame('function', $definition['type']);
            $this->assertSame($name, $definition['function']['name']);
            $this->assertSame('object', $definition['function']['parameters']['type']);
            $this->assertSame([], array_diff($definition['function']['parameters']['required'], array_keys($definition['function']['parameters']['properties'])));
            $this->assertStringEndsWith('.', ChatTools::summary($name));
            $this->assertStringNotContainsString('. ', ChatTools::summary($name), 'the first sentence only');
        }
    }

    public function testAnUnknownToolIsAnError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ChatTools::definition('delete_everything');
    }

    public function testTheLoadersOfferOnlyWhatCanStillBeLoaded(): void
    {
        $this->assertSame(['contact', 'navigation'], ChatTools::loadModule(['contact', 'navigation'])['function']['parameters']['properties']['modules']['items']['enum']);
        $this->assertSame(['web_search'], ChatTools::loadTools(['web_search'])['function']['parameters']['properties']['tools']['items']['enum']);
    }

    public function testDefaultTextsInEveryShippedLanguage(): void
    {
        $texts = new DefaultTexts(Translations::translator());

        foreach (DefaultTexts::LANGUAGES as $language) {
            $record = $texts->forRecord($language);

            $this->assertCount(2 + \count(AgentLanguageModel::UI_KEYS), $record);

            foreach ($record as $column => $text) {
                $this->assertStringNotContainsString('widget.', $text, $language.': '.$column);
                $this->assertNotSame('', trim($text), $language.': '.$column);
            }
        }
    }

    public function testTheLocaleOfTheTexts(): void
    {
        $this->assertSame('de', DefaultTexts::locale('de_AT'));
        $this->assertSame('hr', DefaultTexts::locale(' hr-HR '));
        $this->assertSame('en', DefaultTexts::locale('pt-BR'), 'not shipped');
        $this->assertSame('en', DefaultTexts::locale(''));
    }
}
