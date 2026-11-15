<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Tests\Chat;

use Neo\SuperAgentBundle\Chat\CannedAnswers;
use Neo\SuperAgentBundle\Chat\StarterQuestions;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;
use Neo\SuperAgentBundle\Tests\Support\Models;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StarterQuestions::class)]
#[CoversClass(CannedAnswers::class)]
final class StarterQuestionsTest extends TestCase
{
    public function testRowsOfTheRowWizard(): void
    {
        $this->assertSame(
            [['question' => 'When are you open?', 'answer' => 'Monday to Friday.'], ['question' => 'Where are you?', 'answer' => '']],
            StarterQuestions::rows(serialize([['question' => ' When are you open? ', 'answer' => ' Monday to Friday. '], ['question' => 'Where are you?', 'answer' => ''], ['question' => '  ', 'answer' => 'No question']])),
        );
    }

    public function testRowsOfOlderContaoVersionsAndTheFormerFormat(): void
    {
        $this->assertSame([['question' => 'Prices?', 'answer' => 'On request.']], StarterQuestions::rows(serialize([['key' => 'Prices?', 'value' => 'On request.']])));
        $this->assertSame([['question' => 'Parking?', 'answer' => 'In the yard | free']], StarterQuestions::rows(serialize(['Parking? | In the yard | free', 'No pipe here'])));
        $this->assertSame([], StarterQuestions::rows(null));
        $this->assertSame([], StarterQuestions::rows('not serialized'));
    }

    public function testTheFormerListIsReadUntilTheMigrationRan(): void
    {
        $before = Models::make(AgentLanguageModel::class, ['starter_questions' => null, 'suggestions' => serialize(['What do you offer?', ' ', 'How much is it?'])]);
        $after = Models::make(AgentLanguageModel::class, ['starter_questions' => serialize([['question' => 'New?', 'answer' => 'Yes.']]), 'suggestions' => serialize(['Old?'])]);

        $this->assertSame([['question' => 'What do you offer?', 'answer' => ''], ['question' => 'How much is it?', 'answer' => '']], StarterQuestions::of($before));
        $this->assertSame([['question' => 'New?', 'answer' => 'Yes.']], StarterQuestions::of($after));
        $this->assertSame([], StarterQuestions::of(null));
    }

    public function testTheModelKnowsTheQuestionsThatHaveAnAnswer(): void
    {
        $texts = Models::make(AgentLanguageModel::class, [
            'canned' => serialize([['question' => 'Prices?', 'answer' => 'On request.'], ['question' => 'Open?', 'answer' => 'Weekdays.']]),
            'starter_questions' => serialize([['question' => 'Open?', 'answer' => 'Monday to Friday, 9 to 17.'], ['question' => 'Without answer?', 'answer' => '']]),
        ]);

        $this->assertSame(['Prices?' => 'On request.', 'Open?' => 'Monday to Friday, 9 to 17.'], (new CannedAnswers())->pairs($texts), 'the answer at a starter question is the one for it');
        $this->assertSame([], (new CannedAnswers())->pairs(null));
    }
}
