<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Obecné';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identita';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Model';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Systémový prompt';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Znalostní báze (grounding)';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Předání týmu a souhlas';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Označení', 'Interní název tohoto asistenta (jen v administraci).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Aktivní', 'Na webu se zobrazuje jen jeden aktivní asistent.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Zobrazované jméno', 'Jak se asistent představuje a jak je uveden v záhlaví chatu (např. „Jan“). Platí pro všechny jazyky.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Model', 'Jazykový model tohoto asistenta. Trasa a klíč se zadávají v „Přístupy a licence“ › AI; jakmile je tam uložen klíč, nabízejí se zde modely trasy.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Teplota', 'Jak volně model formuluje: nižší = věcnější a stálejší. Pro chat se hodí 0,2 až 0,5.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Délka odpovědi (max. tokenů)', 'Nejdelší odpověď. U modelů, které přemýšlejí, se přidá místo na přemýšlení.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Systémový prompt', 'Pravidla chování asistenta. {name} se nahradí zobrazovaným jménem. Platí pro všechny jazyky.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Zdroj znalostí', 'Zdroj ověřených faktů, ze kterých asistent vychází. Platí pro všechny jazyky.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Znalostní text', 'Ověřená fakta jako text; připojí se k systémovému promptu jako kontext.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Znalostní soubor', 'Soubor (txt/md/json) ze správce souborů, který slouží jako znalostní báze.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['E-mail pro předání', 'Adresa, na kterou se posílají žádosti „předat týmu“. Pokud žádná není, nechte prázdné – asistent pak předání nikdy nenabídne a odkáže na kontaktní možnosti webu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Předat komu (jméno)', 'Zobrazí se na tlačítkách chatu, např. „Předat Janovi“ — zadejte jméno ve 3. pádě (komu). Prázdné = „týmu“.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Kategorie souhlasu', 'Kategorie cookie lišty, po jejímž odsouhlasení se chat zobrazí. Rozšíření pro souhlas s cookies ze stejné řady funguje bez dalšího nastavení; jiný nástroj pro souhlas potřebuje malý skript (dokumentace, „Customising“). Bez nástroje pro souhlas na stránce je chat hned dostupný.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Jazyky', 'Správa jazykových verzí (pozdrav, návrhy, otázky a odpovědi …) tohoto asistenta.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Žádný',
    'text' => 'Text (zadaný ručně)',
    'file' => 'Soubor ze správce souborů',
    'llms' => 'llms.txt tohoto webu (doporučeno)',
    'llms_text' => 'llms.txt + vlastní doplňující text',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Osobnost a styl odpovědí';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Pokročilá nastavení';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Oslovení', 'Jak má asistent oslovovat návštěvníky?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Délka odpovědí', 'Jak podrobné mají být odpovědi?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Tón', 'Základní tón odpovědí.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Vyhledávání na webu';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Doporučení — na koho asistent návštěvníky odkazuje';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Povolit vyhledávání na webu', 'Asistent se může sám rozhodnout, že k otázce něco dohledá na webu — ale jen ve zdrojích schválených níže. Bez této volby odpovídá pouze ze znalostní báze.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Vyhledávací služba', '„Číst jen schválené zdroje“ načítá uvedené adresy přímo (zdarma, ideální pro llms.txt výrobců). „SerpAPI“ navíc hledá přes vyhledávač — výsledky se i tak přísně omezují na schválené domény.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Schválené zdroje', 'Jedna adresa na řádek: web partnera nebo výrobce, jeho llms.txt nebo datové rozhraní (např. katalog tržiště). Pokud úvodní stránka na otázku neodpoví, asistent projde odpovídající podstránky. Volitelně doplňte klíčová slova — „adresa | slovo1 slovo2“ — aby věděl, k čemu zdroj slouží; u adres API je to nutné, protože z adresy to poznat nejde. Tento seznam je zároveň zámek: NIC mimo tyto domény se nenačte, ani když o to návštěvník asistenta požádá.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Zdrojů na otázku', 'Kolik zdrojů asistent k jedné otázce nejvýše přečte. Více zdrojů = lepší odpovědi, ale pomalejší a dražší.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Vždy odkazovat na', 'U nastavení, realizace, integrace, migrace, podpory a nákupu asistent VŽDY odkazuje na tohoto poskytovatele — nikdy na výrobce nebo vývojáře řešení třetí strany. Produkty třetích stran zmínit smí; kontaktní osobou ale zůstáváte vždy vy. Prázdné = žádné pravidlo pro doporučení.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Preferované vlastní řešení', 'Pokud se toto řešení k otázce hodí, asistent ho uvede jako první — poctivě a jen tam, kde opravdu sedí. Prázdné = žádná preference.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['K čemu řešení slouží', 'Jedna až dvě věty: co umí a pro koho je? Podle toho ho asistent zmíní jen tehdy, když se opravdu hodí.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Vykání', 'du' => 'Tykání'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Krátké (2–3 věty)', 'medium' => 'Střední (4–6 vět)', 'long' => 'Podrobné (se seznamem, když pomůže)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Profesionální a věcný', 'friendly' => 'Přátelský a vlídný', 'expert' => 'Odborný a přesný'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Číst jen schválené zdroje (zdarma)', 'serpapi' => 'Hledat přes SerpAPI — omezeno na schválené domény'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 zdroj', '2' => '2 zdroje', '3' => '3 zdroje', '4' => '4 zdroje', '5' => '5 zdrojů'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Přístupy a licence', 'Přístup AI, vyhledávání na internetu a licence SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Zatím není uložen klíč AI. Do té doby web chat nezobrazuje.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'Klíč SerpAPI je uložen v části „Přístupy a licence“.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Zatím není uložen klíč SerpAPI. Do té doby asistent čte jen povolené zdroje.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Otevřít';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Zadat nyní';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Pro %1$s (výchozí stránka „%2$s“) zatím žádný asistent nemá vlastní texty; chat tam zobrazuje výchozí texty.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Vytvořit s návrhy pro „%s“';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Bezpečnost';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Bez zdravotních rad', 'Asistent nestanovuje diagnózy, nedává rady k léčbě, lékům ani zdraví a takové dotazy odmítne vlastními slovy podle pokynu v jazykovém záznamu (např. číslo tísňového volání). Pravidlo pro jazykový model nevyloučí každou chybu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Režim ochrany údajů', 'Chat žádá, aby návštěvníci nezadávali osobní údaje. Server ze zprávy odstraní e-mailové adresy, telefonní čísla, IBAN, čísla karet, čísla sociálního pojištění a data narození dříve, než odejde službě AI; na zdravotní a podobné údaje dbá model. Místa, země a adresy bez jména nejsou osobní údaje (např. dotazy na počasí). Funkce, které osobní údaje potřebují (např. rezervace nebo vyhledání rezervace), pak nejsou možné.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Kontaktní stránka', 'Tuto stránku může asistent otevřít, když chce návštěvník kontaktovat osobu (licence Basic), vedle formuláře v chatu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Obrázek', 'Zobrazí se v okně chatu místo ikony robota (čtvercový, např. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Umístění', 'Roh obrazovky pro tlačítko a okno chatu. Pokud jsou ve stejném rohu tlačítka jiných rozšíření (nastavení cookies, přístupnost), uspořádají se společně podle nastavení v rozvržení stránky: jedno tlačítko nastavení s nabídkou, nad sebou nebo vedle sebe.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Vpravo dole',
    'left' => 'Vlevo dole',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Míra uvažování', 'Jak důkladně model před odpovědí přemýšlí. Více uvažování = přesnější, ale pomalejší a dražší. Pro chat na webu obvykle stačí „Nízká“.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Vypnuto', 'low' => 'Nízká (doporučeno)', 'medium' => 'Střední', 'high' => 'Vysoká'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'umí uvažovat';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'umí používat nástroje';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'až %s tokenů na odpověď';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Tento model neumí používat nástroje. Vyhledávání na internetu s ním nefunguje; navigace a předání fungují přes náhradní řádky.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Netestováno s nástroji připojení. Otestováno: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Hlasové zadávání';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Povolit hlasové zadávání', 'Návštěvníci mohou zprávu namluvit (nejvýše 60 sekund). Nahrávka jde přes váš server k přepisu do služby AI, neukládá se a text se objeví ve vstupním poli ke kontrole. Při zapnutém režimu ochrany údajů jen spolu s volbou „Hlasové zadávání i v režimu ochrany údajů“.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Model přepisu', 'Model pro přepis ze seznamu trasy (v Requesty schválit v části „Transcriptions“). V EU zpracovávají např. modely Mistral (Voxtral) a Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Dokud je zapnutý režim ochrany údajů, hlasové zadávání není: nahrávka by šla k přepisu nefiltrovaná. Chcete-li ho povolit, zapněte níže „Hlasové zadávání i v režimu ochrany údajů“.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Hlasové zadávání i v režimu ochrany údajů', 'Mikrofon zůstane dostupný i při zapnutém režimu ochrany údajů. Nahrávka jde ke službě přepisu nefiltrovaná; filtruje se až text: e-mailové adresy, telefonní čísla a podobné údaje se odstraní dřív, než se objeví ve vstupním poli a než se cokoli dostane k modelu chatu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Platí jen s e-mailovou adresou pro předávání výše: bez ní chat nemá formulář pro předání.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Zveřejnění';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Viditelné pro', 'Noví asistenti začínají v režimu „Jen náhled“: chat pak vidí jen přihlášení administrátoři, v náhledu frontendu. Otestujte ho tam, než ho zpřístupníte všem návštěvníkům.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Jen náhled – chat vidí jen přihlášení administrátoři v náhledu',
    'public' => 'Všichni návštěvníci',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '„Všichni návštěvníci“ lze zvolit, jakmile budou podmínky rozšíření potvrzeny.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Nejprve prosím potvrďte podmínky v části „Přístupy a licence“. Do té doby zůstává asistent v náhledu.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Náhled';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Než bude asistent viditelný pro návštěvníky, musí být podmínky potvrzeny.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'Funkce edice %s jsou zamčené, dokud nebudou potvrzeny její podmínky.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Další pole formuláře', 'E-mailová adresa a zpráva jsou ve formuláři vždy a jsou povinné. Tato pole se přidají na přání; zadané údaje jdou jen e-mailem na adresu pro předání, nikdy k AI.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Z toho povinná', 'Povinná mohou být jen pole zvolená vlevo.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Jméno',
    'phone' => 'Telefon',
    'company' => 'Firma',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Políčko newsletteru', 'Nabídne ve formuláři „Odebírat newsletter“, nikdy předem zaškrtnuté. Vybere se modul newsletteru tohoto webu; platí jeho seznamy nebo kanály a jeho potvrzovací e-mail (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Vypnuto';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Newsletter Contao',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'Zvolený modul newsletteru už neexistuje. Dokud nebude zvolen jiný, formulář políčko newsletteru nezobrazí.',
    'brevo_incomplete' => 'Ve zvoleném modulu Brevo chybí seznam, potvrzovací šablona nebo potvrzovací stránka. Do té doby formulář políčko newsletteru nezobrazí.',
    'brevo_key' => 'Zatím není uložen klíč API pro Brevo („Přístupy a licence“). Do té doby formulář políčko newsletteru nezobrazí.',
    'contao_channels' => 'Ve zvoleném modulu „Přihlášení k odběru“ není zvolen žádný kanál. Do té doby formulář políčko newsletteru nezobrazí.',
    'contao_page' => 'Zvolený modul „Přihlášení k odběru“ není na žádné zveřejněné stránce. Potvrzovací odkaz vede na tuto stránku; dokud tam modul není, formulář políčko newsletteru nezobrazí.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Zatím neexistuje žádný modul newsletteru (Brevo nebo „Přihlášení k odběru“ z Contaa). Vytvořte ho, pak ho zde půjde zvolit.';
$GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] = 'Přeposlané dotazy z %s nelze odeslat: chybí adresa odesílatele. U kořenové stránky zadejte „E-mailovou adresu administrátora webu“, v nabídce Systém › Nastavení „E-mailovou adresu administrátora systému“, nebo na serveru nastavte MAILER_FROM. Do té doby chat místo odeslání zobrazí chybu.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Souhlas není potřeba (chat hned dostupný)',
    'functional' => 'Funkční',
    'statistics' => 'Statistika',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (vlastní kategorie)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'věcně, vždy stejně',
    '0.4' => 'výchozí',
    '1' => 'rozmanitě',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s tokenů (asi %2$s slov)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Vyžadovat souhlas s kontaktováním', 'Zobrazí ve formuláři povinné políčko: návštěvník souhlasí s použitím údajů pro dotaz a s kontaktováním. E-mail vašemu týmu souhlas uvede.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Zásady ochrany osobních údajů', 'Stránka, na kterou políčko odkazuje. Prázdné = bez odkazu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Předvybraná předvolba', 'Pro pole telefonu. Prázdné = podle jazyka stránky (např. němčina → Německo). Návštěvníci mohou zvolit libovolnou předvolbu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Podle jazyka stránky';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Připojení', 'Připojit servery MCP, načíst a blokovat jejich nástroje (jen správci).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Protokol', 'Volání nástrojů za posledních 30 dní (jen správci).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Nástroje připojení';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Nástroje', 'Co smí tento asistent používat. Ve výchozím stavu není nic zaškrtnuto, zablokované nástroje se nezobrazují. Kdy nástroj zavolá, rozhoduje asistent sám.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'neaktivní';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'zablokováno';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Již není k dispozici';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Zatím neexistuje žádné připojení k serveru MCP. Správci ho založí přes „Připojení“ nad seznamem asistentů.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Otevřít připojení';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Režim ochrany osobních údajů je zapnutý: tyto nástroje vyžadují údaje o osobě (%s). Server ze zpráv odstraňuje e-mailové adresy a telefonní čísla a asistent se na osobní údaje neptá, takže nástroje tyto údaje nedostanou.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Podle služby AI zvolený model neumí volat nástroje. Zvolte jiný model, jinak zůstanou nástroje nevyužité.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Nástroje připojení (např. rezervace) potřebují silný jazykový model: musí naplánovat několik kroků, převzít údaje z konverzace a volat nástroje jeden po druhém. Slabší modely se ptají znovu, vynechávají kroky nebo to vzdají. Otestováno na rezervačním postupu šablony apaleo: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'Zvolený model mezi ně patří.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'Zvolený model mezi ně nepatří: vyzkoušejte postupy svých připojení v chatu dříve, než je použijí návštěvníci.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Zaškrtnuto, ale nepoužitelné: %d (zablokováno, již není k dispozici nebo připojení neaktivní). Chat je nepoužívá.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skilly', 'Instalovat skilly z GitHubu nebo jako soubor (jen správci).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Nainstalované skilly';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skilly', 'Které nainstalované skilly smí tento asistent používat. Ve výchozím stavu není nic zaškrtnuto. Model je vidí ve svém katalogu a skill si načte sám, když otázka spadá do jeho oblasti.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'Modul „%s“ nemá v této kategorii žádnou aktivní službu, takže se lišta návštěvníků nikdy nezeptá na souhlas s chatem: kde se lišta zobrazí, zůstane chat nedostupný; na stránkách bez lišty se spustí bez dotazu. Přidejte službu „AI chatovací asistent“ v této kategorii nebo zvolte jinou kategorii.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Přidat službu nyní';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Správce ji může přidat v modulu „%s“.';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Jazyky a úvodní otázky';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Asistent má jeden jazyk: pozdrav, úvodní otázky, otázky a odpovědi a texty okna chatu jsou v tomto jazyce. Pro každý další jazyk webu vytvořte vlastního asistenta (zkopírujte ho a změňte jazyk) a vyberte ho v modulu chatu daného jazyka.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Zatím žádný jazyk: chat zobrazuje výchozí texty a žádné úvodní otázky.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Žádné úvodní otázky';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Upravit texty a úvodní otázky';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Přidat jazyk';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Odpověď:';
