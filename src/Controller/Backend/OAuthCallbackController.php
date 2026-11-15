<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Controller\Backend;

use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Message;
use Contao\System;
use Neo\SuperAgentBundle\Backend\ConnectionCallbacks;
use Neo\SuperAgentBundle\Mcp\McpException;
use Neo\SuperAgentBundle\Mcp\OAuthFlow;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Back from the sign-in page of a service (OAuth): exchanges the code for
 * tokens, reads the tools and returns to the connection. Administrators only;
 * the state ties the answer to the sign-in started in this session.
 */
final class OAuthCallbackController
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly OAuthFlow $oauth,
        private readonly ConnectionCallbacks $callbacks,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $this->framework->initialize();
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin) {
            throw new AccessDeniedException('Only administrators may connect MCP servers.');
        }

        System::loadLanguageFile('tl_neo_agent_connection');
        $lang = $GLOBALS['TL_LANG']['tl_neo_agent_connection'];
        $params = ['do' => 'neo_agent', 'table' => 'tl_neo_agent_connection'];

        if ('' !== (string) $request->query->get('error')) {
            Message::addError(sprintf($lang['oauth_failed'] ?? '%s', mb_substr(strip_tags((string) ($request->query->get('error_description') ?: $request->query->get('error'))), 0, 200)));

            return new RedirectResponse($this->router->generate('contao_backend', $params));
        }

        try {
            $connection = $this->oauth->finish((string) $request->query->get('state'), (string) $request->query->get('code'), (string) $user->username);
        } catch (McpException $e) {
            Message::addError(sprintf($lang['oauth_failed'] ?? '%s', $e->getMessage()));

            return new RedirectResponse($this->router->generate('contao_backend', $params));
        }

        Message::addConfirmation($lang['oauth_done'] ?? '');
        $this->callbacks->read($connection);

        return new RedirectResponse($this->router->generate('contao_backend', $params + ['act' => 'edit', 'id' => (int) $connection->id]));
    }
}
