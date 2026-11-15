<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Security;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\UserModel;
use Neo\SuperAgentBundle\Model\AgentModel;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Assistants in "preview only" are shown to administrators logged in to the back
 * end, in the Contao front-end preview; visitors never see them and the chat
 * endpoints treat them as absent for everybody else.
 */
final class PreviewAccess
{
    public const PREVIEW = 'preview';

    private bool|null $admin = null;

    public function __construct(
        private readonly TokenChecker $tokenChecker,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @param bool $inPreview true for page output (only in the front-end preview),
     *                        false for the chat endpoints called from that page
     */
    public function canSee(AgentModel $agent, bool $inPreview): bool
    {
        return self::PREVIEW !== (string) $agent->visibility || $this->adminViewing($inPreview);
    }

    /** A back-end administrator is logged in (and, for page output, viewing the preview). */
    public function adminViewing(bool $inPreview): bool
    {
        if ($inPreview && !$this->requestStack->getCurrentRequest()?->attributes->get('_preview', false)) {
            return false;
        }

        if (null === $this->admin) {
            $name = $this->tokenChecker->getBackendUsername();
            $user = null !== $name ? UserModel::findByUsername($name) : null;
            $this->admin = null !== $user && $user->admin && !$user->disable;
        }

        return $this->admin;
    }
}
