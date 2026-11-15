<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Nowe połączenie', 'Połączyć serwer MCP.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Edytuj', 'Edytuj połączenie.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Usuń', 'Usuń połączenie.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Szczegóły', 'Pokaż szczegóły.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Asystenci', 'Powrót do listy asystentów.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Połączenie';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Logowanie';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Dane osobowe';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Narzędzia';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Potwierdzenie';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Nazwa', 'Nazwa połączenia na liście i przy wyborze w asystencie, np. „Rezerwacja terminów”.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Adres serwera MCP', 'Adres, który dostawca podaje dla swojego serwera MCP, np. https://mcp.example.com/mcp. Tylko HTTPS i tylko adresy publiczne.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Logowanie', 'Jak strona loguje się na serwerze. Podaje to instrukcja dostawcy.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Bez logowania',
    'key' => 'Klucz (Authorization: Bearer)',
    'headers' => 'Własne nagłówki',
    'oauth' => 'OAuth (logowanie w usłudze)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Dane osobowe', 'Obowiązuje w obu kierunkach: dla tego, co asystent wysyła do usługi, i dla wyników przekazywanych do usługi AI. Rozpoznawane są adresy e-mail, numery telefonów, IBAN, numery kart, numery ubezpieczenia społecznego i daty urodzenia, nie imiona i nazwiska ani adresy.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Maskować: zastąpić symbolem zastępczym',
    'block' => 'Nie wysyłać: wstrzymać wywołanie lub wynik',
    'send' => 'Wysyłać wszystko',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Klucz', 'Wysyłany jako „Authorization: Bearer …”. Zapisywany w postaci zaszyfrowanej i nigdy więcej nie wyświetlany. Puste pole zachowuje zapisany klucz.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Własne nagłówki', 'Nazwa i wartość w każdym wierszu, np. „X-Api-Key”. Wartości są zapisywane w postaci zaszyfrowanej i nie są ponownie wyświetlane; pusta wartość zachowuje zapisaną.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['ID klienta (tylko własna aplikacja)', 'Potrzebne tylko wtedy, gdy usługa nie oferuje automatycznej rejestracji: utwórz tam aplikację, wpisz tam powyższy adres przekierowania, a tutaj ID klienta. Puste = automatyczna rejestracja.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Sekret klienta (tylko własna aplikacja)', 'Sekret aplikacji, jeśli usługa go wydaje. Zapisywany w postaci zaszyfrowanej i nigdy więcej nie wyświetlany.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Uwaga: przez serwer MCP asystent może wywoływać funkcje innego systemu – także takie, które zmieniają tam lub usuwają dane albo pokazują dane klientów. Każdy odwiedzający czat korzysta przy tym z dostępu tego połączenia. Udostępniaj tylko narzędzia o niskim ryzyku lub ograniczone przez skill i przetestuj asystenta, zanim udostępnisz go odwiedzającym. Narzędzia, które według serwera zmieniają lub usuwają dane albo nie są oznaczone, są domyślnie zablokowane. Ryzyko szczątkowe pozostaje, ponieważ model językowy może się mylić lub zostać wprowadzony w błąd przez sprytnie sformułowane wiadomości. Producent tego rozszerzenia nie ponosi odpowiedzialności za szkody spowodowane przez udostępnione narzędzia. Co dokładnie obowiązuje i jakie są wyjątki, określają warunki licencji.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'aktywne · udostępniono %d z %d narzędzi';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'narzędzia nieodczytane';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'niepotwierdzone';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Podaj adres zaczynający się od https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'Nazwa nagłówka „%s” jest nieprawidłowa. Dozwolone są litery, cyfry i łączniki.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Logowanie w usłudze';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Zalogowano przez %s dnia %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Jeszcze nie zalogowano. Zapisz adres, a następnie „Połącz”: zostaniesz przekierowany na stronę logowania usługi i potem wrócisz tutaj.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Połącz';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Połącz ponownie';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Wyloguj';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Adres przekierowania tej strony (potrzebny tylko dla własnej aplikacji w usłudze):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'Logowanie w usłudze zostało usunięte.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'Logowanie w usłudze nie powiodło się: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'Logowanie w usłudze zostało zapisane.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Narzędzia nie zostały jeszcze odczytane. Zostaną odczytane po zapisaniu, gdy logowanie będzie kompletne.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'Adres się zmienił. Narzędzia zostaną ponownie odczytane przy zapisie; do tego czasu połączenie nie jest aktywne.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d narzędzi z %s, odczytano %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Odczytaj narzędzia ponownie';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = 'Odczytano narzędzia: %d, w tym zablokowane: %d.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'Nie udało się odczytać narzędzi: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Narzędzia i blokady';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Zablokuj wszystkie narzędzia, które według serwera zmieniają lub usuwają dane albo nie są oznaczone';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Blokada';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Narzędzie';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Rodzaj według serwera';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Uwaga';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'zablokowane';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NOWE';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'wymaga danych osobowych';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Żaden asystent nie może użyć zablokowanego narzędzia. Kto zdejmuje blokadę, potwierdza to poniżej; zapisywane jest, kto i kiedy to zrobił. Nowe narzędzia serwera nie są zaznaczone u żadnego asystenta.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'tylko odczyt',
    'change' => 'zmienia dane',
    'delete' => 'usuwa dane',
    'unmarked' => 'nieoznaczone',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Blokada nie została zdjęta: %s. Aby ją zdjąć, potwierdź poniżej, że sprawdziłeś narzędzia.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Potwierdzone przez %s dnia %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Jeszcze niepotwierdzone. Dopiero wtedy asystenci mogą używać narzędzi tego połączenia.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'Sprawdziłem udostępnione narzędzia i rozumiem ryzyko szczątkowe.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Wymagane przy pierwszym udostępnieniu połączenia i za każdym razem, gdy zdejmujesz blokadę.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Szablon', 'Uzupełnia nazwę, adres i logowanie, dopóki te pola są puste, i określa, jak narzędzia tej usługi są klasyfikowane i blokowane. Skill szablonu zostaje zainstalowany przy zapisie. Zostaw puste dla dowolnego serwera MCP.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Instrukcja dostawcy:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'zalecane';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'pokazuje dane klientów';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Ta usługa wymaga własnej aplikacji: utwórz aplikację w usłudze i wpisz tam adres przekierowania podany poniżej. ID klienta i sekret klienta aplikacji wpisz w pola pod spodem. Następnie „Połącz”.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'Skill „%s” szablonu jest zainstalowany. Zaznacz go przy asystentach, którzy mają go używać.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'Nie udało się zainstalować skilla szablonu „%s”: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Konfiguracja w usłudze %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Kopiuj';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Skopiowano';
