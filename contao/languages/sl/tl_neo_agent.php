<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Splošno';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identiteta';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Model';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Sistemski poziv';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Baza znanja (podlaga za odgovore)';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Posredovanje in privolitev';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Oznaka', 'Interno ime tega asistenta (vidno samo v zaledju).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Aktiven', 'Na spletni strani je prikazan le en aktiven asistent.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Prikazno ime', 'Kako se asistent predstavi in kako je prikazan v glavi klepeta (npr. „Janez“). Velja za vse jezike.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Model', 'Jezikovni model tega asistenta. Pot in ključ vnesete v »Dostopi in licence« › UI; ko je tam shranjen ključ, so tukaj na voljo modeli poti.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Temperatura', 'Kako svobodno model oblikuje odgovore: nižje = bolj stvarno in enakomerno. Za klepet ustreza 0,2 do 0,5.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Dolžina odgovora (najv. žetonov)', 'Najdaljši odgovor. Pri modelih, ki razmišljajo, se doda prostor za razmišljanje.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Sistemski poziv', 'Pravila vedenja za asistenta. {name} se nadomesti s prikaznim imenom. Velja za vse jezike.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Vir znanja', 'Vir preverjenih dejstev, na katera se asistent opira. Velja za vse jezike.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Besedilo znanja', 'Preverjena dejstva kot besedilo, ki se kot kontekst doda sistemskemu pozivu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Datoteka znanja', 'Datoteka (txt/md/json) iz upravitelja datotek, ki služi kot baza znanja.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['E-pošta za posredovanje', 'Naslov, na katerega se pošiljajo zahteve „posreduj ekipi“. Pustite prazno, če ga ni – asistent potem nikoli ne ponudi posredovanja in napoti na kontaktne možnosti spletne strani.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Posreduj (ime)', 'Prikazano na gumbih klepeta, npr. „Posreduj Janezu“. Pustite prazno za „ekipi“.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Kategorija privolitve', 'Kategorija pasice za piškotke, po privolitvi katere se prikaže klepet. Razširitev za privolitev v piškotke iz iste serije deluje brez dodatnih nastavitev; drugo orodje za privolitev potrebuje majhen skript (dokumentacija, »Customising«). Brez orodja za privolitev na strani je klepet takoj na voljo.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Jeziki', 'Upravljanje jezikovnih različic (pozdrav, predlogi, vprašanja in odgovori …) tega asistenta.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Brez',
    'text' => 'Besedilo (vneseno)',
    'file' => 'Datoteka iz upravitelja datotek',
    'llms' => 'Datoteka llms.txt spletnega mesta (priporočeno)',
    'llms_text' => 'llms.txt + dodatno lastno besedilo',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Osebnost in slog odgovorov';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Napredne nastavitve';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Način nagovora', 'Kako naj asistent nagovarja obiskovalce?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Dolžina odgovorov', 'Kako podrobni naj bodo odgovori?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Ton', 'Osnovni ton odgovorov.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Spletno iskanje';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Napotitev – na koga se obiskovalci napotijo';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Dovoli spletno iskanje', 'Asistent se lahko sam odloči, da za vprašanje nekaj poišče na spletu – vendar le v spodaj odobrenih virih. Brez tega odgovarja samo iz baze znanja.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Iskalna storitev', '„Beri samo odobrene vire“ neposredno prebere navedene naslove (brezplačno, idealno za llms.txt proizvajalcev). „SerpAPI“ dodatno išče prek iskalnika – rezultati so še vedno strogo omejeni na odobrene domene.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Odobreni viri', 'En naslov na vrstico: spletno mesto partnerja ali proizvajalca, njegova datoteka llms.txt ali podatkovna točka (npr. katalog tržnice). Če začetna stran ne odgovori na vprašanje, asistent sledi ustreznim podstranem. Po želji dodajte ključne besede – „naslov | ključna_beseda1 ključna_beseda2“ –, da ve, čemu je vir namenjen; pri naslovih API je to nujno, ker naslov tega ne pove. Ta seznam je hkrati tudi zapora: NIČ zunaj teh domen se ne prenese, tudi če obiskovalec asistenta za to prosi.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Viri na vprašanje', 'Največ koliko virov asistent prebere za eno vprašanje. Več virov = boljši odgovori, a počasneje in dražje.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Vedno napoti na', 'Za postavitev, uvedbo, integracijo, selitev, podporo in nakup asistent VEDNO napoti na tega ponudnika – nikoli na proizvajalca ali razvijalca rešitve tretje osebe. Izdelke tretjih oseb lahko omeni; kontaktna oseba ostanete vedno vi. Prazno = brez pravila za napotitev.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Prednostna lastna rešitev', 'Če ta rešitev ustreza vprašanju, jo asistent omeni prvo – pošteno in le tam, kjer res ustreza. Prazno = brez prednosti.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['Čemu je rešitev namenjena', 'Ena ali dve povedi: kaj zmore, komu je namenjena? Asistent na podlagi tega rešitev omeni le, ko res ustreza.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Vikanje (formalno)', 'du' => 'Tikanje (neformalno)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Kratko (2–3 povedi)', 'medium' => 'Srednje (4–6 povedi)', 'long' => 'Podrobno (s seznamom, kadar pomaga)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Strokovno in stvarno', 'friendly' => 'Prijazno in toplo', 'expert' => 'Tehnično in natančno'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Beri samo odobrene vire (brezplačno)', 'serpapi' => 'Iskanje prek SerpAPI – omejeno na odobrene domene'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 vir', '2' => '2 vira', '3' => '3 viri', '4' => '4 viri', '5' => '5 virov'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Dostopi in licence', 'Dostop UI, iskanje po spletu in licenca SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Ključ UI še ni shranjen. Do takrat spletno mesto klepeta ne prikazuje.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'Ključ SerpAPI je shranjen v »Dostopi in licence«.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Ključ SerpAPI še ni shranjen. Do takrat asistent bere le odobrene vire.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Odpri';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Vnesite zdaj';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Noben asistent še nima lastnih besedil za %1$s (začetna stran »%2$s«); klepet tam prikazuje privzeta besedila.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Ustvari s predlogi za »%s«';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Varnost';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Brez zdravstvenih nasvetov', 'Asistent ne postavlja diagnoz in ne daje nasvetov o zdravljenju, zdravilih ali zdravju ter taka vprašanja odkloni s svojimi besedami po navodilu v jezikovnem zapisu (npr. številka za klic v sili). Pravilo za jezikovni model ne izključi vsake napake.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Način zasebnosti', 'Klepet prosi, naj obiskovalci ne vnašajo osebnih podatkov. Strežnik iz sporočila odstrani e-poštne naslove, telefonske številke, IBAN, številke kartic, številke socialnega zavarovanja in datume rojstva, preden gre do storitve UI; na zdravstvene podatke in podobno pazi model. Kraji, države in naslovi brez imena ne štejejo za osebne podatke (npr. vprašanja o vremenu). Funkcije, ki potrebujejo osebne podatke (npr. rezervacija ali vpogled v rezervacijo), takrat niso mogoče.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Kontaktna stran', 'To stran lahko asistent odpre, ko obiskovalec želi doseči osebo (licenca Basic), poleg obrazca v klepetu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Slika', 'Prikaže se v oknu klepeta namesto ikone robota (kvadratna, npr. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Položaj', 'Kot zaslona za gumb in okno klepeta. Če so v istem kotu gumbi drugih razširitev (nastavitve piškotkov, dostopnost), so razporejeni skupaj, kot je nastavljeno v postavitvi strani: en gumb za nastavitve z menijem, drug nad drugim ali drug ob drugem.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Spodaj desno',
    'left' => 'Spodaj levo',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Obseg razmisleka', 'Kako temeljito model razmisli, preden odgovori. Več razmisleka = natančneje, a počasneje in dražje. Za klepet na spletnem mestu običajno zadošča »Nizek«.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Izklopljeno', 'low' => 'Nizek (priporočeno)', 'medium' => 'Srednji', 'high' => 'Visok'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'zna razmišljati';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'zna uporabljati orodja';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'do %s žetonov na odgovor';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Ta model ne zna uporabljati orodij. Iskanje po spletu z njim ne deluje; navigacija in posredovanje delujeta prek nadomestnih vrstic.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Ni preizkušeno z orodji povezav. Preizkušeno: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Glasovni vnos';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Dovoli glasovni vnos', 'Obiskovalci lahko sporočilo narekujejo (največ 60 sekund). Posnetek gre prek vašega strežnika do storitve UI za prepis, se ne shrani, besedilo pa se prikaže v vnosnem polju za preverjanje. Pri vklopljenem načinu zasebnosti le skupaj z nastavitvijo »Glasovni vnos tudi v načinu zasebnosti«.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Model za prepis', 'Model za prepis s seznama poti (pri Requesty ga odobrite pod »Transcriptions«). V EU obdelujejo npr. modeli Mistral (Voxtral) in Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Dokler je vklopljen način zasebnosti, glasovnega vnosa ni: posnetek bi šel do storitve za prepis nefiltriran. Za dovoljenje spodaj vklopite »Glasovni vnos tudi v načinu zasebnosti«.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Glasovni vnos tudi v načinu zasebnosti', 'Mikrofon ostane na voljo tudi pri vklopljenem načinu zasebnosti. Posnetek gre do storitve za prepis nefiltriran; filtrira se šele besedilo: e-poštni naslovi, telefonske številke in podobni podatki se odstranijo, preden se prikaže v vnosnem polju in preden kar koli pride do modela klepeta.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Velja le z e-poštnim naslovom za posredovanje zgoraj: brez njega klepet nima obrazca za posredovanje.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Objava';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Vidno za', 'Novi pomočniki so najprej v načinu »Samo predogled«: takrat klepet vidijo samo prijavljeni skrbniki, v predogledu spletnega mesta. Tam ga preizkusite, preden ga omogočite vsem obiskovalcem.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Samo predogled – klepet vidijo samo prijavljeni skrbniki v predogledu',
    'public' => 'Vsi obiskovalci',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '»Vsi obiskovalci« lahko izberete, ko so pogoji razširitve potrjeni.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Najprej potrdite pogoje pod »Dostopi in licence«. Do takrat ostane pomočnik v predogledu.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Predogled';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Preden pomočnik postane viden obiskovalcem, morajo biti pogoji potrjeni.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'Funkcije izdaje %s so zaklenjene, dokler njeni pogoji niso potrjeni.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Dodatna polja obrazca', 'E-poštni naslov in sporočilo sta v obrazcu vedno in sta obvezna. Ta polja se dodajo po želji; vneseni podatki gredo le po e-pošti na naslov za posredovanje, nikoli k UI.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Od tega obvezna', 'Obvezna so lahko le polja, izbrana na levi.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Ime',
    'phone' => 'Telefon',
    'company' => 'Podjetje',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Potrditveno polje za novice', 'V obrazcu ponudi »Naroči se na novice«, nikoli vnaprej označeno. Izbere se modul za novice tega spletnega mesta; veljajo njegovi seznami ali kanali in njegovo potrditveno sporočilo (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Izklopljeno';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Novice Contao',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'Izbrani modul za novice ne obstaja več. Dokler ni izbran drug, obrazec ne prikaže polja za novice.',
    'brevo_incomplete' => 'V izbranem modulu Brevo manjka seznam, potrditvena predloga ali potrditvena stran. Do takrat obrazec ne prikaže polja za novice.',
    'brevo_key' => 'Ključ API za Brevo še ni shranjen („Dostopi in licence“). Do takrat obrazec ne prikaže polja za novice.',
    'contao_channels' => 'V izbranem modulu »Naročnina« ni izbran noben kanal. Do takrat obrazec ne prikaže polja za novice.',
    'contao_page' => 'Izbrani modul »Naročnina« ni na nobeni objavljeni strani. Potrditvena povezava vodi na to stran; dokler modula ni tam, obrazec ne prikaže polja za novice.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Modula za novice še ni (Brevo ali »Naročnina« iz Contaa). Ustvarite ga, nato ga lahko izberete tukaj.';
$GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] = 'Posredovanih vprašanj s/z %s ni mogoče poslati: manjka naslov pošiljatelja. Pri korenski strani vnesite »Naslov elektronske pošte administratorja spletnega mesta« ali pod Sistem › Nastavitve »Administratorjev naslov elektronske pošte« ali na strežniku nastavite MAILER_FROM. Do takrat klepet namesto pošiljanja prikaže napako.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Privolitev ni potrebna (klepet takoj na voljo)',
    'functional' => 'Funkcionalno',
    'statistics' => 'Statistika',
    'marketing' => 'Trženje',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (lastna kategorija)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'stvarno, vedno enako',
    '0.4' => 'privzeto',
    '1' => 'raznoliko',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s žetonov (približno %2$s besed)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Zahtevaj privolitev za stik', 'V obrazcu prikaže obvezno potrditveno polje: obiskovalec se strinja, da se njegovi podatki uporabijo za vprašanje in da ga kontaktirate. E-pošta vaši ekipi zabeleži privolitev.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Politika zasebnosti', 'Stran, na katero povezuje polje. Prazno = brez povezave.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Vnaprej izbrana klicna številka', 'Za polje telefona. Prazno = glede na jezik strani (npr. nemščina → Nemčija). Obiskovalci lahko izberejo katero koli klicno številko.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Glede na jezik strani';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Povezave', 'Poveži strežnike MCP, preberi in zakleni njihova orodja (samo skrbniki).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Dnevnik', 'Klici orodij v zadnjih 30 dneh (samo skrbniki).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Orodja povezav';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Orodja', 'Kaj sme ta asistent uporabljati. Privzeto ni nič označeno, zaklenjena orodja niso prikazana. Kdaj pokliče orodje, odloči asistent sam.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'ni aktivna';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'zaklenjeno';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Ni več na voljo';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Povezave s strežnikom MCP še ni. Skrbniki jo ustvarijo z gumbom »Povezave« nad seznamom asistentov.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Odpri povezave';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Način varstva podatkov je vklopljen: ta orodja zahtevajo podatke o osebi (%s). Strežnik iz sporočil odstrani e-poštne naslove in telefonske številke, asistent pa ne sprašuje po osebnih podatkih, zato orodja teh podatkov ne dobijo.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Po navedbah storitve UI izbrani model ne zna klicati orodij. Izberite drug model, sicer orodja ostanejo neuporabljena.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Orodja povezav (npr. rezervacije) potrebujejo zmogljiv jezikovni model: načrtovati mora več korakov, prevzeti podatke iz pogovora in klicati orodja enega za drugim. Šibkejši modeli znova sprašujejo, preskočijo korake ali obupajo. Preizkušeno s postopkom rezervacije predloge apaleo: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'Izbrani model je med njimi.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'Izbranega modela ni med njimi: preizkusite postopke svojih povezav v klepetu, preden jih uporabijo obiskovalci.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Označeno, a neuporabno: %d (zaklenjeno, ni več na voljo ali povezava ni aktivna). Klepet jih ne uporablja.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skilli', 'Namesti skille z GitHuba ali kot datoteko (samo skrbniki).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Nameščeni skilli';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skilli', 'Katere nameščene skille sme ta asistent uporabljati. Privzeto ni nič označeno. Model jih vidi v svojem katalogu in sam naloži skill, ko vprašanje spada na njegovo področje.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'Modul »%s« v tej kategoriji nima aktivne storitve, zato pasica obiskovalcev nikoli ne vpraša za privolitev za klepet: kjer se pasica prikaže, klepet ni na voljo; na straneh brez pasice se zažene brez vprašanja. Dodajte storitev »Klepetalni pomočnik z UI« v tej kategoriji ali izberite drugo kategorijo.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Dodaj storitev zdaj';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Skrbnik jo lahko doda v modulu »%s«.';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Jeziki in začetna vprašanja';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Asistent ima en jezik: pozdrav, začetna vprašanja, vprašanja in odgovori ter besedila okna klepeta so v tem jeziku. Za vsak nadaljnji jezik spletnega mesta ustvarite svojega asistenta (kopirajte ga in spremenite jezik) in ga izberite v modulu klepeta tega jezika.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Še ni jezika: klepet prikazuje privzeta besedila in nobenih začetnih vprašanj.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Ni začetnih vprašanj';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Uredi besedila in začetna vprašanja';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Dodaj jezik';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Odgovor:';
