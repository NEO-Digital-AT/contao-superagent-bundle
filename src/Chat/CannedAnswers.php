<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Neo\SuperAgentBundle\Model\AgentLanguageModel;

/**
 * The questions and answers of a language record, and the starter questions
 * that have an answer. They are knowledge for the model (section QUESTIONS AND
 * ANSWERS), not replies the server sends itself: the model gives the answer of
 * a listed question in its own words. A starter question without an answer is
 * not listed; the model answers it on its own.
 */
final class CannedAnswers
{
    /**
     * @return array<string, string> question => answer, as the operator wrote them
     */
    public function pairs(?AgentLanguageModel $texts): array
    {
        $pairs = [];

        // Questions & answers, then the starter questions that have an answer: the answer set at a
        // starter question is the one for that question.
        foreach ([...StarterQuestions::rows($texts->canned ?? null), ...StarterQuestions::of($texts)] as $row) {
            if ('' !== $row['answer']) {
                $pairs[$row['question']] = $row['answer'];
            }
        }

        return $pairs;
    }
}
