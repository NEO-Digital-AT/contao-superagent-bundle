<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Ogólne';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Tożsamość';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Model';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'Prompt systemowy';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Baza wiedzy';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Przekazanie do zespołu i zgoda';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Nazwa', 'Wewnętrzna nazwa tego asystenta (widoczna tylko w panelu administracyjnym).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Aktywny', 'Na stronie wyświetlany jest tylko jeden aktywny asystent.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Nazwa wyświetlana', 'Jak asystent się przedstawia i jak jest podpisany w nagłówku czatu (np. „Jan”). Wspólna dla wszystkich języków.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Model', 'Model językowy tego asystenta. Trasę i klucz wprowadza się w „Dostępy i licencje” › AI; gdy zapisano tam klucz, modele trasy są tu dostępne do wyboru.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Temperatura', 'Jak swobodnie model formułuje odpowiedzi: niżej = rzeczowo i jednolicie. Dla czatu pasuje 0,2–0,5.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Długość odpowiedzi (maks. tokenów)', 'Maksymalna długość odpowiedzi. Przy modelach, które myślą, dochodzi miejsce na myślenie.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Prompt systemowy', 'Zasady zachowania asystenta. {name} zostanie zastąpione nazwą wyświetlaną. Wspólny dla wszystkich języków.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Źródło wiedzy', 'Źródło sprawdzonych informacji, na których opiera się asystent. Wspólne dla wszystkich języków.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Tekst bazy wiedzy', 'Sprawdzone informacje w formie tekstu, dołączane do promptu systemowego jako kontekst.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Plik bazy wiedzy', 'Plik (txt/md/json) z menedżera plików, używany jako baza wiedzy.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['E-mail do przekazywania', 'Adres, na który trafiają prośby „przekaż do zespołu”. Pozostawić puste, jeśli go nie ma – asystent nigdy nie zaproponuje wtedy przekazania i odeśle do możliwości kontaktu na stronie.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Przekaż do (nazwa)', 'Wyświetlane na przyciskach czatu, np. „Przekaż do Jana” (nazwę należy wpisać w dopełniaczu, np. „Jana”). Puste = „zespołu”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Kategoria zgody', 'Kategoria banera cookies, po której zaakceptowaniu pojawia się czat. Rozszerzenie zgody na cookies z tej samej serii działa bez dodatkowej konfiguracji; inne narzędzie zgody wymaga małego skryptu (dokumentacja, „Customising”). Bez narzędzia zgody na stronie czat jest od razu dostępny.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Języki', 'Zarządzanie wersjami językowymi (powitanie, propozycje, pytania i odpowiedzi …) tego asystenta.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Brak',
    'text' => 'Tekst (wpisany)',
    'file' => 'Plik z menedżera plików',
    'llms' => 'Plik llms.txt tej witryny (zalecane)',
    'llms_text' => 'llms.txt + własny dodatkowy tekst',
];

// Personality, web search, referral
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Osobowość i styl odpowiedzi';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Ustawienia zaawansowane';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Forma zwracania się', 'Jak asystent ma zwracać się do odwiedzających?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Długość odpowiedzi', 'Jak szczegółowe mają być odpowiedzi?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Ton', 'Podstawowy ton odpowiedzi.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Wyszukiwanie w internecie';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Polecanie — do kogo kierowani są odwiedzający';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Zezwól na wyszukiwanie w internecie', 'Asystent może sam zdecydować, że przy danym pytaniu coś sprawdzi w internecie — ale wyłącznie w źródłach zatwierdzonych poniżej. Bez tej opcji odpowiada tylko na podstawie bazy wiedzy.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Usługa wyszukiwania', '„Tylko zatwierdzone źródła” pobiera podane adresy bezpośrednio (bezpłatnie, idealne dla plików llms.txt producentów). „SerpAPI” dodatkowo przeszukuje internet przez wyszukiwarkę — wyniki są jednak nadal ściśle ograniczone do zatwierdzonych domen.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Zatwierdzone źródła', 'Jeden adres na wiersz: strona partnera lub producenta, jej llms.txt albo źródło danych (np. katalog marketplace). Jeśli strona startowa nie odpowiada na pytanie, asystent przechodzi do pasujących podstron. Opcjonalnie można dodać słowa kluczowe — „adres | słowo1 słowo2” — aby asystent wiedział, do czego służy dane źródło; w przypadku adresów API jest to konieczne, bo sam adres tego nie zdradza. Ta lista działa też jak blokada: NIC spoza tych domen nie jest pobierane, nawet jeśli odwiedzający o to poprosi.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Źródła na pytanie', 'Ile źródeł asystent czyta maksymalnie przy jednym pytaniu. Więcej źródeł = lepsze odpowiedzi, ale wolniej i drożej.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Zawsze polecaj', 'W sprawach konfiguracji, wdrożenia, integracji, migracji, wsparcia i zakupu asystent ZAWSZE kieruje do tego usługodawcy — nigdy do producenta czy twórcy rozwiązania firmy trzeciej. Może wymieniać produkty innych firm; osobą kontaktową zawsze pozostają Państwo. Puste = brak reguły polecania.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Preferowane własne rozwiązanie', 'Jeśli to rozwiązanie pasuje do pytania, asystent wymienia je jako pierwsze — uczciwie i tylko tam, gdzie naprawdę pasuje. Puste = brak preferencji.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['Do czego służy rozwiązanie', 'Jedno lub dwa zdania: co potrafi, dla kogo jest przeznaczone? Dzięki temu asystent wspomina o nim tylko wtedy, gdy naprawdę pasuje.'];

