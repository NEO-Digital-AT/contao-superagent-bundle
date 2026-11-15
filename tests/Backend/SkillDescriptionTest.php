<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Backend;

use Neo\SuperAgentBundle\Backend\SkillCallbacks;
use PHPUnit\Framework\TestCase;

final class SkillDescriptionTest extends TestCase
{
    public function testAShortDescriptionStaysAsItIs(): void
    {
        $this->assertSame('Books a room.', SkillCallbacks::shorten('  Books a room. ', 140));
    }

    public function testALongDescriptionIsCutAtAWordWithAnEllipsis(): void
    {
        $text = str_repeat('Answers questions about the hotel, ', 6);
        $short = SkillCallbacks::shorten($text, 140);

        $this->assertLessThanOrEqual(140, mb_strlen($short));
        $this->assertStringEndsWith('…', $short);
        $this->assertStringStartsWith(mb_substr($short, 0, -1), $text, 'only whole words are kept');
        $this->assertDoesNotMatchRegularExpression('/[ ,;:.\-–]…$/u', $short, 'no space or punctuation before the ellipsis');
        $this->assertStringStartsWith(mb_substr($short, 0, -1).' ', $text, 'ends after a whole word');
    }

    public function testAWordLongerThanHalfTheLimitIsCutInside(): void
    {
        $short = SkillCallbacks::shorten(str_repeat('x', 200), 20);

        $this->assertSame(str_repeat('x', 19).'…', $short);
    }
}
