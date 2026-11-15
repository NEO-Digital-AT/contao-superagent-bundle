<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

/**
 * Language models that passed the booking test of the apaleo template with
 * tools of a connection twice (several steps in a row: offers, data from the
 * conversation, booking once after the approval, payment link in the same
 * reply, address and phone number entered in the right format). Weaker
 * models ask again, skip steps or give up; the back end says so when an
 * assistant uses tools of a connection with a model outside this list.
 *
 * Model names without the provider and region of the route
 * ("azure/gpt-6-luna@swedencentral" is "gpt-6-luna").
 */
final class TestedModels
{
    /** Date of the last test run. */
    public const TESTED_AT = '2026-11-15';

    /** @var array<string, string> model name => name shown */
    public const MODELS = [
        'gpt-6-luna' => 'GPT-6 Luna',
        'gpt-6-sol' => 'GPT-6 Sol',
        'gpt-6.1-sol' => 'GPT-6.1 Sol',
        'gpt-5.6-luna' => 'GPT-5.6 Luna',
        'gpt-5.5' => 'GPT-5.5',
        'claude-sonnet-4-5' => 'Claude Sonnet 4.5',
        'mistral-large-4' => 'Mistral Large 4',
    ];

    /** The names shown, in the order of the list. */
    public static function names(): string
    {
        return implode(', ', self::MODELS);
    }

    /** Whether a model of a route ("provider/name@region") is one of the tested. */
    public static function covers(string $model): bool
    {
        return isset(self::MODELS[self::name($model)]);
    }

    private static function name(string $model): string
    {
        $name = strtolower(trim($model));
        $name = str_contains($name, '/') ? substr($name, strrpos($name, '/') + 1) : $name;

        return str_contains($name, '@') ? strstr($name, '@', true) : $name;
    }
}