// Options (key = stored value)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Formalnie (Pan/Pani)', 'du' => 'Nieformalnie (na „ty”)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Krótko (2–3 zdania)', 'medium' => 'Średnio (4–6 zdań)', 'long' => 'Szczegółowo (z listą, jeśli to pomaga)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Profesjonalnie i rzeczowo', 'friendly' => 'Przyjaźnie i ciepło', 'expert' => 'Fachowo i precyzyjnie'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Tylko zatwierdzone źródła (bezpłatnie)', 'serpapi' => 'Wyszukiwanie przez SerpAPI — ograniczone do zatwierdzonych domen'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 źródło', '2' => '2 źródła', '3' => '3 źródła', '4' => '4 źródła', '5' => '5 źródeł'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Dostępy i licencje', 'Dostęp AI, wyszukiwanie w internecie i licencja SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Nie zapisano jeszcze klucza AI. Do tego czasu strona nie wyświetla czatu.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'Klucz SerpAPI jest zapisany w „Dostępy i licencje”.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Nie zapisano jeszcze klucza SerpAPI. Do tego czasu asystent czyta tylko dozwolone źródła.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Otwórz';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Wprowadź teraz';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Żaden asystent nie ma jeszcze własnych tekstów dla %1$s (strona startowa „%2$s”); czat pokazuje tam teksty domyślne.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Utwórz z propozycjami dla „%s”';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Bezpieczeństwo';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Bez porad medycznych', 'Asystent nie stawia diagnoz, nie udziela porad dotyczących leczenia, leków ani zdrowia i odmawia takich pytań własnymi słowami, zgodnie ze wskazówką w rekordzie języka (np. numer alarmowy). Reguła dla modelu językowego nie wyklucza każdego błędu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Tryb prywatności', 'Czat prosi, aby nie wpisywać danych osobowych. Serwer usuwa z wiadomości adresy e-mail, numery telefonów, IBAN, numery kart, numery ubezpieczenia i daty urodzenia, zanim trafi ona do usługi AI; na dane o zdrowiu i podobne zwraca uwagę model. Miejsca, kraje i adresy bez nazwiska nie są danymi osobowymi (np. pytania o pogodę). Funkcje wymagające danych osobowych (np. rezerwacja lub sprawdzenie rezerwacji) nie są wtedy możliwe.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Strona kontaktowa', 'Asystent może otworzyć tę stronę, gdy odwiedzający chce skontaktować się z osobą (licencja Basic), oprócz formularza w czacie.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Obraz', 'Wyświetlany w oknie czatu zamiast ikony robota (kwadratowy, np. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Położenie', 'Róg ekranu dla przycisku i okna czatu. Jeśli w tym samym rogu są przyciski innych rozszerzeń (ustawienia plików cookie, dostępność), są one ułożone razem zgodnie z ustawieniem w układzie strony: jeden przycisk ustawień z menu, jeden nad drugim lub obok siebie.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Na dole po prawej',
    'left' => 'Na dole po lewej',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Nakład rozumowania', 'Jak dokładnie model zastanawia się przed odpowiedzią. Więcej rozumowania = dokładniej, ale wolniej i drożej. Do czatu na stronie zwykle wystarcza „Niski”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Wyłączony', 'low' => 'Niski (zalecany)', 'medium' => 'Średni', 'high' => 'Wysoki'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'potrafi rozumować';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'potrafi używać narzędzi';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'do %s tokenów na odpowiedź';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Ten model nie potrafi używać narzędzi. Wyszukiwanie w internecie nie działa; nawigacja i przekazywanie działają przez linie zastępcze.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Nieprzetestowany z narzędziami połączeń. Przetestowane: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Wprowadzanie głosowe';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Zezwól na wprowadzanie głosowe', 'Odwiedzający mogą nagrać wiadomość (najwyżej 60 sekund). Nagranie trafia przez Państwa serwer do usługi AI w celu transkrypcji, nie jest zapisywane, a tekst pojawia się w polu wpisywania do sprawdzenia. Przy włączonym trybie prywatności tylko razem z opcją „Wprowadzanie głosowe także w trybie prywatności”.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Model transkrypcji', 'Model do transkrypcji z listy trasy (w Requesty do zatwierdzenia w „Transcriptions”). W UE przetwarzają np. modele Mistral (Voxtral) i Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Dopóki tryb prywatności jest włączony, wprowadzanie głosowe nie jest dostępne: nagranie trafiłoby do usługi transkrypcji bez filtrowania. Aby na nie zezwolić, proszę włączyć poniżej „Wprowadzanie głosowe także w trybie prywatności”.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Wprowadzanie głosowe także w trybie prywatności', 'Mikrofon pozostaje dostępny przy włączonym trybie prywatności. Nagranie trafia do usługi transkrypcji bez filtrowania; filtrowany jest dopiero tekst: adresy e-mail, numery telefonów i podobne dane są usuwane, zanim pojawi się w polu wpisywania i zanim cokolwiek trafi do modelu czatu.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Działa tylko z adresem e-mail do przekazywania powyżej: bez niego czat nie ma formularza przekazania.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Publikacja';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Widoczny dla', 'Nowi asystenci zaczynają w trybie „Tylko podgląd”: wtedy czat widzą tylko zalogowani administratorzy, w podglądzie frontendu. Prosimy przetestować go tam, zanim stanie się widoczny dla wszystkich odwiedzających.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Tylko podgląd – czat widzą tylko zalogowani administratorzy w podglądzie',
    'public' => 'Wszyscy odwiedzający',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '„Wszyscy odwiedzający” można wybrać, gdy tylko warunki rozszerzenia zostaną potwierdzone.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Prosimy najpierw potwierdzić warunki w „Dostępy i licencje”. Do tego czasu asystent pozostaje w podglądzie.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Podgląd';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Zanim asystent stanie się widoczny dla odwiedzających, warunki muszą zostać potwierdzone.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'Funkcje edycji %s są zablokowane, dopóki jej warunki nie zostaną potwierdzone.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Dodatkowe pola formularza', 'Adres e-mail i wiadomość są zawsze w formularzu i są obowiązkowe. Te pola dodaje się na życzenie; wpisane dane trafiają tylko e-mailem na adres przekazywania, nigdy do AI.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['W tym obowiązkowe', 'Obowiązkowe mogą być tylko pola wybrane po lewej.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Imię i nazwisko',
    'phone' => 'Telefon',
    'company' => 'Firma',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Pole newslettera', 'Oferuje w formularzu „Zapisz się do newslettera”, nigdy nie zaznaczone z góry. Wybiera się moduł newslettera tej strony; obowiązują jego listy lub kanały i e-mail potwierdzający (double opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Wyłączone';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Newsletter Contao',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'Wybrany moduł newslettera już nie istnieje. Dopóki nie zostanie wybrany inny, formularz nie pokazuje pola newslettera.',
    'brevo_incomplete' => 'W wybranym module Brevo brakuje listy, szablonu potwierdzenia lub strony potwierdzenia. Do tego czasu formularz nie pokazuje pola newslettera.',
    'brevo_key' => 'Nie zapisano jeszcze klucza API Brevo („Dostępy i licencje”). Do tego czasu formularz nie pokazuje pola newslettera.',
    'contao_channels' => 'W wybranym module „Subskrypcja” nie wybrano kanału. Do tego czasu formularz nie pokazuje pola newslettera.',
    'contao_page' => 'Wybrany moduł „Subskrypcja” nie znajduje się na żadnej opublikowanej stronie. Link potwierdzający prowadzi do tej strony; dopóki moduł tam nie stoi, formularz nie pokazuje pola newslettera.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Nie ma jeszcze modułu newslettera (Brevo lub „Subskrypcja” Contao). Po jego utworzeniu można go tu wybrać.';
$GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] = 'Przekazanych zapytań z %s nie można wysłać: brakuje adresu nadawcy. Wpisz „Adres e-mail administratora strony” na stronie głównej albo „Adres e-mail administratora systemu” w System › Ustawienia, albo ustaw MAILER_FROM na serwerze. Do tego czasu czat zamiast wysłać pokazuje błąd.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Zgoda niepotrzebna (czat dostępny od razu)',
    'functional' => 'Funkcjonalne',
    'statistics' => 'Statystyka',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (własna kategoria)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'rzeczowo, zawsze tak samo',
    '0.4' => 'domyślnie',
    '1' => 'różnorodnie',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s tokenów (ok. %2$s słów)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Wymagaj zgody na kontakt', 'Pokazuje w formularzu obowiązkowe pole: odwiedzający zgadza się na użycie danych do zapytania i na kontakt. E-mail do zespołu odnotowuje zgodę.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Polityka prywatności', 'Strona, do której prowadzi link przy polu. Puste = bez linku.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Wstępnie wybrany numer kierunkowy', 'Dla pola telefonu. Puste = według języka strony (np. niemiecki → Niemcy). Odwiedzający mogą wybrać dowolny numer kierunkowy.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Według języka strony';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Połączenia', 'Łączyć serwery MCP, odczytywać i blokować ich narzędzia (tylko administratorzy).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Dziennik', 'Wywołania narzędzi z ostatnich 30 dni (tylko administratorzy).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Narzędzia połączeń';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Narzędzia', 'Z czego ten asystent może korzystać. Domyślnie nic nie jest zaznaczone, zablokowane narzędzia nie są wyświetlane. Asystent sam decyduje, kiedy wywołać narzędzie.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'nieaktywne';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'zablokowane';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Już niedostępne';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Nie ma jeszcze połączenia z serwerem MCP. Administratorzy tworzą je przez „Połączenia” nad listą asystentów.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Otwórz połączenia';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Tryb ochrony danych włączony: te narzędzia wymagają danych osoby (%s). Serwer usuwa z wiadomości adresy e-mail i numery telefonów, a asystent nie prosi o dane osobowe, więc narzędzia nie otrzymują tych danych.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Według usługi AI wybrany model nie potrafi wywoływać narzędzi. Wybierz inny model, w przeciwnym razie narzędzia pozostaną nieużywane.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Narzędzia połączeń (np. rezerwacje) wymagają mocnego modelu językowego: musi zaplanować kilka kroków, przejąć dane z rozmowy i wywoływać narzędzia jedno po drugim. Słabsze modele pytają ponownie, pomijają kroki lub się poddają. Przetestowane na procesie rezerwacji szablonu apaleo: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'Wybrany model do nich należy.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'Wybrany model do nich nie należy: sprawdź procesy swoich połączeń w czacie, zanim skorzystają z nich odwiedzający.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Zaznaczone, ale niedostępne: %d (zablokowane, już niedostępne lub połączenie nieaktywne). Czat z nich nie korzysta.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skille', 'Instalować skille z GitHuba lub jako plik (tylko administratorzy).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Zainstalowane skille';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skille', 'Z których zainstalowanych skilli może korzystać ten asystent. Domyślnie nic nie jest zaznaczone. Model widzi je w swoim katalogu i sam wczytuje skill, gdy pytanie dotyczy jego zakresu.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'Moduł „%s” nie zawiera aktywnej usługi w tej kategorii, więc baner nigdy nie prosi odwiedzających o zgodę na czat: tam, gdzie pojawia się baner, czat pozostaje niedostępny; na stronach bez banera uruchamia się bez pytania. Proszę dodać usługę „Asystent czatu AI” w tej kategorii lub wybrać inną kategorię.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Dodaj usługę teraz';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Administrator może ją dodać w module „%s”.';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Języki i pytania startowe';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Asystent ma jeden język: powitanie, pytania startowe, pytania i odpowiedzi oraz teksty okna czatu są w tym języku. Dla każdego kolejnego języka strony utwórz osobnego asystenta (skopiuj go i zmień język) i wybierz go w module czatu tego języka.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Brak języka: czat pokazuje teksty domyślne i żadnych pytań startowych.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Brak pytań startowych';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Edytuj teksty i pytania startowe';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Dodaj język';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Odpowiedź:';
