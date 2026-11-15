<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Neo\BasicBundle\License\CreditLine;
use Neo\SuperAgentBundle\Model\AgentModel;
use Neo\SuperAgentBundle\Security\PreviewAccess;

/**
 * Enqueues the widget CSS/JS via the layout (TL_CSS / TL_JAVASCRIPT) so Contao
 * combines and minifies them with the rest of the page assets — never a raw
 * <link>/<script> in the template. Loaded only when an assistant is published,
 * and only on the main request (so it reliably reaches the page head/body).
 * The credit line of the free edition comes from the shared script of the
 * Basic bundle (CreditLine), the same for every extension.
 */
final class AssetListener
{
    public function __construct(
        private readonly CreditLine $credit,
        private readonly PreviewAccess $preview,
    ) {
    }

    #[AsHook('getPageLayout')]
    public function onGetPageLayout(PageModel $page, LayoutModel $layout, PageRegular $pageRegular): void
    {
        // Assistants in "preview only" count for administrators in the front-end preview.
        if (null === AgentModel::findPublishedAgent($this->preview->adminViewing(true))) {
            return;
        }

        $GLOBALS['TL_CSS']['neo_superagent'] = 'bundles/neosuperagent/neo-agent.css|all|static';
        $this->credit->register();
        // |async: the script waits for DOMContentLoaded itself, so it must not
        // block HTML parsing in the <head>.
        $GLOBALS['TL_JAVASCRIPT']['neo_superagent'] = 'bundles/neosuperagent/neo-agent.js|async|static';
    }
}
