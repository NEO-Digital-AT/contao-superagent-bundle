<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Nova povezava', 'Poveži strežnik MCP.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Uredi', 'Uredi povezavo.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Izbriši', 'Izbriši povezavo.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Podrobnosti', 'Prikaži podrobnosti.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Asistenti', 'Nazaj na seznam asistentov.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Povezava';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Prijava';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Osebni podatki';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Orodja';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Potrditev';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Ime', 'Ime povezave na seznamu in pri izbiri pri asistentu, npr. »Rezervacija terminov«.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Naslov strežnika MCP', 'Naslov, ki ga ponudnik navaja za svoj strežnik MCP, npr. https://mcp.example.com/mcp. Samo HTTPS in samo javni naslovi.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Prijava', 'Kako se spletno mesto prijavi na strežnik. To piše v navodilih ponudnika.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Brez prijave',
    'key' => 'Ključ (Authorization: Bearer)',
    'headers' => 'Lastne glave',
    'oauth' => 'OAuth (prijava pri storitvi)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Osebni podatki', 'Velja v obe smeri: za to, kar asistent pošlje storitvi, in za rezultate, ki gredo k storitvi UI. Prepoznani so e-poštni naslovi, telefonske številke, IBAN, številke kartic, številke socialnega zavarovanja in datumi rojstva, ne imena in naslovi.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Maskiraj: zamenjaj z nadomestnim besedilom',
    'block' => 'Ne pošlji: zadrži klic ali rezultat',
    'send' => 'Pošlji vse',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Ključ', 'Pošlje se kot »Authorization: Bearer …«. Shrani se šifrirano in se ne prikaže več. Prazno polje ohrani shranjeni ključ.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Lastne glave', 'Ime in vrednost v vsaki vrstici, npr. »X-Api-Key«. Vrednosti se shranijo šifrirano in se ne prikažejo več; prazna vrednost ohrani shranjeno.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['ID odjemalca (samo lastna aplikacija)', 'Potrebno samo, če storitev ne ponuja samodejne registracije: tam ustvarite aplikacijo, tam vnesite zgornji naslov preusmeritve, tukaj pa ID odjemalca. Prazno = samodejna registracija.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Skrivnost odjemalca (samo lastna aplikacija)', 'Skrivnost aplikacije, če jo storitev izda. Shrani se šifrirano in se ne prikaže več.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Pozor: prek strežnika MCP lahko asistent kliče funkcije drugega sistema – tudi take, ki tam spreminjajo ali brišejo podatke ali prikazujejo podatke strank. Vsak obiskovalec klepeta pri tem dela z dostopom te povezave. Omogočite samo orodja z majhnim tveganjem ali orodja, ki jih omejuje skill, in asistenta preizkusite, preden ga omogočite obiskovalcem. Orodja, ki po navedbah strežnika spreminjajo ali brišejo podatke ali niso označena, so privzeto zaklenjena. Preostalo tveganje kljub temu ostane, saj se jezikovni model lahko zmoti ali ga zavedejo spretno oblikovana sporočila. Proizvajalec te razširitve ne prevzema odgovornosti za škodo, ki jo povzročijo omogočena orodja. Kaj natančno velja in katere so izjeme, določajo licenčni pogoji.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'aktivna · omogočenih %d od %d orodij';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'orodja niso prebrana';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'ni potrjena';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Vnesite naslov, ki se začne s https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'Ime glave »%s« ni veljavno. Dovoljene so črke, številke in vezaji.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Prijava pri storitvi';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Prijavil(a) %s dne %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Še ni prijave. Shranite naslov, nato »Poveži«: preusmerjeni boste na stran za prijavo storitve in se nato vrnete sem.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Poveži';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Poveži znova';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Odjava';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Naslov preusmeritve tega spletnega mesta (potreben samo za lastno aplikacijo pri storitvi):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'Prijava pri storitvi je bila odstranjena.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'Prijava pri storitvi ni uspela: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'Prijava pri storitvi je shranjena.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Orodja še niso prebrana. Prebrana bodo po shranjevanju, ko bo prijava popolna.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'Naslov se je spremenil. Orodja se ob shranjevanju preberejo znova; do takrat povezava ni aktivna.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d orodij od %s, prebrano %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Znova preberi orodja';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = 'Prebranih orodij: %d, od tega zaklenjenih: %d.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'Orodij ni bilo mogoče prebrati: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Orodja in zaklepi';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Zakleni vsa orodja, ki po navedbah strežnika spreminjajo ali brišejo podatke ali niso označena';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Zaklep';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Orodje';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Vrsta po navedbah strežnika';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Opomba';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'zaklenjeno';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NOVO';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'potrebuje osebne podatke';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Zaklenjenega orodja ne more uporabiti noben asistent. Kdor odstrani zaklep, to potrdi spodaj; shrani se, kdo in kdaj je to storil. Nova orodja strežnika niso označena pri nobenem asistentu.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'samo bere',
    'change' => 'spreminja podatke',
    'delete' => 'briše podatke',
    'unmarked' => 'ni označeno',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Zaklep ni odstranjen: %s. Za odstranitev spodaj potrdite, da ste orodja preverili.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Potrdil(a) %s dne %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Še ni potrjena. Šele potem lahko asistenti uporabljajo orodja te povezave.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'Preveril(a) sem omogočena orodja in razumem preostalo tveganje.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Potrebno za prvo omogočitev povezave in vsakič, ko odstranite zaklep.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Predloga', 'Izpolni ime, naslov in prijavo, dokler so ta polja prazna, ter določi, kako so orodja te storitve razvrščena in zaklenjena. Skill predloge se namesti ob shranjevanju. Za poljuben strežnik MCP pustite prazno.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Navodila ponudnika:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'priporočeno';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'prikazuje podatke strank';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Ta storitev potrebuje lastno aplikacijo: pri storitvi ustvarite aplikacijo in tam vnesite naslov preusmeritve, ki je naveden spodaj. ID odjemalca in skrivnost odjemalca aplikacije vnesite v polji pod tem. Nato »Poveži«.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'Skill »%s« predloge je nameščen. Označite ga pri asistentih, ki naj ga uporabljajo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'Skilla predloge »%s« ni bilo mogoče namestiti: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Nastavitev pri storitvi %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Kopiraj';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Kopirano';
