<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Skill;

/**
 * A skill could not be fetched. The code names the reason for the back-end text
 * (TL_LANG.tl_neo_agent_skill.source_<code>), the detail is technical.
 */
final class SkillSourceException extends \RuntimeException
{
    public function __construct(
        public readonly string $reason,
        public readonly string $detail = '',
    ) {
        parent::__construct($reason.('' !== $detail ? ': '.$detail : ''));
    }
}
