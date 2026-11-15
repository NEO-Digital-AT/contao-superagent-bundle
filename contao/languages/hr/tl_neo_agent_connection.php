<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Nova veza', 'Poveži MCP poslužitelj.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Uredi', 'Uredi vezu.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Izbriši', 'Izbriši vezu.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Pojedinosti', 'Prikaži pojedinosti.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Asistenti', 'Natrag na popis asistenata.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Veza';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Prijava';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Osobni podaci';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Alati';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Potvrda';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Naziv', 'Naziv veze na popisu i pri odabiru kod asistenta, npr. „Rezervacija termina”.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Adresa MCP poslužitelja', 'Adresa koju pružatelj navodi za svoj MCP poslužitelj, npr. https://mcp.example.com/mcp. Samo HTTPS i samo javne adrese.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Prijava', 'Kako se web-mjesto prijavljuje na poslužitelj. To piše u uputama pružatelja.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Bez prijave',
    'key' => 'Ključ (Authorization: Bearer)',
    'headers' => 'Vlastita zaglavlja',
    'oauth' => 'OAuth (prijava kod usluge)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Osobni podaci', 'Vrijedi u oba smjera: za ono što asistent šalje usluzi i za rezultate koji idu usluzi UI. Prepoznaju se adrese e-pošte, telefonski brojevi, IBAN, brojevi kartica, brojevi socijalnog osiguranja i datumi rođenja, ne imena i adrese.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Maskiraj: zamijeni rezerviranim tekstom',
    'block' => 'Ne šalji: zadrži poziv ili rezultat',
    'send' => 'Šalji sve',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Ključ', 'Šalje se kao „Authorization: Bearer …”. Sprema se šifrirano i više se ne prikazuje. Prazno polje zadržava spremljeni ključ.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Vlastita zaglavlja', 'Naziv i vrijednost po retku, npr. „X-Api-Key”. Vrijednosti se spremaju šifrirano i više se ne prikazuju; prazna vrijednost zadržava spremljenu.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['ID klijenta (samo vlastita aplikacija)', 'Potrebno samo ako usluga ne nudi automatsku registraciju: ondje izradite aplikaciju, ondje unesite gornju adresu preusmjeravanja, a ovdje ID klijenta. Prazno = automatska registracija.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Tajna klijenta (samo vlastita aplikacija)', 'Tajna aplikacije, ako je usluga izdaje. Sprema se šifrirano i više se ne prikazuje.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Pozor: putem MCP poslužitelja asistent može pozivati funkcije drugog sustava – i one koje ondje mijenjaju ili brišu podatke ili prikazuju podatke klijenata. Svaki posjetitelj chata pritom radi s pristupom ove veze. Omogućite samo alate s malim rizikom ili alate koje ograničava skill i testirajte asistenta prije nego što ga omogućite posjetiteljima. Alati koji prema poslužitelju mijenjaju ili brišu podatke ili nisu označeni, zadano su zaključani. Preostali rizik ipak ostaje jer se jezični model može prevariti ili ga mogu zavarati vješto sročene poruke. Proizvođač ovog proširenja ne preuzima odgovornost za štetu nastalu omogućenim alatima. Što točno vrijedi i koje su iznimke, navedeno je u licencnim uvjetima.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'aktivna · omogućeno %d od %d alata';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'alati nisu pročitani';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'nije potvrđena';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Unesite adresu koja počinje s https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'Naziv zaglavlja „%s” nije valjan. Dopuštena su slova, znamenke i crtice.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Prijava kod usluge';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Prijavio/la %s dana %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Još niste prijavljeni. Spremite adresu, zatim „Poveži”: bit ćete preusmjereni na stranicu za prijavu usluge i potom se vratiti ovamo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Poveži';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Poveži ponovno';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Odjava';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Adresa preusmjeravanja ovog web-mjesta (potrebna samo za vlastitu aplikaciju kod usluge):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'Prijava kod usluge je uklonjena.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'Prijava kod usluge nije uspjela: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'Prijava kod usluge je spremljena.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Alati još nisu pročitani. Pročitat će se nakon spremanja, čim prijava bude potpuna.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'Adresa se promijenila. Alati se pri spremanju ponovno čitaju; do tada veza nije aktivna.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d alata s %s, pročitano %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Ponovno pročitaj alate';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = 'Pročitano alata: %d, od toga zaključano: %d.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'Alate nije bilo moguće pročitati: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Alati i zaključavanja';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Zaključaj sve alate koji prema poslužitelju mijenjaju ili brišu podatke ili nisu označeni';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Zaključavanje';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Alat';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Vrsta prema poslužitelju';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Napomena';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'zaključano';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NOVO';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'treba osobne podatke';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Zaključani alat ne može koristiti nijedan asistent. Tko ukloni zaključavanje, potvrđuje to dolje; sprema se tko je to učinio i kada. Novi alati poslužitelja nisu označeni ni kod jednog asistenta.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'samo čita',
    'change' => 'mijenja podatke',
    'delete' => 'briše podatke',
    'unmarked' => 'nije označeno',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Zaključavanje nije uklonjeno: %s. Za uklanjanje dolje potvrdite da ste provjerili alate.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Potvrdio/la %s dana %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Još nije potvrđena. Tek tada asistenti mogu koristiti alate ove veze.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'Provjerio/la sam omogućene alate i razumijem preostali rizik.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Potrebno za prvo omogućivanje veze i svaki put kada uklonite zaključavanje.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Predložak', 'Ispunjava naziv, adresu i prijavu dok su ta polja prazna te određuje kako se alati ove usluge razvrstavaju i zaključavaju. Skill predloška instalira se pri spremanju. Za bilo koji MCP poslužitelj ostavite prazno.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Upute pružatelja:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'preporučeno';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'prikazuje podatke kupaca';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Ova usluga treba vlastitu aplikaciju: izradite aplikaciju kod usluge i ondje upišite adresu preusmjeravanja navedenu dolje. ID klijenta i tajnu klijenta aplikacije upišite u polja ispod. Zatim „Poveži”.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'Skill „%s” predloška je instaliran. Označite ga kod asistenata koji ga trebaju koristiti.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'Skill predloška „%s” nije bilo moguće instalirati: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Postavljanje kod usluge %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Kopiraj';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Kopirano';
