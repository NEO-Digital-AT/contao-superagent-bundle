<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Backend;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Whether the cookie consent extension of this family (NEO Cookie Consent) can
 * ask visitors for the consent category of an assistant. Without an active
 * service in that category its banner never asks for it: where the banner
 * appears, the chat could never start. Read from the tables of that extension,
 * so that this bundle does not depend on it.
 */
final class CookieConsentCheck
{
    public const MODULE = 'neo_cookieconsent';

    public function __construct(
        private readonly Connection $db,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    /**
     * true/false: the extension is installed with its banner on and has / has no
     * active service in the category; null: not installed or switched off.
     */
    public function offers(string $category): bool|null
    {
        if ('' === $category || !isset($GLOBALS['BE_MOD']['neo'][self::MODULE])) {
            return null;
        }

        try {
            $active = $this->db->fetchOne('SELECT active FROM tl_neo_cookieconsent_settings ORDER BY id LIMIT 1');

            // No settings record yet: the extension's default is "on".
            if (false !== $active && '1' !== (string) $active) {
                return null;
            }

            return (int) $this->db->fetchOne("SELECT COUNT(*) FROM tl_neo_cookieconsent_service WHERE published = '1' AND tstamp > 0 AND category = ?", [$category]) > 0;
        } catch (\Throwable) {
            return null; // tables not created yet (before the database update)
        }
    }

    /**
     * The reason for the field: what happens, and a link that adds the AI chat
     * in this category (the extension's operation "add_service"), or who can do
     * it when the user may not open the cookie consent module.
     */
    public function reason(string $category): string
    {
        $lang = $GLOBALS['TL_LANG']['tl_neo_agent'] ?? [];
        $module = (string) ($GLOBALS['TL_LANG']['MOD'][self::MODULE][0] ?? 'Cookie consent');
        $text = StringUtil::specialchars(sprintf($lang['consent_unoffered'] ?? '%s', $module));

        if (!isset($GLOBALS['BE_MOD']['neo'][self::MODULE]['add_service']) || !$this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, self::MODULE)) {
            return $text.' '.StringUtil::specialchars(sprintf($lang['consent_ask_admin'] ?? '%s', $module));
        }

        $url = $this->router->generate('contao_backend', [
            'do' => self::MODULE,
            'key' => 'add_service',
            'template' => 'ai-chat',
            'category' => $category,
            'rt' => (string) System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(),
        ]);

        return sprintf('%s <a href="%s">%s</a>', $text, StringUtil::specialchars($url), StringUtil::specialchars($lang['consent_add'] ?? 'Add'));
    }
}
