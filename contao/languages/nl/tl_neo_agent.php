<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Algemeen';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identiteit';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Model';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Systeemprompt';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Kennisbank (grounding)';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Doorsturen & toestemming';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Benaming', 'Interne naam van deze assistent (alleen in de back-end).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Actief', 'Op de website wordt maar één actieve assistent getoond.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Weergavenaam', 'Hoe de assistent zich voorstelt en in de kop van de chat verschijnt (bijv. “Jan”). Geldt voor alle talen.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Model', 'Het taalmodel van deze assistent. Route en sleutel staan onder „Toegang en licenties” › AI; zodra daar een sleutel is opgeslagen, worden hier de modellen van de route aangeboden.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Temperatuur', 'Hoe vrij het model formuleert: lager = zakelijker en gelijkmatiger. Voor een chat past 0,2 tot 0,5.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Antwoordlengte (max. tokens)', 'Maximale lengte van een antwoord. Bij modellen die nadenken komt er ruimte voor het nadenken bij.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Systeemprompt', 'Gedragsregels voor de assistent. {name} wordt vervangen door de weergavenaam. Geldt voor alle talen.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Kennisbron', 'Bron van de gecontroleerde feiten waarop de assistent zich baseert. Geldt voor alle talen.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Kennistekst', 'Gecontroleerde feiten als tekst, die als context aan de systeemprompt worden toegevoegd.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Kennisbestand', 'Een bestand (txt/md/json) uit het bestandsbeheer dat als kennisbank dient.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['E-mailadres voor doorsturen', 'Adres waarnaar verzoeken via “doorsturen naar het team” worden verstuurd. Leeg laten als er geen is – de assistent biedt dan nooit doorsturen aan en verwijst naar de contactmogelijkheden van de website.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Doorsturen naar (naam)', 'Verschijnt op de knoppen in de chat, bijv. “Doorsturen naar Jan”. Leeg laten voor “het team”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Toestemmingscategorie', 'Categorie van de cookiebanner na wier toestemming de chat verschijnt. De cookie-toestemmingsextensie uit dezelfde reeks werkt zonder verdere instelling; een andere toestemmingstool heeft een klein script nodig (documentatie, ‘Customising’). Zonder toestemmingstool op de pagina is de chat direct beschikbaar.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Talen', 'De taalvarianten (begroeting, suggesties, vragen en antwoorden …) van deze assistent beheren.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Geen',
    'text' => 'Tekst (zelf ingevoerd)',
    'file' => 'Bestand uit het bestandsbeheer',
    'llms' => 'De llms.txt van de website (aanbevolen)',
    'llms_text' => 'llms.txt + eigen aanvullende tekst',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Persoonlijkheid & antwoordstijl';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Instellingen voor experts';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Aanspreekvorm', 'Hoe moet de assistent bezoekers aanspreken?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Lengte van antwoorden', 'Hoe uitgebreid moeten de antwoorden zijn?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Toon', 'De basistoon van de antwoorden.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Zoeken op het web';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Doorverwijzing — naar wie bezoekers worden verwezen';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Zoeken op het web toestaan', 'De assistent mag zelf besluiten om voor een vraag iets op het web op te zoeken — maar alleen in de hieronder goedgekeurde bronnen. Zonder deze optie antwoordt hij uitsluitend vanuit de kennisbank.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Zoekdienst', '“Alleen goedgekeurde bronnen lezen” haalt de vermelde adressen rechtstreeks op (gratis, ideaal voor de llms.txt van fabrikanten). “SerpAPI” zoekt daarnaast via een zoekmachine — de resultaten worden nog steeds strikt gefilterd op de goedgekeurde domeinen.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Goedgekeurde bronnen', 'Eén adres per regel: de website van een partner of fabrikant, de llms.txt daarvan of een gegevensbron (bijv. de catalogus van een marktplaats). Als de startpagina de vraag niet beantwoordt, volgt de assistent de passende subpagina’s. Voeg desgewenst trefwoorden toe — “adres | trefwoord1 trefwoord2” — zodat hij weet waarvoor een bron dient; bij API-adressen is dat nodig, omdat het adres dat niet verraadt. Deze lijst is ook het slot: NIETS buiten deze domeinen wordt opgehaald, zelfs niet als een bezoeker de assistent daarom vraagt.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Bronnen per vraag', 'Hoeveel bronnen de assistent per vraag maximaal leest. Meer bronnen = betere antwoorden, maar trager en duurder.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Altijd verwijzen naar', 'Voor inrichting, implementatie, koppeling, migratie, ondersteuning en aankoop verwijst de assistent ALTIJD naar deze aanbieder — nooit naar de fabrikant of ontwikkelaar van een oplossing van derden. Producten van derden mag hij noemen; het aanspreekpunt blijft altijd u. Leeg laten = geen verwijsregel.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Voorkeur voor eigen oplossing', 'Als deze oplossing bij de vraag past, noemt de assistent haar als eerste — eerlijk en alleen waar ze echt past. Leeg laten = geen voorkeur.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['Waarvoor de oplossing dient', 'Een of twee zinnen: wat kan ze, voor wie is ze bedoeld? Daarmee noemt de assistent haar alleen als ze echt past.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Formeel (u)', 'du' => 'Informeel (je/jij)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Kort (2–3 zinnen)', 'medium' => 'Gemiddeld (4–6 zinnen)', 'long' => 'Uitgebreid (met een opsomming waar dat helpt)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Professioneel & zakelijk', 'friendly' => 'Vriendelijk & hartelijk', 'expert' => 'Vakkundig & precies'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Alleen goedgekeurde bronnen lezen (gratis)', 'serpapi' => 'Zoeken via SerpAPI — beperkt tot de goedgekeurde domeinen'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 bron', '2' => '2 bronnen', '3' => '3 bronnen', '4' => '4 bronnen', '5' => '5 bronnen'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Toegang en licenties', 'AI-toegang, zoeken op internet en licentie van de SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Er is nog geen AI-sleutel opgeslagen. Tot dan toont de website de chat niet.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'De SerpAPI-sleutel is opgeslagen onder „Toegang en licenties”.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Er is nog geen SerpAPI-sleutel opgeslagen. Tot dan leest de assistent alleen de vrijgegeven bronnen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Openen';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Nu invullen';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Nog geen assistent heeft eigen teksten voor %1$s (startpagina „%2$s”); de chat toont daar de standaardteksten.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Met voorstellen aanmaken bij „%s”';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Veiligheid';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Geen medisch advies', 'De assistent geeft geen diagnoses, behandel-, medicatie- of gezondheidstips en weigert zulke vragen in eigen woorden, volgens de richtlijn in het taalrecord (bijv. het alarmnummer). Een regel voor een taalmodel sluit niet elke vergissing uit.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Privacymodus', 'De chat vraagt geen persoonsgegevens in te voeren. De server verwijdert e-mailadressen, telefoonnummers, IBAN’s, kaartnummers, burgerservicenummers en geboortedata uit het bericht voordat het naar de AI-dienst gaat; op gezondheidsgegevens en dergelijke let het model. Plaatsen, landen en adressen zonder naam gelden niet als persoonsgegevens (bijv. vragen over het weer). Functies die persoonsgegevens nodig hebben (bijv. boeken of een reservering opvragen) zijn dan niet mogelijk.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Contactpagina', 'Deze pagina kan de assistent openen als een bezoeker een persoon wil bereiken (Basic-licentie), naast het formulier in de chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Afbeelding', 'Verschijnt in het chatvenster in plaats van het robotpictogram (vierkant, bijv. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Positie', 'Hoek van het scherm voor chatknop en venster. Staan er knoppen van andere extensies (cookie-instellingen, toegankelijkheid) in dezelfde hoek, dan worden ze samen geordend zoals ingesteld in de pagina-layout: één instellingenknop met een menu, boven elkaar of naast elkaar.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Rechtsonder',
    'left' => 'Linksonder',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Denkinspanning', 'Hoe grondig het model nadenkt voordat het antwoordt. Meer denken = nauwkeuriger, maar trager en duurder. Voor een chat op een website is „Laag” meestal genoeg.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Uit', 'low' => 'Laag (aanbevolen)', 'medium' => 'Gemiddeld', 'high' => 'Hoog'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'kan nadenken';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'kan tools gebruiken';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'tot %s tokens per antwoord';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Dit model kan geen tools gebruiken. Zoeken op internet werkt er niet mee; navigatie en doorsturen lopen via vervangende regels.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Niet getest met tools van koppelingen. Getest: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Spraakinvoer';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Spraakinvoer toestaan', 'Bezoekers kunnen een bericht inspreken (hoogstens 60 seconden). De opname gaat via uw server voor transcriptie naar de AI-dienst, wordt niet bewaard, en de tekst verschijnt in het invoerveld om te controleren. Met de privacymodus aan alleen samen met „Spraakinvoer ook in de privacymodus”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Transcriptiemodel', 'Model voor de transcriptie, uit de lijst van de route (bij Requesty vrijgeven onder „Transcriptions”). In de EU verwerken bijv. de modellen van Mistral (Voxtral) en Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Zolang de privacymodus aan staat, is er geen spraakinvoer: een opname zou ongefilterd naar de transcriptiedienst gaan. Zet hieronder „Spraakinvoer ook in de privacymodus” aan om die toe te staan.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Spraakinvoer ook in de privacymodus', 'De microfoon blijft beschikbaar als de privacymodus aan staat. De opname gaat ongefilterd naar de transcriptiedienst; alleen de tekst wordt gefilterd: e-mailadressen, telefoonnummers en dergelijke worden verwijderd voordat hij in het invoerveld verschijnt en voordat er iets naar het chatmodel gaat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Werkt alleen met een e-mailadres voor doorsturen hierboven: zonder dat heeft de chat geen doorstuurformulier.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Publicatie';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Zichtbaar voor', 'Nieuwe assistenten staan eerst op ‘Alleen voorbeeld’: dan zien alleen ingelogde beheerders de chat, in het voorbeeld van de frontend. Test hem daar voordat u hem voor alle bezoekers zichtbaar maakt.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Alleen voorbeeld – alleen ingelogde beheerders zien de chat in het voorbeeld',
    'public' => 'Alle bezoekers',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '‘Alle bezoekers’ kan worden gekozen zodra de voorwaarden van de extensie zijn bevestigd.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Bevestig eerst de voorwaarden onder ‘Toegang en licenties’. Tot dan blijft de assistent in het voorbeeld.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Voorbeeld';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Voordat een assistent zichtbaar wordt voor bezoekers, moeten de voorwaarden zijn bevestigd.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'De functies van de editie %s zijn vergrendeld totdat de voorwaarden ervan zijn bevestigd.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Extra velden in het formulier', 'E-mailadres en bericht staan altijd in het formulier en zijn verplicht. Deze velden komen er op verzoek bij; wat bezoekers invullen gaat alleen per e-mail naar het doorstuuradres, nooit naar de AI.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Waarvan verplicht', 'Alleen velden die links zijn gekozen, kunnen verplicht zijn.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Naam',
    'phone' => 'Telefoon',
    'company' => 'Bedrijf',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Vakje nieuwsbrief', 'Biedt in het formulier ‘Abonneren op de nieuwsbrief’ aan, nooit vooraf aangevinkt. Kies een nieuwsbriefmodule van deze website; de lijsten of kanalen en de bevestigingsmail daarvan gelden (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Uit';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Contao-nieuwsbrief',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'De gekozen nieuwsbriefmodule bestaat niet meer. Tot er een andere is gekozen, toont het formulier geen vakje voor de nieuwsbrief.',
    'brevo_incomplete' => 'In de gekozen Brevo-module ontbreekt een lijst, een bevestigingssjabloon of een bevestigingspagina. Tot dan toont het formulier geen vakje voor de nieuwsbrief.',
    'brevo_key' => 'Er is nog geen Brevo-API-sleutel opgeslagen (“Toegang en licenties”). Tot dan toont het formulier geen vakje voor de nieuwsbrief.',
    'contao_channels' => 'In de gekozen module ‘Abonneren’ is geen kanaal gekozen. Tot dan toont het formulier geen vakje voor de nieuwsbrief.',
    'contao_page' => 'De gekozen module ‘Abonneren’ staat op geen enkele gepubliceerde pagina. De bevestigingslink leidt naar die pagina; zolang de module er niet staat, toont het formulier geen vakje voor de nieuwsbrief.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Er is nog geen nieuwsbriefmodule (Brevo of ‘Abonneren’ van Contao). Maak er een aan, dan kan die hier worden gekozen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] = 'Doorgestuurde aanvragen van %s kunnen niet worden verzonden: er ontbreekt een afzenderadres. Vul bij de startpagina het „E-mail adres van de websitebeheerder” in, of onder Systeem › Instellingen het „E-mail adres van de systeembeheerder”, of stel MAILER_FROM in op de server. Tot dan toont de chat een fout in plaats van te verzenden.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Geen toestemming nodig (chat direct beschikbaar)',
    'functional' => 'Functioneel',
    'statistics' => 'Statistieken',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (eigen categorie)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'zakelijk, altijd gelijk geformuleerd',
    '0.4' => 'standaard',
    '1' => 'afwisselend',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s tokens (ongeveer %2$s woorden)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Toestemming voor contact vragen', 'Toont een verplicht vakje in het formulier: de bezoeker gaat ermee akkoord dat zijn gegevens voor de vraag worden gebruikt en dat er contact met hem wordt opgenomen. De e-mail aan uw team vermeldt de toestemming.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Privacyverklaring', 'Pagina waarnaar het vakje linkt. Leeg = zonder link.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Vooraf gekozen landcode', 'Voor het telefoonveld. Leeg = volgens de taal van de pagina (bijv. Duits → Duitsland). Bezoekers kunnen elke landcode kiezen.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Volgens de taal van de pagina';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Verbindingen', 'MCP-servers verbinden, tools lezen en vergrendelen (alleen beheerders).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Logboek', 'Toolaanroepen van de laatste 30 dagen (alleen beheerders).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Tools van de verbindingen';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Tools', 'Wat deze assistent mag gebruiken. Standaard is niets aangevinkt, vergrendelde tools worden niet getoond. De assistent beslist zelf wanneer hij een tool aanroept.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'niet actief';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'vergrendeld';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Niet meer beschikbaar';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Er is nog geen verbinding met een MCP-server. Beheerders maken die aan via “Verbindingen” boven de lijst met assistenten.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Verbindingen openen';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Privacymodus aan: deze tools vragen om gegevens van een persoon (%s). De server verwijdert e-mailadressen en telefoonnummers uit de berichten en de assistent vraagt niet om persoonsgegevens, dus de tools krijgen deze gegevens niet.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Volgens de AI-dienst kan het gekozen model geen tools aanroepen. Kies een ander model, anders blijven de tools ongebruikt.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Tools van koppelingen (bijv. boekingen) hebben een sterk taalmodel nodig: het moet meerdere stappen plannen, gegevens uit het gesprek overnemen en tools na elkaar aanroepen. Zwakkere modellen vragen opnieuw, slaan stappen over of geven op. Getest met het boekingsproces van het sjabloon apaleo: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'Het gekozen model hoort erbij.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'Het gekozen model hoort er niet bij: controleer de processen van uw koppelingen in de chat voordat bezoekers ze gebruiken.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Aangevinkt maar niet bruikbaar: %d (vergrendeld, niet meer beschikbaar of verbinding niet actief). De chat gebruikt ze niet.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skills', 'Skills van GitHub of als bestand installeren (alleen beheerders).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Geïnstalleerde skills';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skills', 'Welke geïnstalleerde skills deze assistent mag gebruiken. Standaard is niets aangevinkt. Het model ziet ze in zijn catalogus en laadt zelf een skill wanneer een vraag binnen zijn gebied valt.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'De module ‘%s’ bevat geen actieve dienst in deze categorie, dus vraagt de banner bezoekers nooit om toestemming voor de chat: waar de banner verschijnt, blijft de chat onbeschikbaar; op pagina’s zonder banner start hij zonder te vragen. Voeg de dienst ‘AI-chatassistent’ in deze categorie toe of kies een andere categorie.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Dienst nu toevoegen';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Een beheerder kan hem toevoegen in de module ‘%s’.';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Talen en startvragen';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Een assistent heeft één taal: begroeting, startvragen, vragen en antwoorden en de teksten van het chatvenster staan in deze taal. Maak voor elke andere taal van de website een eigen assistent aan (kopiëren, taal wijzigen) en kies die in de chatmodule van die taal.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Nog geen taal: de chat toont de standaardteksten en geen startvragen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Geen startvragen';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Teksten en startvragen bewerken';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Taal toevoegen';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Antwoord:';
