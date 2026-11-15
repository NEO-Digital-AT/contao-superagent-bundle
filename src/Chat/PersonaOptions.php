<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

/**
 * The assistant's persona as SELECT LISTS instead of a free-text prompt: form of
 * address, answer length and tone. The keys are stored; the values describe the
 * choice to the model in SETTINGS (the core skill says to follow them).
 */
final class PersonaOptions
{
    /** Form of address. */
    public const ADDRESS = [
        'sie' => 'formal (the formal form of address where the language has one)',
        'du' => 'informal (the way friends talk, where the language makes that distinction)',
    ];

    /** Answer length. */
    public const LENGTH = [
        'short' => 'short, 2–3 sentences',
        'medium' => 'medium, about 4–6 sentences',
        'long' => 'thorough, a short list where it helps',
    ];

    /** Tone. */
    public const STYLE = [
        'professional' => 'professional, factual, no marketing fluff',
        'friendly' => 'warm, welcoming, easy to understand',
        'expert' => 'precise and technically accurate, still understandable',
    ];
}
