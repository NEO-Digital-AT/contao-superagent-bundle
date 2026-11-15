<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Új kapcsolat', 'MCP-szerver csatlakoztatása.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Szerkesztés', 'A kapcsolat szerkesztése.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Törlés', 'A kapcsolat törlése.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Részletek', 'A részletek megjelenítése.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Asszisztensek', 'Vissza az asszisztensek listájához.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Kapcsolat';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Bejelentkezés';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Személyes adatok';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Eszközök';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Megerősítés';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Név', 'A kapcsolat neve a listában és az asszisztensnél való választáskor, pl. „Időpontfoglalás”.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Az MCP-szerver címe', 'A cím, amelyet a szolgáltató az MCP-szerveréhez megad, pl. https://mcp.example.com/mcp. Csak HTTPS és csak nyilvános címek.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Bejelentkezés', 'Hogyan jelentkezik be a weboldal a szerverre. Ezt a szolgáltató útmutatója tartalmazza.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Bejelentkezés nélkül',
    'key' => 'Kulcs (Authorization: Bearer)',
    'headers' => 'Saját fejlécek',
    'oauth' => 'OAuth (bejelentkezés a szolgáltatásnál)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Személyes adatok', 'Mindkét irányban érvényes: arra, amit az asszisztens a szolgáltatásnak küld, és az MI-szolgáltatáshoz továbbított eredményekre. Felismeri az e-mail-címeket, telefonszámokat, IBAN-okat, kártyaszámokat, társadalombiztosítási számokat és születési dátumokat, a neveket és címeket nem.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Maszkolás: helyettesítő szövegre cserélés',
    'block' => 'Nem küldi el: a hívást vagy az eredményt visszatartja',
    'send' => 'Mindent elküld',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Kulcs', '„Authorization: Bearer …” formában kerül elküldésre. Titkosítva tárolódik, és többé nem jelenik meg. Üresen hagyva a tárolt kulcs marad.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Saját fejlécek', 'Soronként név és érték, pl. „X-Api-Key”. Az értékek titkosítva tárolódnak, és többé nem jelennek meg; az üres érték megtartja a tároltat.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['Kliensazonosító (csak saját alkalmazásnál)', 'Csak akkor szükséges, ha a szolgáltatás nem kínál automatikus regisztrációt: hozzon létre ott egy alkalmazást, adja meg ott a fenti átirányítási címet, itt pedig a kliensazonosítót. Üres = automatikus regisztráció.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Klienstitok (csak saját alkalmazásnál)', 'Az alkalmazás titka, ha a szolgáltatás ad ilyet. Titkosítva tárolódik, és többé nem jelenik meg.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Figyelem: egy MCP-szerveren keresztül az asszisztens egy másik rendszer funkcióit hívhatja meg – olyanokat is, amelyek ott adatokat módosítanak, törölnek vagy ügyféladatokat jelenítenek meg. A csevegés minden látogatója ennek a kapcsolatnak a hozzáférésével dolgozik. Csak kis kockázatú vagy skill által korlátozott eszközöket engedélyezzen, és tesztelje az asszisztenst, mielőtt a látogatók számára elérhetővé teszi. Azok az eszközök, amelyek a szerver szerint adatokat módosítanak vagy törölnek, illetve nincsenek megjelölve, alapértelmezés szerint zárolva vannak. Maradék kockázat így is marad, mert egy nyelvi modell tévedhet, vagy ügyesen megfogalmazott üzenetek félrevezethetik. A bővítmény gyártója nem vállal felelősséget az engedélyezett eszközök által okozott károkért. Hogy pontosan mi érvényes és milyen kivételek vannak, azt a licencfeltételek tartalmazzák.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'aktív · %d / %d eszköz engedélyezve';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'eszközök nincsenek beolvasva';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'nincs megerősítve';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Adjon meg https://-sel kezdődő címet.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'A(z) „%s” fejlécnév érvénytelen. Betűk, számjegyek és kötőjelek engedélyezettek.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Bejelentkezés a szolgáltatásnál';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Bejelentkezett: %s, %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Még nincs bejelentkezve. Mentse a címet, majd „Csatlakozás”: a szolgáltatás bejelentkezési oldalára kerül, utána ide tér vissza.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Csatlakozás';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Újracsatlakozás';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Kijelentkezés';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'A weboldal átirányítási címe (csak saját alkalmazáshoz szükséges a szolgáltatásnál):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'A bejelentkezés a szolgáltatásnál törölve.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'A bejelentkezés a szolgáltatásnál nem sikerült: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'A bejelentkezés a szolgáltatásnál mentve.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Az eszközök még nincsenek beolvasva. Mentés után kerülnek beolvasásra, amint a bejelentkezés teljes.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'A cím megváltozott. Az eszközöket mentéskor újra beolvassa; addig a kapcsolat nem aktív.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d eszköz innen: %s, beolvasva: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Eszközök újraolvasása';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = 'Beolvasott eszközök: %d, ebből zárolva: %d.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'Az eszközöket nem sikerült beolvasni: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Eszközök és zárolások';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Minden olyan eszköz zárolása, amely a szerver szerint adatokat módosít vagy töröl, vagy nincs megjelölve';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Zárolás';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Eszköz';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Típus a szerver szerint';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Megjegyzés';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'zárolva';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'ÚJ';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'személyes adatokat igényel';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Zárolt eszközt egyetlen asszisztens sem használhat. Aki feloldja a zárolást, lent megerősíti; megőrződik, ki és mikor tette. A szerver új eszközei egyetlen asszisztensnél sincsenek bejelölve.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'csak olvas',
    'change' => 'adatokat módosít',
    'delete' => 'adatokat töröl',
    'unmarked' => 'nincs megjelölve',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'A zárolás nem lett feloldva: %s. A feloldáshoz erősítse meg lent, hogy ellenőrizte az eszközöket.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Megerősítette: %s, %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Még nincs megerősítve. Csak ezután használhatják az asszisztensek a kapcsolat eszközeit.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'Ellenőriztem az engedélyezett eszközöket, és megértem a maradék kockázatot.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Szükséges a kapcsolat első engedélyezéséhez és minden zárolásfeloldáskor.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Sablon', 'Kitölti a nevet, a címet és a bejelentkezést, amíg ezek a mezők üresek, és meghatározza, hogyan sorolódnak be és zárolódnak a szolgáltatás eszközei. A sablon skillje mentéskor települ. Tetszőleges MCP-szerverhez hagyja üresen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'A szolgáltató útmutatója:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'ajánlott';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'ügyféladatokat mutat';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Ehhez a szolgáltatáshoz saját alkalmazás kell: hozzon létre egy alkalmazást a szolgáltatásnál, és adja meg ott a lent látható átirányítási címet. Az alkalmazás kliensazonosítója és titkos kulcsa az alatta lévő mezőkbe kerül. Ezután „Csatlakozás”.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'A sablon „%s” skillje telepítve van. Jelölje be azoknál az asszisztenseknél, amelyeknek használniuk kell.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'A(z) „%s” sablon skillje nem telepíthető: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Beállítás itt: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Másolás';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Másolva';
