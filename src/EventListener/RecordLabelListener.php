<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\EventListener;

use Contao\CoreBundle\Event\DataContainerRecordLabelEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Breadcrumb labels: the assistant's title and "Language DE" for a language
 * record instead of Contao's generic "Edit element ID 1".
 */
#[AsEventListener]
final class RecordLabelListener
{
    public function __invoke(DataContainerRecordLabelEvent $event): void
    {
        if (null !== $event->getLabel()) {
            return;
        }

        $id = $event->getIdentifier();
        $row = $event->getData();

        if (str_starts_with($id, 'contao.db.tl_neo_agent.')) {
            $title = trim((string) ($row['title'] ?? ''));
            $name = trim((string) ($row['assistant_name'] ?? ''));
            $event->setLabel('' !== $name && $name !== $title ? sprintf('%s [%s]', $title, $name) : $title);
        } elseif (str_starts_with($id, 'contao.db.tl_neo_agent_language.')) {
            $event->setLabel(strtoupper((string) ($row['language'] ?? '')));
        }
    }
}
