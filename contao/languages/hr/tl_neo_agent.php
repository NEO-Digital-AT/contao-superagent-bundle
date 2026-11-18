<?php

declare(strict_types=1);

// Legende
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Općenito';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identitet';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Model';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Sistemske upute (system prompt)';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Baza znanja (grounding)';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Prosljeđivanje i privola';

// Polja
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Naziv', 'Interni naziv ovog asistenta (samo u back endu).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Aktivan', 'Na web-stranici se prikazuje samo jedan aktivni asistent.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Prikazano ime', 'Kako se asistent predstavlja i kako se prikazuje u zaglavlju chata (npr. „Ivan”). Isto za sve jezike.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Model', 'Jezični model ovog asistenta. Ruta i ključ unose se pod „Pristupi i licence” › AI; čim je ondje spremljen ključ, ovdje se nude modeli rute.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Temperatura', 'Koliko slobodno model oblikuje odgovore: niže = činjeničnije i ujednačenije. Za chat odgovara 0,2 do 0,5.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Duljina odgovora (maks. tokena)', 'Najdulji odgovor. Kod modela koji razmišljaju dodaje se prostor za razmišljanje.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Sistemske upute', 'Pravila ponašanja asistenta. {name} se zamjenjuje prikazanim imenom. Isto za sve jezike.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Izvor znanja', 'Izvor provjerenih činjenica na koje se asistent oslanja. Isto za sve jezike.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Tekst znanja', 'Provjerene činjenice kao tekst, dodane sistemskim uputama kao kontekst.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Datoteka znanja', 'Datoteka (txt/md/json) iz upravitelja datoteka koja služi kao baza znanja.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['E-mail za prosljeđivanje', 'Adresa na koju se šalju zahtjevi „proslijedi timu”. Ostavite prazno ako ga nema – asistent tada nikada ne nudi prosljeđivanje i upućuje na mogućnosti kontakta na web-stranici.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Proslijedi (ime)', 'Prikazuje se na gumbima chata, npr. „Proslijedi Ivanu”. Upišite ime u dativu (komu?). Ostavite prazno za „timu”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Kategorija privole', 'Kategorija bannera za kolačiće nakon čije privole se pojavljuje chat. Proširenje za privolu za kolačiće iz iste serije radi bez dodatnog podešavanja; drugi alat za privolu treba mali skript (dokumentacija, „Customising”). Bez alata za privolu na stranici chat je odmah dostupan.'];

// Operacije
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Jezici', 'Upravljanje jezičnim inačicama (pozdrav, prijedlozi, pitanja i odgovori …) ovog asistenta.'];

// Referenca (oznake opcija)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Bez izvora',
    'text' => 'Tekst (upisani)',
    'file' => 'Datoteka iz upravitelja datoteka',
    'llms' => 'Datoteka llms.txt web-stranice (preporučeno)',
    'llms_text' => 'llms.txt + vlastiti dodatni tekst',
];

// Osobnost, pretraživanje weba, upućivanje
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Osobnost i stil odgovora';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Napredne postavke';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Oblik obraćanja', 'Kako se asistent treba obraćati posjetiteljima?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Duljina odgovora', 'Koliko detaljni trebaju biti odgovori?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Ton', 'Osnovni ton odgovora.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Pretraživanje weba';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Upućivanje — kome se posjetitelji upućuju';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Dopusti pretraživanje weba', 'Asistent može sam odlučiti da za neko pitanje potraži informacije na webu — ali samo u izvorima odobrenima u nastavku. Bez ove opcije odgovara samo iz baze znanja.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Usluga pretraživanja', '„Čitaj samo odobrene izvore” izravno dohvaća navedene adrese (besplatno, idealno za llms.txt proizvođača). „SerpAPI” dodatno pretražuje putem tražilice — rezultati se i dalje strogo ograničavaju na odobrene domene.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Odobreni izvori', 'Jedna adresa po retku: stranica partnera ili proizvođača, njezin llms.txt ili podatkovna točka (npr. katalog tržnice). Ako početna stranica ne odgovara na pitanje, asistent slijedi odgovarajuće podstranice. Po želji dodajte ključne riječi — „adresa | pojam1 pojam2” — kako bi znao čemu izvor služi; za API adrese to je potrebno jer sama adresa to ne otkriva. Ovaj popis ujedno je i brava: NIŠTA izvan ovih domena ne dohvaća se, čak ni ako to posjetitelj zatraži od asistenta.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Izvora po pitanju', 'Koliko izvora asistent najviše čita za jedno pitanje. Više izvora = bolji odgovori, ali sporije i skuplje.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Uvijek uputi na', 'Za postavljanje, uvođenje, integraciju, migraciju, podršku i kupnju asistent UVIJEK upućuje na ovog pružatelja — nikada na proizvođača ili razvijatelja rješenja treće strane. Smije spominjati proizvode trećih strana; kontakt uvijek ostajete vi. Prazno = bez pravila upućivanja.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Preferirano vlastito rješenje', 'Ako ovo rješenje odgovara pitanju, asistent ga navodi prvo — iskreno i samo kad doista odgovara. Prazno = bez preferencije.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['Čemu rješenje služi', 'Jedna ili dvije rečenice: što može i za koga je namijenjeno? Asistent ga na temelju toga spominje samo kad doista odgovara.'];

