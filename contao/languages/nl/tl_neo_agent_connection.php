<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Nieuwe verbinding', 'Een MCP-server verbinden.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Bewerken', 'Verbinding bewerken.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Verwijderen', 'Verbinding verwijderen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Details', 'Details tonen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Assistenten', 'Terug naar de lijst met assistenten.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Verbinding';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Aanmelding';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Persoonsgegevens';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Tools';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Bevestiging';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Naam', 'De naam van de verbinding in de lijst en bij de keuze aan de assistent, bijv. “Afspraken”.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Adres van de MCP-server', 'Het adres dat de aanbieder voor zijn MCP-server opgeeft, bijv. https://mcp.example.com/mcp. Alleen HTTPS en alleen openbare adressen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Aanmelding', 'Hoe de website zich bij de server aanmeldt. Dat staat in de handleiding van de aanbieder.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Zonder aanmelding',
    'key' => 'Sleutel (Authorization: Bearer)',
    'headers' => 'Eigen headers',
    'oauth' => 'OAuth (aanmelden bij de dienst)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Persoonsgegevens', 'Geldt in beide richtingen: voor wat de assistent naar de dienst stuurt en voor de resultaten die naar de AI-dienst gaan. Herkend worden e-mailadressen, telefoonnummers, IBAN, kaartnummers, burgerservice-/sofinummers en geboortedata, geen namen en adressen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Maskeren: vervangen door een plaatshouder',
    'block' => 'Niet versturen: aanroep of resultaat tegenhouden',
    'send' => 'Alles versturen',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Sleutel', 'Wordt meegestuurd als “Authorization: Bearer …”. Wordt versleuteld opgeslagen en niet meer getoond. Leeg laten behoudt de opgeslagen sleutel.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Eigen headers', 'Naam en waarde per regel, bijv. “X-Api-Key”. De waarden worden versleuteld opgeslagen en niet meer getoond; een lege waarde behoudt de opgeslagen waarde.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['Client-ID (alleen bij eigen app)', 'Alleen nodig als de dienst geen automatische registratie biedt: maak daar een app aan, vul daar het doorstuuradres hierboven in en hier de client-ID. Leeg = automatische registratie.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Client-secret (alleen bij eigen app)', 'Het secret van de app, als de dienst er een uitgeeft. Wordt versleuteld opgeslagen en niet meer getoond.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Let op: via een MCP-server kan de assistent functies van een ander systeem aanroepen – ook functies die daar gegevens wijzigen, verwijderen of klantgegevens tonen. Elke bezoeker van de chat werkt daarbij met de toegang van deze verbinding. Geef alleen tools vrij met weinig risico of tools die een skill begrenst, en test de assistent voordat u hem voor bezoekers zichtbaar maakt. Tools die volgens de server gegevens wijzigen of verwijderen, of niet gemarkeerd zijn, zijn standaard vergrendeld. Er blijft een restrisico, want een taalmodel kan zich vergissen of door slim geformuleerde berichten misleid worden. De fabrikant van deze extensie aanvaardt geen aansprakelijkheid voor schade door vrijgegeven tools. Wat precies geldt en welke uitzonderingen er zijn, staat in de licentievoorwaarden.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'actief · %d van %d tools vrijgegeven';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'tools niet gelezen';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'niet bevestigd';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Voer een adres in dat begint met https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'De headernaam “%s” is ongeldig. Letters, cijfers en koppeltekens zijn toegestaan.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Aanmelding bij de dienst';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Aangemeld door %s op %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Nog niet aangemeld. Sla het adres op en klik dan op “Verbinden”: u gaat naar de aanmeldpagina van de dienst en komt daarna hier terug.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Verbinden';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Opnieuw verbinden';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Afmelden';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Doorstuuradres van deze website (alleen nodig voor een eigen app bij de dienst):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'De aanmelding bij de dienst is verwijderd.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'De aanmelding bij de dienst is mislukt: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'De aanmelding bij de dienst is opgeslagen.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'De tools zijn nog niet gelezen. Ze worden na het opslaan gelezen, zodra de aanmelding volledig is.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'Het adres is gewijzigd. De tools worden bij het opslaan opnieuw gelezen; tot dan is de verbinding niet actief.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d tools van %s, gelezen op %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Tools opnieuw lezen';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = '%d tools gelezen, waarvan %d vergrendeld.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'De tools konden niet worden gelezen: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Tools en vergrendelingen';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Alle tools vergrendelen die volgens de server gegevens wijzigen of verwijderen, of niet gemarkeerd zijn';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Vergrendeling';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Tool';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Soort volgens de server';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Opmerking';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'vergrendeld';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NIEUW';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'heeft persoonsgegevens nodig';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Geen enkele assistent kan een vergrendelde tool gebruiken. Wie een vergrendeling opheft, bevestigt dat hieronder; wie het deed en wanneer wordt bewaard. Nieuwe tools van de server zijn bij geen enkele assistent aangevinkt.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'alleen lezen',
    'change' => 'wijzigt gegevens',
    'delete' => 'verwijdert gegevens',
    'unmarked' => 'niet gemarkeerd',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Vergrendeling niet opgeheven: %s. Bevestig daarvoor hieronder dat u de tools hebt gecontroleerd.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Bevestigd door %s op %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Nog niet bevestigd. Pas daarna kunnen assistenten de tools van deze verbinding gebruiken.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'Ik heb de vrijgegeven tools gecontroleerd en begrijp het restrisico.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Nodig voor de eerste vrijgave van de verbinding en telkens wanneer u een vergrendeling opheft.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Sjabloon', 'Vult naam, adres en aanmelding in zolang deze velden leeg zijn, en legt vast hoe de tools van deze dienst worden ingedeeld en vergrendeld. Een skill van het sjabloon wordt bij het opslaan geïnstalleerd. Leeg laten voor een willekeurige MCP-server.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Handleiding van de aanbieder:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'aanbevolen';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'toont klantgegevens';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Deze dienst heeft een eigen app nodig: maak bij de dienst een app aan en vul daar het doorstuuradres in dat hieronder staat. Client-ID en client-secret van de app komen in de velden daaronder. Daarna “Verbinden”.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'De skill “%s” van het sjabloon is geïnstalleerd. Vink hem aan bij de assistenten die hem moeten gebruiken.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'De skill van het sjabloon “%s” kon niet worden geïnstalleerd: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Instellen bij %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Kopiëren';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Gekopieerd';
