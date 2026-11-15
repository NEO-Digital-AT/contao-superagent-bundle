<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Általános';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identitás';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Modell';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Rendszerutasítás (system prompt)';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Tudásbázis (grounding)';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Továbbítás és hozzájárulás';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Megnevezés', 'Az asszisztens belső neve (csak a háttérrendszerben).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Aktív', 'A weboldalon csak egy aktív asszisztens jelenik meg.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Megjelenített név', 'Így mutatkozik be az asszisztens, és így jelenik meg a csevegőablak fejlécében (pl. „János”). Minden nyelvre közös.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Modell', 'Az asszisztens nyelvi modellje. Az útvonal és a kulcs itt adható meg: „Hozzáférések és licencek” › MI; ha ott kulcs van tárolva, itt az útvonal modelljei közül lehet választani.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Hőmérséklet', 'Mennyire szabadon fogalmaz a modell: alacsonyabb = tárgyilagosabb és egyenletesebb. Csevegéshez 0,2–0,5 illik.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Válaszhossz (max. token)', 'A válasz legnagyobb hossza. Gondolkodó modelleknél hely adódik hozzá a gondolkodáshoz.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Rendszerutasítás', 'Viselkedési szabályok az asszisztens számára. A {name} helyére a megjelenített név kerül. Minden nyelvre közös.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Tudásforrás', 'Az ellenőrzött tények forrása, amelyekre az asszisztens támaszkodik. Minden nyelvre közös.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Tudásszöveg', 'Ellenőrzött tények szövegként, amelyek kontextusként a rendszerutasításhoz kapcsolódnak.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Tudásfájl', 'Egy fájl (txt/md/json) a fájlkezelőből, amely tudásbázisként szolgál.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['Továbbítási e-mail-cím', 'Erre a címre érkeznek a „továbbítás a csapatnak” kérések. Hagyja üresen, ha nincs ilyen – ekkor az asszisztens soha nem ajánl továbbítást, hanem a weboldal elérhetőségeire utal.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Továbbítás neki (név)', 'A csevegőgombokon jelenik meg, pl. „Továbbítás – címzett: János”. Hagyja üresen, ekkor „a csapat” szerepel.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Hozzájárulási kategória', 'A sütisáv azon kategóriája, amelynek elfogadása után megjelenik a csevegés. Az ugyanabból a sorozatból származó sütihozzájárulási bővítmény további beállítás nélkül működik; más hozzájárulási eszközhöz egy kis szkript kell (dokumentáció, „Customising”). Ha az oldalon nincs hozzájárulási eszköz, a csevegés azonnal elérhető.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Nyelvek', 'Az asszisztens nyelvi változatainak (üdvözlés, javaslatok, kérdések és válaszok …) kezelése.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Nincs',
    'text' => 'Szöveg (beírt)',
    'file' => 'Fájl a fájlkezelőből',
    'llms' => 'A weboldal llms.txt fájlja (ajánlott)',
    'llms_text' => 'llms.txt + saját kiegészítő szöveg',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Személyiség és válaszstílus';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Szakértői beállítások';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Megszólítás', 'Hogyan szólítsa meg az asszisztens a látogatókat?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Válaszok hossza', 'Mennyire legyenek részletesek a válaszok?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Hangnem', 'A válaszok alapvető hangneme.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Webes keresés';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Ajánlás — kihez irányítja a látogatókat';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Webes keresés engedélyezése', 'Az asszisztens maga döntheti el, hogy egy kérdésnél utánanéz-e valaminek a weben — de csak az alább jóváhagyott forrásokban. Enélkül csak a tudásbázisból válaszol.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Keresőszolgáltatás', 'A „Csak jóváhagyott források olvasása” közvetlenül lekéri a felsorolt címeket (ingyenes, ideális a gyártók llms.txt fájljaihoz). A „SerpAPI” ezen felül keresőmotorral is keres — az eredmények ekkor is szigorúan a jóváhagyott domainekre szűrődnek.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Jóváhagyott források', 'Soronként egy cím: partner- vagy gyártói oldal, annak llms.txt fájlja vagy egy adatvégpont (pl. egy piactér katalógusa). Ha a kezdőlap nem válaszolja meg a kérdést, az asszisztens a megfelelő aloldalakat követi. Opcionálisan kulcsszavakat is megadhat — „cím | kulcsszó1 kulcsszó2” —, hogy tudja, mire való egy forrás; API-címeknél erre szükség van, mert a címből nem derül ki. Ez a lista egyben a zár is: SEMMI nem kerül lekérésre ezeken a domaineken kívül, még akkor sem, ha egy látogató erre kéri az asszisztenst.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Források kérdésenként', 'Legfeljebb hány forrást olvas el az asszisztens egy kérdéshez. Több forrás = jobb válaszok, de lassabb és drágább.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Mindig ide irányít', 'Beállítás, megvalósítás, integráció, migráció, támogatás és vásárlás esetén az asszisztens MINDIG ehhez a szolgáltatóhoz irányít — soha nem egy külső megoldás gyártójához vagy fejlesztőjéhez. Megnevezhet külső termékeket; a kapcsolattartó azonban mindig Ön marad. Üres = nincs ajánlási szabály.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Előnyben részesített saját megoldás', 'Ha ez a megoldás illik a kérdéshez, az asszisztens elsőként ezt említi — őszintén, és csak ott, ahol valóban illik. Üres = nincs előnyben részesített megoldás.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['Mire való a megoldás', 'Egy-két mondat: mit tud, kinek szól? Az asszisztens ennek alapján csak akkor említi, ha valóban illik.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Magázó (formális)', 'du' => 'Tegező (közvetlen)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Rövid (2–3 mondat)', 'medium' => 'Közepes (4–6 mondat)', 'long' => 'Részletes (ha segít, listával)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Professzionális és tárgyszerű', 'friendly' => 'Barátságos és közvetlen', 'expert' => 'Szakmai és precíz'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Csak jóváhagyott források olvasása (ingyenes)', 'serpapi' => 'Keresés SerpAPI-val — a jóváhagyott domainekre korlátozva'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 forrás', '2' => '2 forrás', '3' => '3 forrás', '4' => '4 forrás', '5' => '5 forrás'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Hozzáférések és licencek', 'A SuperAgent MI-hozzáférése, internetes keresése és licence'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Még nincs MI-kulcs tárolva. Addig a weboldal nem jeleníti meg a csevegőt.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'A SerpAPI-kulcs itt van tárolva: „Hozzáférések és licencek”.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Még nincs SerpAPI-kulcs tárolva. Addig az asszisztens csak a jóváhagyott forrásokat olvassa.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Megnyitás';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Megadás most';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = '%1$s nyelvhez (kezdőoldal: „%2$s”) még egyetlen asszisztensnek sincsenek saját szövegei; a csevegő ott az alapértelmezett szövegeket mutatja.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Létrehozás javaslatokkal ehhez: „%s”';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Biztonság';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Nincs orvosi tanács', 'Az asszisztens nem ad diagnózist, kezelési, gyógyszer- vagy egészségügyi tanácsot, és az ilyen kérdéseket saját szavaival utasítja el a nyelvi rekord útmutatása szerint (pl. segélyhívó szám). Egy nyelvi modellnek adott szabály nem zár ki minden tévedést.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Adatvédelmi mód', 'A csevegő arra kéri a látogatókat, hogy ne adjanak meg személyes adatokat. A szerver eltávolítja az üzenetből az e-mail-címeket, telefonszámokat, IBAN-okat, kártyaszámokat, TAJ-számokat és születési dátumokat, mielőtt az az MI-szolgáltatáshoz kerülne; az egészségügyi és hasonló adatokra a modell ügyel. A helyek, országok és címek név nélkül nem számítanak személyes adatnak (pl. időjárással kapcsolatos kérdések). A személyes adatokat igénylő funkciók (pl. foglalás vagy egy foglalás lekérése) ekkor nem lehetségesek.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Kapcsolati oldal', 'Ezt az oldalt nyithatja meg az asszisztens, ha egy látogató személlyel szeretne kapcsolatba lépni (Basic licenc), a csevegőben lévő űrlap mellett.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Kép', 'A csevegőablakban a robot ikon helyett jelenik meg (négyzetes, pl. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Elhelyezés', 'A képernyő sarka a csevegőgombnak és -ablaknak. Ha ugyanabban a sarokban más bővítmények gombjai is vannak (sütibeállítások, akadálymentesség), az oldalelrendezés beállítása szerint együtt jelennek meg: egy beállítások gomb menüvel, egymás fölött vagy egymás mellett.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Jobbra lent',
    'left' => 'Balra lent',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Gondolkodási ráfordítás', 'Milyen alaposan gondolkodik a modell, mielőtt válaszol. Több gondolkodás = pontosabb, de lassabb és drágább. Weboldalon futó csevegéshez általában elég az „Alacsony”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Ki', 'low' => 'Alacsony (ajánlott)', 'medium' => 'Közepes', 'high' => 'Magas'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'tud gondolkodni';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'tud eszközöket használni';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'válaszonként legfeljebb %s token';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Ez a modell nem tud eszközöket használni. Az internetes keresés nem működik vele; a navigáció és a továbbítás helyettesítő sorokkal működik.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Kapcsolatok eszközeivel nincs tesztelve. Tesztelve: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Hangbevitel';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Hangbevitel engedélyezése', 'A látogatók bemondhatnak egy üzenetet (legfeljebb 60 másodperc). A felvétel az Ön szerverén keresztül az MI-szolgáltatáshoz kerül átírásra, nem tároljuk, a szöveg pedig ellenőrzésre megjelenik a beviteli mezőben. Bekapcsolt adatvédelmi módban csak a „Hangbevitel adatvédelmi módban is” beállítással együtt.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Átírási modell', 'Az átíráshoz használt modell az útvonal listájából (Requestyben a „Transcriptions” alatt kell jóváhagyni). Az EU-ban dolgoznak például a Mistral (Voxtral) és a Scaleway modelljei.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Amíg az adatvédelmi mód be van kapcsolva, nincs hangbevitel: a felvétel szűretlenül jutna el az átíró szolgáltatáshoz. Az engedélyezéshez kapcsolja be lent a „Hangbevitel adatvédelmi módban is” beállítást.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Hangbevitel adatvédelmi módban is', 'A mikrofon bekapcsolt adatvédelmi módban is elérhető marad. A felvétel szűretlenül jut el az átíró szolgáltatáshoz; csak a szöveget szűrjük: az e-mail-címeket, telefonszámokat és hasonló adatokat eltávolítjuk, mielőtt megjelenik a beviteli mezőben, és mielőtt bármi a csevegőmodellhez kerülne.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Csak a fenti továbbítási e-mail-címmel érvényes: nélküle a csevegőben nincs továbbítási űrlap.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Közzététel';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Látható', 'Az új asszisztensek először „Csak előnézet” állapotban vannak: ekkor csak a bejelentkezett adminisztrátorok látják a csevegőt, a frontend előnézetében. Tesztelje ott, mielőtt minden látogató számára láthatóvá teszi.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Csak előnézet – csak a bejelentkezett adminisztrátorok látják a csevegőt az előnézetben',
    'public' => 'Minden látogató',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = 'A „Minden látogató” akkor választható, amikor a bővítmény feltételei megerősítést nyertek.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Először erősítse meg a feltételeket a „Hozzáférések és licencek” oldalon. Addig az asszisztens előnézetben marad.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Előnézet';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Mielőtt egy asszisztens láthatóvá válik a látogatók számára, a feltételeket meg kell erősíteni.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'A(z) %s kiadás funkciói zárolva vannak, amíg a feltételeit meg nem erősítik.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['További mezők az űrlapon', 'Az e-mail-cím és az üzenet mindig szerepel az űrlapon, és kötelező. Ezek a mezők igény szerint kerülnek hozzá; a megadott adatok csak e-mailben jutnak a továbbítási címre, soha nem az MI-hez.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Ebből kötelező', 'Csak a bal oldalon kiválasztott mezők lehetnek kötelezők.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Név',
    'phone' => 'Telefon',
    'company' => 'Cég',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Hírlevél jelölőnégyzet', 'Az űrlapon felkínálja a „Feliratkozás a hírlevélre” lehetőséget, soha nincs előre bejelölve. A weboldal egyik hírlevélmodulját kell választani; annak listái vagy csatornái és megerősítő e-mailje érvényes (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Ki';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Contao hírlevél',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'A kiválasztott hírlevélmodul már nem létezik. Amíg nem választanak másikat, az űrlap nem mutat hírlevél jelölőnégyzetet.',
    'brevo_incomplete' => 'A kiválasztott Brevo-modulból hiányzik a lista, a megerősítő sablon vagy a megerősítő oldal. Addig az űrlap nem mutat hírlevél jelölőnégyzetet.',
    'brevo_key' => 'Még nincs Brevo API-kulcs tárolva („Hozzáférések és licencek”). Addig az űrlap nem mutat hírlevél jelölőnégyzetet.',
    'contao_channels' => 'A kiválasztott „Feliratkozás” modulban nincs csatorna kiválasztva. Addig az űrlap nem mutat hírlevél jelölőnégyzetet.',
    'contao_page' => 'A kiválasztott „Feliratkozás” modul egyetlen közzétett oldalon sincs. A megerősítő hivatkozás erre az oldalra vezet; amíg a modul nincs ott, az űrlap nem mutat hírlevél jelölőnégyzetet.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Még nincs hírlevélmodul (Brevo vagy a Contao „Feliratkozás” modulja). Hozzon létre egyet, utána itt kiválasztható.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Nincs szükség hozzájárulásra (a csevegés azonnal elérhető)',
    'functional' => 'Funkcionális',
    'statistics' => 'Statisztika',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (saját kategória)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'tárgyilagos, mindig azonos',
    '0.4' => 'alapértelmezett',
    '1' => 'változatos',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s token (kb. %2$s szó)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Hozzájárulás kérése a kapcsolatfelvételhez', 'Kötelező jelölőnégyzetet mutat az űrlapon: a látogató hozzájárul, hogy adatait a kéréshez felhasználják, és felvegyék vele a kapcsolatot. A csapatnak szóló e-mail rögzíti a hozzájárulást.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Adatvédelmi tájékoztató', 'Az oldal, amelyre a jelölőnégyzet hivatkozik. Üres = hivatkozás nélkül.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Előre kiválasztott országhívó-szám', 'A telefonmezőhöz. Üres = az oldal nyelve szerint (pl. német → Németország). A látogatók bármely hívószámot választhatják.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Az oldal nyelve szerint';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Kapcsolatok', 'MCP-szerverek csatlakoztatása, eszközeik beolvasása és zárolása (csak rendszergazdák).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Napló', 'Az elmúlt 30 nap eszközhívásai (csak rendszergazdák).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'A kapcsolatok eszközei';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Eszközök', 'Amit ez az asszisztens használhat. Alapértelmezés szerint semmi sincs bejelölve, a zárolt eszközök nem jelennek meg. Hogy mikor hív meg egy eszközt, azt az asszisztens maga dönti el.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'nem aktív';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'zárolva';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Már nem elérhető';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Még nincs kapcsolat MCP-szerverrel. A rendszergazdák az asszisztensek listája feletti „Kapcsolatok” gombbal hozhatják létre.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Kapcsolatok megnyitása';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Az adatvédelmi mód be van kapcsolva: ezek az eszközök egy személy adatait kérik (%s). A szerver eltávolítja az üzenetekből az e-mail-címeket és a telefonszámokat, az asszisztens pedig nem kér személyes adatokat, így az eszközök nem kapják meg ezeket az adatokat.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Az MI-szolgáltatás szerint a választott modell nem tud eszközöket hívni. Válasszon másik modellt, különben az eszközök kihasználatlanok maradnak.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'A kapcsolatok eszközeihez (pl. foglalásokhoz) erős nyelvi modell kell: több lépést kell megterveznie, át kell vennie az adatokat a beszélgetésből, és egymás után kell hívnia az eszközöket. A gyengébb modellek újra rákérdeznek, kihagynak lépéseket vagy feladják. Az apaleo sablon foglalási folyamatával tesztelve: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'A választott modell köztük van.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'A választott modell nincs köztük: próbálja ki a kapcsolatai folyamatait a csevegésben, mielőtt a látogatók használnák őket.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Bejelölve, de nem használható: %d (zárolva, már nem elérhető vagy a kapcsolat nem aktív). A csevegés nem használja őket.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skillek', 'Skillek telepítése GitHubról vagy fájlként (csak rendszergazdák).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Telepített skillek';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skillek', 'Mely telepített skilleket használhatja ez az asszisztens. Alapértelmezés szerint semmi sincs bejelölve. A modell a katalógusában látja őket, és magától tölt be egy skillt, ha egy kérdés annak területére esik.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'A(z) „%s” modulban nincs aktív szolgáltatás ebben a kategóriában, így a sáv soha nem kér hozzájárulást a csevegéshez: ahol a sáv megjelenik, a csevegés nem érhető el; a sáv nélküli oldalakon kérdés nélkül indul. Vegye fel az „MI-csevegőasszisztens” szolgáltatást ebben a kategóriában, vagy válasszon másik kategóriát.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Szolgáltatás felvétele most';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Egy rendszergazda felveheti a(z) „%s” modulban.';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Nyelvek és kezdő kérdések';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Egy asszisztensnek egy nyelve van: az üdvözlés, a kezdő kérdések, a kérdések és válaszok és a csevegőablak szövegei ezen a nyelven vannak. A webhely minden további nyelvéhez hozzon létre külön asszisztenst (másolja le, módosítsa a nyelvet), és válassza ki az adott nyelv csevegőmoduljában.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Még nincs nyelv: a csevegés az alapértelmezett szövegeket mutatja, kezdő kérdések nélkül.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Nincsenek kezdő kérdések';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Szövegek és kezdő kérdések szerkesztése';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Nyelv hozzáadása';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Válasz:';
