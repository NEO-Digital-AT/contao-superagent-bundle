<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Nové připojení', 'Připojit server MCP.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Upravit', 'Upravit připojení.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Smazat', 'Smazat připojení.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Podrobnosti', 'Zobrazit podrobnosti.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Asistenti', 'Zpět na seznam asistentů.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Připojení';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Přihlášení';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Osobní údaje';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Nástroje';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Potvrzení';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Název', 'Název připojení v seznamu a při výběru u asistenta, např. „Rezervace termínů“.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Adresa serveru MCP', 'Adresa, kterou poskytovatel uvádí pro svůj server MCP, např. https://mcp.example.com/mcp. Pouze HTTPS a pouze veřejné adresy.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Přihlášení', 'Jak se web přihlašuje k serveru. Uvádí to návod poskytovatele.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Bez přihlášení',
    'key' => 'Klíč (Authorization: Bearer)',
    'headers' => 'Vlastní hlavičky',
    'oauth' => 'OAuth (přihlášení ke službě)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Osobní údaje', 'Platí v obou směrech: pro to, co asistent posílá službě, a pro výsledky, které jdou ke službě AI. Rozpoznávají se e-mailové adresy, telefonní čísla, IBAN, čísla karet, čísla sociálního pojištění a data narození, ne jména a adresy.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Maskovat: nahradit zástupným textem',
    'block' => 'Neposílat: zadržet volání nebo výsledek',
    'send' => 'Posílat vše',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Klíč', 'Posílá se jako „Authorization: Bearer …“. Ukládá se šifrovaně a už se nezobrazí. Prázdné pole ponechá uložený klíč.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Vlastní hlavičky', 'Název a hodnota na řádek, např. „X-Api-Key“. Hodnoty se ukládají šifrovaně a už se nezobrazí; prázdná hodnota ponechá uloženou.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['ID klienta (jen vlastní aplikace)', 'Potřeba jen tehdy, když služba nenabízí automatickou registraci: založte tam aplikaci, zadejte tam výše uvedenou adresu přesměrování a sem ID klienta. Prázdné = automatická registrace.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Tajný klíč klienta (jen vlastní aplikace)', 'Tajný klíč aplikace, pokud ho služba vydává. Ukládá se šifrovaně a už se nezobrazí.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Pozor: přes server MCP může asistent volat funkce jiného systému – i takové, které tam mění nebo mažou data nebo zobrazují údaje zákazníků. Každý návštěvník chatu přitom pracuje s přístupem tohoto připojení. Povolte jen nástroje s malým rizikem nebo nástroje, které omezuje skill, a asistenta otestujte, než ho zpřístupníte návštěvníkům. Nástroje, které podle serveru mění nebo mažou data nebo nejsou označené, jsou ve výchozím stavu zablokované. Zbytkové riziko přesto zůstává, protože jazykový model se může mýlit nebo ho mohou oklamat chytře formulované zprávy. Výrobce tohoto rozšíření nenese odpovědnost za škody způsobené povolenými nástroji. Co přesně platí a jaké jsou výjimky, stanoví licenční podmínky.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'aktivní · povoleno %d z %d nástrojů';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'nástroje nenačteny';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'nepotvrzeno';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Zadejte adresu začínající https://.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'Název hlavičky „%s“ je neplatný. Povolena jsou písmena, číslice a pomlčky.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Přihlášení ke službě';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Přihlásil(a) %s dne %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Zatím nepřihlášeno. Uložte adresu a pak „Připojit“: přejdete na přihlašovací stránku služby a poté se vrátíte sem.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Připojit';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Připojit znovu';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Odhlásit';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Adresa přesměrování tohoto webu (potřeba jen pro vlastní aplikaci u služby):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'Přihlášení ke službě bylo odstraněno.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'Přihlášení ke službě se nezdařilo: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'Přihlášení ke službě bylo uloženo.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Nástroje zatím nebyly načteny. Načtou se po uložení, jakmile bude přihlášení úplné.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'Adresa se změnila. Nástroje se při uložení načtou znovu; do té doby není připojení aktivní.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d nástrojů z %s, načteno %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Načíst nástroje znovu';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = 'Načteno nástrojů: %d, z toho zablokovaných: %d.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'Nástroje se nepodařilo načíst: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Nástroje a blokace';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Zablokovat všechny nástroje, které podle serveru mění nebo mažou data nebo nejsou označené';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Blokace';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Nástroj';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Druh podle serveru';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Poznámka';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'zablokováno';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NOVÉ';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'potřebuje osobní údaje';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Zablokovaný nástroj nemůže použít žádný asistent. Kdo blokaci zruší, potvrdí to níže; ukládá se, kdo to udělal a kdy. Nové nástroje serveru nejsou zaškrtnuté u žádného asistenta.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'jen čte',
    'change' => 'mění data',
    'delete' => 'maže data',
    'unmarked' => 'neoznačeno',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Blokace nezrušena: %s. Ke zrušení níže potvrďte, že jste nástroje zkontrolovali.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Potvrdil(a) %s dne %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Zatím nepotvrzeno. Teprve potom mohou asistenti používat nástroje tohoto připojení.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'Zkontroloval(a) jsem povolené nástroje a rozumím zbytkovému riziku.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Nutné pro první povolení připojení a pokaždé, když zrušíte blokaci.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Šablona', 'Vyplní název, adresu a přihlášení, dokud jsou tato pole prázdná, a určí, jak se nástroje této služby zařadí a zablokují. Skill šablony se nainstaluje při uložení. Pro libovolný server MCP nechte prázdné.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Návod poskytovatele:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'doporučeno';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'zobrazuje údaje zákazníků';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Tato služba potřebuje vlastní aplikaci: založte u služby aplikaci a zadejte tam adresu přesměrování uvedenou níže. ID klienta a tajný klíč klienta aplikace patří do polí pod tím. Potom „Připojit“.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'Skill „%s“ šablony je nainstalován. Zaškrtněte ho u asistentů, kteří ho mají používat.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'Skill šablony „%s“ se nepodařilo nainstalovat: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Nastavení u služby %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Kopírovat';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Zkopírováno';