// Opcije (ključ = pohranjena vrijednost)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Službeno (persiranje, „vi”)', 'du' => 'Neslužbeno (na „ti”)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Kratko (2–3 rečenice)', 'medium' => 'Srednje (4–6 rečenica)', 'long' => 'Detaljno (s popisom kad pomaže)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Profesionalno i činjenično', 'friendly' => 'Prijateljski i toplo', 'expert' => 'Stručno i precizno'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Čitaj samo odobrene izvore (besplatno)', 'serpapi' => 'Pretraživanje putem SerpAPI-ja — ograničeno na odobrene domene'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 izvor', '2' => '2 izvora', '3' => '3 izvora', '4' => '4 izvora', '5' => '5 izvora'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Pristupi i licence', 'AI pristup, pretraživanje interneta i licenca SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'AI ključ još nije spremljen. Do tada web-stranica ne prikazuje chat.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'SerpAPI ključ spremljen je pod „Pristupi i licence”.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'SerpAPI ključ još nije spremljen. Do tada asistent čita samo odobrene izvore.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Otvori';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Unesite sada';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Nijedan asistent još nema vlastite tekstove za %1$s (početna stranica „%2$s”); chat ondje prikazuje zadane tekstove.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Izradi s prijedlozima za „%s”';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Sigurnost';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Bez medicinskih savjeta', 'Asistent ne postavlja dijagnoze niti daje savjete o liječenju, lijekovima ili zdravlju te takva pitanja odbija vlastitim riječima prema uputi u jezičnom zapisu (npr. broj za hitne slučajeve). Pravilo za jezični model ne isključuje svaku pogrešku.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Način privatnosti', 'Chat moli da se ne unose osobni podaci. Poslužitelj iz poruke uklanja adrese e-pošte, telefonske brojeve, IBAN, brojeve kartica, brojeve socijalnog osiguranja i datume rođenja prije nego što ode AI usluzi; na zdravstvene i slične podatke pazi model. Mjesta, zemlje i adrese bez imena ne smatraju se osobnim podacima (npr. pitanja o vremenu). Funkcije kojima trebaju osobni podaci (npr. rezervacija ili pregled rezervacije) tada nisu moguće.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Kontaktna stranica', 'Ovu stranicu asistent može otvoriti kad posjetitelj želi doći do osobe (licenca Basic), uz obrazac u chatu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Slika', 'Prikazuje se u prozoru chata umjesto ikone robota (kvadratna, npr. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Položaj', 'Kut zaslona za gumb i prozor chata. Ako su u istom kutu gumbi drugih proširenja (postavke kolačića, pristupačnost), raspoređuju se zajedno kako je postavljeno u izgledu stranice: jedan gumb postavki s izbornikom, jedan iznad drugoga ili jedan pokraj drugoga.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Dolje desno',
    'left' => 'Dolje lijevo',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Razina razmišljanja', 'Koliko temeljito model razmišlja prije odgovora. Više razmišljanja = točnije, ali sporije i skuplje. Za chat na web-stranici obično je dovoljno „Niska”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Isključeno', 'low' => 'Niska (preporučeno)', 'medium' => 'Srednja', 'high' => 'Visoka'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'može razmišljati';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'može koristiti alate';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'do %s tokena po odgovoru';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Ovaj model ne može koristiti alate. Pretraživanje interneta s njim ne radi; navigacija i prosljeđivanje rade preko zamjenskih redaka.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Nije testirano s alatima veza. Testirano: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Glasovni unos';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Dopusti glasovni unos', 'Posjetitelji mogu izgovoriti poruku (najviše 60 sekundi). Snimka ide preko vašeg poslužitelja AI usluzi na prijepis, ne sprema se, a tekst se pojavljuje u polju za unos radi provjere. Uz uključen način privatnosti samo zajedno s postavkom „Glasovni unos i u načinu privatnosti”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Model za prijepis', 'Model za prijepis s popisa rute (kod Requestyja odobrite ga pod „Transcriptions”). U EU-u obrađuju npr. modeli Mistral (Voxtral) i Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Dok je uključen način privatnosti, nema glasovnog unosa: snimka bi nefiltrirana otišla usluzi za prijepis. Da biste ga dopustili, ispod uključite „Glasovni unos i u načinu privatnosti”.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Glasovni unos i u načinu privatnosti', 'Mikrofon ostaje dostupan i uz uključen način privatnosti. Snimka ide usluzi za prijepis nefiltrirana; filtrira se tek tekst: adrese e-pošte, telefonski brojevi i slični podaci uklanjaju se prije nego što se pojavi u polju za unos i prije nego što išta stigne do modela chata.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Vrijedi samo uz adresu e-pošte za prosljeđivanje iznad: bez nje chat nema obrazac za prosljeđivanje.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Objava';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Vidljivo za', 'Novi asistenti najprije su u načinu „Samo pregled”: tada chat vide samo prijavljeni administratori, u pregledu web-stranice. Ondje ga testirajte prije nego što ga omogućite svim posjetiteljima.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Samo pregled – chat vide samo prijavljeni administratori u pregledu',
    'public' => 'Svi posjetitelji',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '„Svi posjetitelji” može se odabrati čim se potvrde uvjeti proširenja.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Najprije potvrdite uvjete pod „Pristupi i licence”. Do tada asistent ostaje u pregledu.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Pregled';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Prije nego što asistent postane vidljiv posjetiteljima, uvjeti moraju biti potvrđeni.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'Funkcije izdanja %s zaključane su dok se njegovi uvjeti ne potvrde.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Dodatna polja obrasca', 'Adresa e-pošte i poruka uvijek su u obrascu i obvezne su. Ova se polja dodaju po želji; uneseni podaci idu samo e-poštom na adresu za prosljeđivanje, nikad UI-ju.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Od toga obvezna', 'Obvezna mogu biti samo polja odabrana lijevo.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Ime',
    'phone' => 'Telefon',
    'company' => 'Tvrtka',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Okvir za newsletter', 'U obrascu nudi „Pretplatite se na newsletter”, nikad unaprijed označeno. Odabire se modul newslettera ove web-stranice; vrijede njegovi popisi ili kanali i njegova e-poruka za potvrdu (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Isključeno';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Contao newsletter',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'Odabrani modul newslettera više ne postoji. Dok se ne odabere drugi, obrazac ne prikazuje okvir za newsletter.',
    'brevo_incomplete' => 'U odabranom Brevo modulu nedostaje popis, predložak za potvrdu ili stranica za potvrdu. Do tada obrazac ne prikazuje okvir za newsletter.',
    'brevo_key' => 'API ključ za Brevo još nije spremljen („Pristupi i licence”). Do tada obrazac ne prikazuje okvir za newsletter.',
    'contao_channels' => 'U odabranom modulu „Pretplata” nije odabran nijedan kanal. Do tada obrazac ne prikazuje okvir za newsletter.',
    'contao_page' => 'Odabrani modul „Pretplata” nije ni na jednoj objavljenoj stranici. Poveznica za potvrdu vodi na tu stranicu; dok modul nije ondje, obrazac ne prikazuje okvir za newsletter.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Još ne postoji modul newslettera (Brevo ili „Pretplata” iz Contaa). Izradite ga, zatim ga ovdje možete odabrati.';
$GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] = 'Proslijeđeni upiti s %s ne mogu se poslati: nedostaje adresa pošiljatelja. Na početnoj stranici unesite e-mail adresu administratora web-stranice ili pod Sustav › Postavke e-mail adresu administratora sustava, ili na poslužitelju postavite MAILER_FROM. Do tada chat umjesto slanja prikazuje pogrešku.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Privola nije potrebna (chat odmah dostupan)',
    'functional' => 'Funkcionalno',
    'statistics' => 'Statistika',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (vlastita kategorija)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'činjenično, uvijek jednako',
    '0.4' => 'zadano',
    '1' => 'raznoliko',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s tokena (oko %2$s riječi)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Traži privolu za kontakt', 'U obrascu prikazuje obvezan okvir: posjetitelj pristaje da se njegovi podaci koriste za upit i da ga kontaktirate. E-poruka vašem timu bilježi privolu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Izjava o zaštiti privatnosti', 'Stranica na koju okvir vodi. Prazno = bez poveznice.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Unaprijed odabrani pozivni broj', 'Za polje telefona. Prazno = prema jeziku stranice (npr. njemački → Njemačka). Posjetitelji mogu odabrati bilo koji pozivni broj.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Prema jeziku stranice';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Veze', 'Poveži MCP poslužitelje, pročitaj i zaključaj njihove alate (samo administratori).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Zapisnik', 'Pozivi alata u posljednjih 30 dana (samo administratori).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Alati veza';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Alati', 'Što ovaj asistent smije koristiti. Zadano ništa nije označeno, zaključani alati se ne prikazuju. Kada će pozvati alat, odlučuje asistent sam.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'nije aktivna';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'zaključano';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Više nije dostupno';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Još nema veze s MCP poslužiteljem. Administratori je izrađuju putem „Veze” iznad popisa asistenata.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Otvori veze';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Način zaštite podataka je uključen: ovi alati traže podatke o osobi (%s). Poslužitelj iz poruka uklanja adrese e-pošte i telefonske brojeve, a asistent ne traži osobne podatke, pa alati te podatke ne dobivaju.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Prema usluzi UI odabrani model ne može pozivati alate. Odaberite drugi model, inače alati ostaju neiskorišteni.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Alati veza (npr. rezervacije) trebaju snažan jezični model: mora planirati više koraka, preuzeti podatke iz razgovora i pozivati alate jedan za drugim. Slabiji modeli ponovno pitaju, preskaču korake ili odustaju. Testirano na postupku rezervacije predloška apaleo: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'Odabrani model je među njima.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'Odabrani model nije među njima: provjerite postupke svojih veza u chatu prije nego što ih posjetitelji koriste.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Označeno, ali neupotrebljivo: %d (zaključano, više nije dostupno ili veza nije aktivna). Chat ih ne koristi.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skillovi', 'Instaliraj skillove s GitHuba ili kao datoteku (samo administratori).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Instalirani skillovi';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skillovi', 'Koje instalirane skillove ovaj asistent smije koristiti. Zadano ništa nije označeno. Model ih vidi u svom katalogu i sam učitava skill kada pitanje spada u njegovo područje.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'Modul „%s” nema aktivnu uslugu u ovoj kategoriji pa banner posjetitelje nikad ne pita za privolu za chat: gdje se banner pojavi, chat ostaje nedostupan; na stranicama bez bannera pokreće se bez pitanja. Dodajte uslugu „Chat-asistent s UI-jem” u ovoj kategoriji ili odaberite drugu kategoriju.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Dodaj uslugu sada';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Administrator je može dodati u modulu „%s”.';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Jezici i početna pitanja';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Asistent ima jedan jezik: pozdrav, početna pitanja, pitanja i odgovori te tekstovi prozora chata su na tom jeziku. Za svaki daljnji jezik web-stranice izradite zasebnog asistenta (kopirajte ga i promijenite jezik) i odaberite ga u modulu chata tog jezika.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Još nema jezika: chat prikazuje zadane tekstove i nema početnih pitanja.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Nema početnih pitanja';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Uredi tekstove i početna pitanja';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Dodaj jezik';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Odgovor:';
