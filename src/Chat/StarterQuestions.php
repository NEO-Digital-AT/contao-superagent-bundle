<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Contao\StringUtil;
use Neo\SuperAgentBundle\Model\AgentLanguageModel;

/**
 * The starter questions of a language record: the questions a visitor can
 * click at the start of a conversation, each with an optional answer the
 * assistant gives for it (in its own words). Stored as rows of question and
 * answer in starter_questions. Until StarterQuestionsMigration has run (the
 * column is still NULL), the former list of suggested questions (suggestions,
 * one question per row) is read, without answers.
 */
final class StarterQuestions
{
    /** @return list<array{question: string, answer: string}> */
    public static function of(AgentLanguageModel|null $texts): array
    {
        if (null === $texts) {
            return [];
        }

        $stored = $texts->starter_questions ?? null;

        return null === $stored ? self::fromList($texts->suggestions ?? null) : self::rows($stored);
    }

    /**
     * Rows as the row wizard stores them ([{question, answer}]), as the key-value
     * wizard of older Contao versions submits them ([{key, value}]) or in the
     * former "Question | Answer" form: trimmed, rows without a question left out.
     *
     * @return list<array{question: string, answer: string}>
     */
    public static function rows(mixed $value): array
    {
        $rows = [];

        foreach (StringUtil::deserialize($value, true) as $row) {
            if (\is_array($row)) {
                $question = trim((string) ($row['question'] ?? $row['key'] ?? ''));
                $answer = trim((string) ($row['answer'] ?? $row['value'] ?? ''));
            } elseif (\is_string($row) && str_contains($row, '|')) {
                // Former format of the questions & answers: "Question | Answer".
                [$question, $answer] = array_map('trim', explode('|', $row, 2));
            } else {
                continue;
            }

            if ('' !== $question) {
                $rows[] = ['question' => $question, 'answer' => $answer];
            }
        }

        return $rows;
    }

    /**
     * The former list (one question per row) as starter questions without an answer.
     *
     * @return list<array{question: string, answer: string}>
     */
    public static function fromList(mixed $value): array
    {
        $rows = [];

        foreach (StringUtil::deserialize($value, true) as $question) {
            $question = \is_string($question) ? trim($question) : '';

            if ('' !== $question) {
                $rows[] = ['question' => $question, 'answer' => ''];
            }
        }

        return $rows;
    }
}
