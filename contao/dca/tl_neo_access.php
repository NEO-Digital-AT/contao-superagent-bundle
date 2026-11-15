<?php

declare(strict_types=1);

use Neo\BasicBundle\Access\AccessPage;
use Neo\SuperAgentBundle\Licence\Terms;
use Neo\SuperAgentBundle\SuperAgent;

/*
 * The SuperAgent's part of "Access keys and licences" (Basic bundle): it uses the
 * shared AI connection, keeps the SerpAPI key of the web search there and has
 * a licence row and its terms per edition.
 */
AccessPage::useAi('SuperAgent');

$GLOBALS['TL_DCA']['tl_neo_access']['fields']['superagent_serpapi_key'] = AccessPage::secretField();
// Optional: only assistants with web search via SerpAPI need it.
AccessPage::addSection('superagent_search_legend', ['superagent_serpapi_key'], 'SuperAgent', true);

AccessPage::addLicense(SuperAgent::LICENSE_MODULE, SuperAgent::PACKAGE, 'SuperAgent');

// Terms per edition; confirmed before an assistant is visible to visitors.
AccessPage::addTerms(SuperAgent::LICENSE_MODULE, 'SuperAgent', Terms::DOMAIN, Terms::PASSAGES);
