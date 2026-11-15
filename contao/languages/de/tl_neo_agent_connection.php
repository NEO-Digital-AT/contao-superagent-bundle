<?php

declare(strict_types=1);

// Connections to MCP servers (edition Plus), administrators only.
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['new'] = ['Neue Verbindung', 'Einen MCP-Server verbinden.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['edit'] = ['Bearbeiten', 'Verbindung bearbeiten.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['delete'] = ['Löschen', 'Verbindung löschen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['show'] = ['Details', 'Details anzeigen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['back_to_agents'] = ['Assistenten', 'Zurück zur Liste der Assistenten.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['title_legend'] = 'Verbindung';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_legend'] = 'Anmeldung';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['data_legend'] = 'Persönliche Daten';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_legend'] = 'Werkzeuge';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_legend'] = 'Bestätigung';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['name'] = ['Name', 'So heißt die Verbindung in der Liste und bei der Auswahl am Assistenten, z. B. „Terminbuchung“.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url'] = ['Adresse des MCP-Servers', 'Die Adresse, die der Anbieter für seinen MCP-Server nennt, z. B. https://mcp.example.com/mcp. Nur HTTPS und nur öffentliche Adressen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth'] = ['Anmeldung', 'Wie sich die Website beim Server anmeldet. Das steht in der Anleitung des Anbieters.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['auth_options'] = [
    'none' => 'Ohne Anmeldung',
    'key' => 'Schlüssel (Authorization: Bearer)',
    'headers' => 'Eigene Header',
    'oauth' => 'OAuth (Anmeldung beim Dienst)',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data'] = ['Persönliche Daten', 'Gilt in beide Richtungen: für die Angaben, die der Assistent an den Dienst schickt, und für die Ergebnisse, die an den KI-Dienst gehen. Erkannt werden E-Mail-Adressen, Telefonnummern, IBAN, Kartennummern, Sozialversicherungsnummern und Geburtsdaten, keine Namen und Adressen.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['personal_data_options'] = [
    'mask' => 'Maskieren: durch einen Platzhalter ersetzen',
    'block' => 'Nicht senden: Aufruf oder Ergebnis zurückhalten',
    'send' => 'Alles senden',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['api_key'] = ['Schlüssel', 'Wird als „Authorization: Bearer …“ mitgeschickt. Wird verschlüsselt gespeichert und nicht wieder angezeigt. Leer lassen behält den gespeicherten Schlüssel.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['headers'] = ['Eigene Header', 'Name und Wert je Zeile, z. B. „X-Api-Key“. Die Werte werden verschlüsselt gespeichert und nicht wieder angezeigt; ein leerer Wert behält den gespeicherten.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_id'] = ['Client-ID (nur bei eigener App)', 'Nur nötig, wenn der Dienst keine automatische Registrierung bietet: Legen Sie dort eine App an, tragen Sie die Weiterleitungsadresse oben ein und hier die Client-ID. Leer = automatische Registrierung.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_client_secret'] = ['Client-Secret (nur bei eigener App)', 'Das Secret der App, falls der Dienst eines vergibt. Wird verschlüsselt gespeichert und nicht wieder angezeigt.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['warning'] = 'Achtung: Über einen MCP-Server kann der Assistent Funktionen eines anderen Systems aufrufen – auch solche, die dort Daten ändern, löschen oder Kundendaten anzeigen. Jeder Besucher des Chats arbeitet dabei mit dem Zugang dieser Verbindung. Geben Sie nur Werkzeuge frei, die wenig Risiko bergen oder die ein Skill eingrenzt, und testen Sie den Assistenten, bevor Sie ihn für Besucher freischalten. Werkzeuge, die laut Server Daten ändern oder löschen oder nicht gekennzeichnet sind, sind ab Werk gesperrt. Ein Restrisiko bleibt trotzdem, denn ein Sprachmodell kann sich irren oder durch geschickt formulierte Nachrichten getäuscht werden. Für Schäden durch freigegebene Werkzeuge übernimmt der Hersteller dieser Erweiterung keine Haftung. Was genau gilt und welche Ausnahmen es gibt, steht in den Lizenzbedingungen.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_active'] = 'aktiv · %d von %d Werkzeugen frei';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unread'] = 'Werkzeuge nicht gelesen';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['badge_unconfirmed'] = 'nicht bestätigt';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['url_https'] = 'Bitte eine Adresse mit https:// eingeben.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['header_name'] = 'Der Header-Name „%s“ ist ungültig. Erlaubt sind Buchstaben, Ziffern und Bindestriche.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_heading'] = 'Anmeldung beim Dienst';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connected'] = 'Angemeldet von %s am %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_not_connected'] = 'Noch nicht angemeldet. Speichern Sie die Adresse, dann „Verbinden“: Sie werden zur Anmeldeseite des Dienstes geleitet und kommen danach hierher zurück.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_connect'] = 'Verbinden';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_reconnect'] = 'Neu verbinden';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_disconnect'] = 'Abmelden';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_redirect'] = 'Weiterleitungsadresse dieser Website (nur für eine eigene App beim Dienst nötig):';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_forgotten'] = 'Die Anmeldung beim Dienst wurde entfernt.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_failed'] = 'Die Anmeldung beim Dienst ist fehlgeschlagen: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_done'] = 'Die Anmeldung beim Dienst ist gespeichert.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_unread'] = 'Die Werkzeuge sind noch nicht gelesen. Sie werden nach dem Speichern gelesen, sobald die Anmeldung vollständig ist.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_url_changed'] = 'Die Adresse hat sich geändert. Die Werkzeuge werden beim Speichern neu gelesen; bis dahin ist die Verbindung nicht aktiv.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_read'] = '%d Werkzeuge von %s, gelesen am %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_reread'] = 'Werkzeuge neu lesen';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_done'] = '%d Werkzeuge gelesen, davon %d gesperrt.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tools_failed'] = 'Die Werkzeuge konnten nicht gelesen werden: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_heading'] = 'Werkzeuge und Sperren';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['lock_all'] = 'Alle Werkzeuge sperren, die laut Server Daten ändern oder löschen oder nicht gekennzeichnet sind';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_locked'] = 'Sperre';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_tool'] = 'Werkzeug';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_kind'] = 'Art laut Server';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['col_note'] = 'Hinweis';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_locked'] = 'gesperrt';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_new'] = 'NEU';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_personal'] = 'braucht persönliche Daten';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['locks_help'] = 'Ein gesperrtes Werkzeug kann kein Assistent benutzen. Wer eine Sperre aufhebt, bestätigt das unten; gespeichert wird, wer es wann getan hat. Neue Werkzeuge des Servers sind bei keinem Assistenten angehakt.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['kind_options'] = [
    'read' => 'liest nur',
    'change' => 'ändert Daten',
    'delete' => 'löscht Daten',
    'unmarked' => 'nicht gekennzeichnet',
];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['unlock_confirm'] = 'Sperre nicht aufgehoben: %s. Bestätigen Sie dazu unten, dass Sie die Werkzeuge geprüft haben.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirmed'] = 'Bestätigt von %s am %s.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['not_confirmed'] = 'Noch nicht bestätigt. Erst danach können Assistenten die Werkzeuge dieser Verbindung benutzen.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_check'] = 'Ich habe die freigegebenen Werkzeuge geprüft und verstehe das Restrisiko.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['confirm_help'] = 'Nötig für die erste Freigabe der Verbindung und jedes Mal, wenn Sie eine Sperre aufheben.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template'] = ['Vorlage', 'Füllt Name, Adresse und Anmeldung aus, solange diese Felder leer sind, und legt fest, wie die Werkzeuge dieses Dienstes eingestuft und gesperrt werden. Ein Skill der Vorlage wird beim Speichern installiert. Leer lassen für einen beliebigen MCP-Server.'];
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_help'] = 'Anleitung des Anbieters:';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_recommended'] = 'empfohlen';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['tool_customer_data'] = 'zeigt Kundendaten';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['oauth_own_app'] = 'Dieser Dienst braucht eine eigene App: Legen Sie beim Dienst eine App an und tragen Sie dort die Weiterleitungsadresse ein, die unten steht. Client-ID und Client-Secret der App kommen in die Felder darunter. Danach „Verbinden“.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_installed'] = 'Der Skill „%s“ der Vorlage ist installiert. Haken Sie ihn bei den Assistenten an, die ihn benutzen sollen.';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_skill_failed'] = 'Der Skill der Vorlage „%s“ konnte nicht installiert werden: %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup'] = 'Einrichtung bei %s';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copy'] = 'Kopieren';
$GLOBALS['TL_LANG']['tl_neo_agent_connection']['template_setup_copied'] = 'Kopiert';
