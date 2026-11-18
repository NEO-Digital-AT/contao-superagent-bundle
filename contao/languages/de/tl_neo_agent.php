<?php

declare(strict_types=1);

// Legends
$GLOBALS['TL_LANG']['tl_neo_agent']['title_legend'] = 'Allgemein';
$GLOBALS['TL_LANG']['tl_neo_agent']['identity_legend'] = 'Identität';
$GLOBALS['TL_LANG']['tl_neo_agent']['provider_legend'] = 'Modell';
$GLOBALS['TL_LANG']['tl_neo_agent']['prompt_legend'] = 'System-Prompt';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_legend'] = 'Wissensbasis (Grounding)';
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_legend'] = 'Eskalation & Einwilligung';

// Fields
$GLOBALS['TL_LANG']['tl_neo_agent']['title'] = ['Bezeichnung', 'Interner Name dieses Assistenten (nur im Backend sichtbar).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['published'] = ['Aktiv', 'Nur ein aktiver Assistent wird im Frontend angezeigt.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['assistant_name'] = ['Anzeigename', 'Wie sich der Assistent vorstellt und im Chat-Kopf erscheint (z. B. „Max“). Gilt für alle Sprachen.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['model'] = ['Modell', 'Das Sprachmodell dieses Assistenten. Route und Schlüssel stehen unter „Zugänge und Lizenzen“ › KI; ist dort ein Schlüssel gespeichert, stehen hier die Modelle der Route zur Auswahl.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature'] = ['Temperatur', 'Wie frei das Modell formuliert: niedriger = sachlicher und gleichbleibender. Für einen Chat passt 0,2 bis 0,5.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens'] = ['Antwortlänge (Max. Tokens)', 'Höchstlänge einer Antwort. Bei Modellen, die denken, kommt Platz zum Denken dazu.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['System-Prompt', 'Verhaltensregeln des Assistenten. {name} wird durch den Anzeigenamen ersetzt. Gilt für alle Sprachen.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_source'] = ['Wissensquelle', 'Quelle der gesicherten Fakten, auf die sich der Assistent stützt. Gilt für alle Sprachen.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_text'] = ['Wissenstext', 'Gesicherte Fakten als Text. Wird dem System-Prompt als Kontext beigefügt.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_file'] = ['Wissensdatei', 'Datei (txt/md/json) aus der Dateiverwaltung als Wissensbasis.'];

$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_email'] = ['Eskalations-E-Mail', 'Adresse, an die weitergeleitete Anfragen aus dem Chat gesendet werden. Leer lassen, wenn es keine gibt – dann bietet der Assistent nie eine Weiterleitung an und verweist auf die Kontaktmöglichkeiten der Website.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['escalation_name'] = ['Weiterleiten an (Name)', 'Steht so auf den Knöpfen im Chat, z. B. „An Max weiterleiten“. Leer lassen für „An das Team“.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_category'] = ['Einwilligungs-Kategorie', 'Kategorie des Cookie-Hinweises, nach deren Zustimmung der Chat erscheint. Die Cookie-Hinweis-Erweiterung derselben Reihe funktioniert ohne weitere Einrichtung; ein anderes Einwilligungswerkzeug braucht ein kleines Skript (Dokumentation, „Anpassen“). Ohne Einwilligungswerkzeug auf der Seite ist der Chat sofort verfügbar.'];

// Operations
$GLOBALS['TL_LANG']['tl_neo_agent']['languages'] = ['Sprachen', 'Sprachvarianten (Begrüßung, Vorschläge, Q&A …) dieses Assistenten verwalten.'];

// Reference (option labels)
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources'] = [
    'none' => 'Keine',
    'text' => 'Text (eingegeben)',
    'file' => 'Datei aus der Dateiverwaltung',
];

// --- Persona (Auswahl statt Freitext-Prompt) + Route/Wissensquelle -----------
$GLOBALS['TL_LANG']['tl_neo_agent']['persona_legend'] = 'Persönlichkeit & Antwortstil';
$GLOBALS['TL_LANG']['tl_neo_agent']['expert_legend'] = 'Experten-Einstellungen';
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address'] = ['Anredeform', 'Wie soll die Assistentin die Besucher ansprechen?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length'] = ['Antwortlänge', 'Wie ausführlich soll geantwortet werden?'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style'] = ['Tonfall', 'Der Grundton der Antworten.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['system_prompt'] = ['Zusätzliche Anweisungen (optional)', 'Nur für Spezialfälle — Anrede, Länge und Tonfall werden bereits oben eingestellt. Platzhalter {name} wird durch den Namen ersetzt.'];
// Die vier neuen/bestehenden Wissensquellen (Referenz-Schlüssel: grounding_sources).
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources']['llms'] = 'llms.txt der Website (empfohlen)';
$GLOBALS['TL_LANG']['tl_neo_agent']['grounding_sources']['llms_text'] = 'llms.txt + eigener Zusatztext';

// --- Internetsuche -----------------------------------------------------------
$GLOBALS['TL_LANG']['tl_neo_agent']['research_legend'] = 'Internetsuche';
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_legend'] = 'Vermittlung — an wen verwiesen wird';
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search'] = ['Internetsuche erlauben', 'Der Assistent darf selbst entscheiden, ob er für eine Frage im Internet nachsieht — aber ausschließlich auf den unten freigegebenen Quellen. Ohne Freigabe antwortet er nur aus der Wissensbasis.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode'] = ['Suchdienst', '„Nur freigegebene Quellen lesen“ ruft die eingetragenen Adressen direkt ab (kostenlos, ideal für llms.txt der Hersteller). „SerpAPI“ sucht zusätzlich über eine Suchmaschine — das Ergebnis wird trotzdem hart auf die freigegebenen Domains gefiltert.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_sources'] = ['Freigegebene Quellen', 'Eine Adresse je Zeile: Partner-/Herstellerseite, deren llms.txt oder ein Datenendpunkt (z. B. der Katalog eines Marktplatzes). Passt die Antwort nicht auf die Startseite, folgt der Assistent den passenden Unterseiten. Optional Stichworte anhängen — „adresse | stichwort1 stichwort2“ —, damit er weiß, wofür eine Quelle zuständig ist; bei API-Adressen ist das nötig, weil man es der Adresse nicht ansieht. Diese Liste ist gleichzeitig die Sperre: Es wird NICHTS außerhalb dieser Domains abgerufen, auch wenn ein Besucher den Assistenten dazu auffordert.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max'] = ['Quellen je Frage', 'Wie viele Quellen der Assistent für eine Frage höchstens liest. Mehr Quellen = bessere Antworten, aber langsamer und teurer.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['referral_company'] = ['Immer verweisen auf', 'Bei Einrichtung, Umsetzung, Integration, Migration, Betreuung und Kauf verweist der Assistent IMMER auf diesen Anbieter — nie auf den Hersteller oder Entwickler einer fremden Lösung. Fremdprodukte darf er nennen; Ansprechpartner bleiben immer Sie. Leer lassen = keine Vermittlungsregel.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution'] = ['Bevorzugte eigene Lösung', 'Passt diese Lösung zur Frage, nennt der Assistent sie zuerst — ehrlich und nur dort, wo sie wirklich passt. Leer lassen = keine Bevorzugung.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['preferred_solution_note'] = ['Wofür die Lösung passt', 'Ein bis zwei Sätze: Was kann sie, für wen ist sie gedacht? Der Assistent nutzt das, um sie nur dann zu nennen, wenn sie wirklich passt.'];

// Auswahllisten (Schlüssel = gespeicherter Wert)
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_address_options'] = ['sie' => 'Förmlich (Sie)', 'du' => 'Locker (du)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['answer_length_options'] = ['short' => 'Kurz (2–3 Sätze)', 'medium' => 'Mittel (4–6 Sätze)', 'long' => 'Ausführlich (bei Bedarf mit Aufzählung)'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tone_style_options'] = ['professional' => 'Professionell & sachlich', 'friendly' => 'Freundlich & zugewandt', 'expert' => 'Fachlich & präzise'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_mode_options'] = ['sources' => 'Nur die freigegebenen Quellen lesen (kostenlos)', 'serpapi' => 'Über SerpAPI suchen — eingeschränkt auf die freigegebenen Domains'];
$GLOBALS['TL_LANG']['tl_neo_agent']['web_search_max_options'] = ['1' => '1 Quelle', '2' => '2 Quellen', '3' => '3 Quellen', '4' => '4 Quellen', '5' => '5 Quellen'];
$GLOBALS['TL_LANG']['tl_neo_agent']['settings'] = ['Zugänge und Lizenzen', 'KI-Zugang, Internetsuche und Lizenz des SuperAgent'];
$GLOBALS['TL_LANG']['tl_neo_agent']['ai_missing'] = 'Noch kein KI-Schlüssel hinterlegt. Bis dahin zeigt die Website den Chat nicht an.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_stored'] = 'Der SerpAPI-Schlüssel ist unter „Zugänge und Lizenzen“ hinterlegt.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_missing'] = 'Noch kein SerpAPI-Schlüssel hinterlegt. Bis dahin liest der Assistent nur die freigegebenen Quellen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link'] = 'Öffnen';
$GLOBALS['TL_LANG']['tl_neo_agent']['serpapi_link_missing'] = 'Jetzt hinterlegen';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_missing'] = 'Für %1$s (Startpunkt „%2$s“) hat noch kein Assistent eigene Texte; der Chat zeigt dort die Standardtexte.';
$GLOBALS['TL_LANG']['tl_neo_agent']['language_create'] = 'Mit Vorschlägen anlegen bei „%s“';
$GLOBALS['TL_LANG']['tl_neo_agent']['safety_legend'] = 'Sicherheit';
$GLOBALS['TL_LANG']['tl_neo_agent']['no_medical'] = ['Keine medizinischen Auskünfte', 'Der Assistent gibt keine Diagnosen, Behandlungs-, Medikamenten- oder Gesundheitstipps und lehnt solche Fragen in eigenen Worten ab, nach der Vorgabe im Sprachdatensatz (z. B. Notrufnummer). Eine Regel an ein Sprachmodell schließt Fehlgriffe nicht ganz aus.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_mode'] = ['Datenschutz-Modus', 'Der Chat bittet, keine persönlichen Daten einzugeben. E-Mail-Adressen, Telefonnummern, IBAN, Kartennummern, Sozialversicherungsnummern und Geburtsdaten entfernt der Server aus der Nachricht, bevor sie an den KI-Dienst geht; auf Gesundheitsangaben und Ähnliches achtet das Modell. Orte, Länder und Adressen ohne Namen gelten nicht als persönliche Daten (z. B. Fragen zum Wetter). Funktionen, die persönliche Daten brauchen (z. B. buchen oder eine Reservierung abrufen), sind dann nicht möglich.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['contact_page'] = ['Kontaktseite', 'Diese Seite kann der Assistent öffnen, wenn ein Besucher eine Person erreichen will (Basis-Lizenz), zusätzlich zum Formular im Chat.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['avatar'] = ['Bild', 'Erscheint statt des Roboter-Icons im Chatfenster (quadratisch, z. B. 96 × 96 px).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side'] = ['Position', 'Ecke des Bildschirms für Chat-Knopf und Fenster. Sitzen Knöpfe anderer Erweiterungen (Cookie-Einstellungen, Barrierefreiheit) in derselben Ecke, werden sie gemeinsam so angeordnet, wie es im Seitenlayout eingestellt ist: eine Einstellungs-Schaltfläche mit Menü, übereinander oder nebeneinander.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['widget_side_options'] = [
    'right' => 'Unten rechts',
    'left' => 'Unten links',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort'] = ['Denkaufwand', 'Wie gründlich das Modell nachdenkt, bevor es antwortet. Mehr Denken = genauer, aber langsamer und teurer. Für einen Chat auf der Website reicht meist „Niedrig“.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['reasoning_effort_options'] = ['off' => 'Aus', 'low' => 'Niedrig (empfohlen)', 'medium' => 'Mittel', 'high' => 'Hoch'];
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_reasoning'] = 'kann denken';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_tools'] = 'kann Werkzeuge nutzen';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_max'] = 'bis zu %s Tokens je Antwort';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_no_tools'] = 'Dieses Modell kann keine Werkzeuge nutzen. Die Internetsuche funktioniert damit nicht; Navigation und Weiterleitung laufen über Ersatzzeilen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['caps_untested'] = 'Mit Werkzeugen von Verbindungen nicht getestet. Getestet: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_legend'] = 'Spracheingabe';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_input'] = ['Spracheingabe erlauben', 'Besucher können eine Nachricht einsprechen (höchstens 60 Sekunden). Die Aufnahme geht über Ihren Server zur Umschrift an den KI-Dienst, wird nicht gespeichert, und der Text erscheint im Eingabefeld zum Prüfen. Bei eingeschaltetem Datenschutzmodus nur zusammen mit „Spracheingabe auch im Datenschutzmodus“.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['transcription_model'] = ['Umschrift-Modell', 'Modell für die Umschrift, aus der Liste der Route (bei Requesty unter „Transcriptions“ freigeben). In der EU verarbeiten z. B. die Modelle von Mistral (Voxtral) und Scaleway.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_privacy_off'] = 'Solange der Datenschutzmodus an ist, gibt es keine Spracheingabe: Eine Aufnahme ginge ungefiltert an den Umschrift-Dienst. Darunter „Spracheingabe auch im Datenschutzmodus“ einschalten, um sie zu erlauben.';
$GLOBALS['TL_LANG']['tl_neo_agent']['voice_in_privacy'] = ['Spracheingabe auch im Datenschutzmodus', 'Das Mikrofon bleibt bei eingeschaltetem Datenschutzmodus verfügbar. Die Aufnahme geht ungefiltert an den Umschrift-Dienst; gefiltert wird erst der Text: E-Mail-Adressen, Telefonnummern und Ähnliches werden entfernt, bevor er im Eingabefeld erscheint und bevor etwas an das Chat-Modell geht.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['forwarding_locked'] = 'Wirkt nur mit einer E-Mail-Adresse für die Weiterleitung oben: Ohne sie hat der Chat kein Formular zum Weiterleiten.';

// Publishing ("preview only") and the terms of the extension
$GLOBALS['TL_LANG']['tl_neo_agent']['publish_legend'] = 'Veröffentlichung';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility'] = ['Sichtbar für', 'Neue Assistenten stehen zuerst auf „Nur Vorschau“: Dann sehen nur angemeldete Administratoren den Chat, in der Vorschau des Frontends. Testen Sie ihn dort, bevor Sie ihn für alle Besucher freischalten.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_options'] = [
    'preview' => 'Nur Vorschau – nur angemeldete Administratoren sehen den Chat in der Vorschau',
    'public' => 'Alle Besucher',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_note'] = '„Alle Besucher“ ist wählbar, sobald die Bedingungen der Erweiterung bestätigt sind.';
$GLOBALS['TL_LANG']['tl_neo_agent']['visibility_locked'] = 'Bitte bestätigen Sie zuerst die Bedingungen unter „Zugänge und Lizenzen“. Bis dahin bleibt der Assistent in der Vorschau.';
$GLOBALS['TL_LANG']['tl_neo_agent']['preview_badge'] = 'Vorschau';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_missing'] = 'Bevor ein Assistent für Besucher sichtbar wird, müssen die Bedingungen bestätigt sein.';
$GLOBALS['TL_LANG']['tl_neo_agent']['terms_edition_missing'] = 'Die Funktionen der Stufe %s sind gesperrt, bis ihre Bedingungen bestätigt sind.';

// Forwarding form: optional fields and the newsletter checkbox
$GLOBALS['TL_LANG']['tl_neo_agent']['form_fields'] = ['Zusätzliche Felder im Formular', 'E-Mail-Adresse und Nachricht stehen immer im Formular und sind Pflicht. Diese Felder kommen auf Wunsch dazu; ihre Angaben gehen nur per E-Mail an die Weiterleitungsadresse, nie an die KI.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_required'] = ['Davon Pflichtfelder', 'Nur Felder, die links gewählt sind, können Pflicht sein.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['form_field_options'] = [
    'name' => 'Name',
    'phone' => 'Telefon',
    'company' => 'Firma',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_module'] = ['Newsletter-Häkchen', 'Bietet im Formular „Newsletter abonnieren“ an, nie vorausgewählt. Gewählt wird ein Newsletter-Modul dieser Website; dessen Listen oder Verteiler und dessen Bestätigungs-E-Mail gelten (Double-Opt-in).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_off'] = 'Aus';
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_kinds'] = [
    'brevo' => 'Brevo',
    'contao' => 'Contao-Newsletter',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_problem'] = [
    'missing' => 'Das gewählte Newsletter-Modul gibt es nicht mehr. Bis ein anderes gewählt ist, zeigt das Formular kein Newsletter-Häkchen.',
    'brevo_incomplete' => 'Im gewählten Brevo-Modul fehlen Liste, Bestätigungsvorlage oder Bestätigungsseite. Bis dahin zeigt das Formular kein Newsletter-Häkchen.',
    'brevo_key' => 'Für Brevo ist noch kein API-Schlüssel hinterlegt („Zugänge und Lizenzen“). Bis dahin zeigt das Formular kein Newsletter-Häkchen.',
    'contao_channels' => 'Im gewählten Modul „Newsletter abonnieren“ ist kein Verteiler gewählt. Bis dahin zeigt das Formular kein Newsletter-Häkchen.',
    'contao_page' => 'Das gewählte Modul „Newsletter abonnieren“ steht auf keiner veröffentlichten Seite. Der Bestätigungslink führt auf diese Seite; bis das Modul dort steht, zeigt das Formular kein Newsletter-Häkchen.',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['newsletter_none'] = 'Es gibt noch kein Newsletter-Modul (Brevo oder „Newsletter abonnieren“ von Contao). Legen Sie eines an, dann lässt es sich hier wählen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['sender_missing'] = 'Weitergeleitete Anfragen von %s können nicht gesendet werden: Es fehlt eine Absenderadresse. Tragen Sie beim Startpunkt die „E-Mail-Adresse des Webseiten-Administrators“ oder unter System › Einstellungen die „E-Mail-Adresse des Systemadministrators“ ein, oder setzen Sie MAILER_FROM auf dem Server. Bis dahin meldet der Chat einen Fehler, statt zu senden.';

// Choices instead of free text
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_options'] = [
    'none' => 'Keine Einwilligung nötig (Chat sofort verfügbar)',
    'functional' => 'Funktional',
    'statistics' => 'Statistik',
    'marketing' => 'Marketing',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_custom'] = '%s (eigene Kategorie)';
$GLOBALS['TL_LANG']['tl_neo_agent']['temperature_notes'] = [
    '0' => 'sachlich, immer gleich formuliert',
    '0.4' => 'Vorgabe',
    '1' => 'abwechslungsreich',
];
$GLOBALS['TL_LANG']['tl_neo_agent']['max_tokens_option'] = '%1$s Tokens (etwa %2$s Wörter)';
$GLOBALS['TL_LANG']['tl_neo_agent']['form_consent'] = ['Einwilligung zur Kontaktaufnahme verlangen', 'Zeigt im Formular ein Pflicht-Häkchen: Der Besucher stimmt zu, dass seine Angaben für die Anfrage verwendet werden und man ihn kontaktiert. Die E-Mail an Ihr Team vermerkt die Zustimmung.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['privacy_page'] = ['Datenschutzerklärung', 'Seite, auf die das Häkchen verlinkt. Leer = ohne Link.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country'] = ['Vorgewählte Ländervorwahl', 'Für das Telefonfeld. Leer = nach der Sprache der Seite (z. B. Deutsch → Deutschland). Besucher können jede Vorwahl wählen.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['phone_country_auto'] = 'Nach Sprache der Seite';

// Tools of the connections to MCP servers (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['connections'] = ['Verbindungen', 'MCP-Server verbinden, Werkzeuge lesen und sperren (nur Administratoren).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['tool_log'] = ['Protokoll', 'Werkzeugaufrufe der letzten 30 Tage (nur Administratoren).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_legend'] = 'Werkzeuge der Verbindungen';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_tools'] = ['Werkzeuge', 'Was dieser Assistent benutzen darf. Ab Werk ist nichts angehakt, gesperrte Werkzeuge erscheinen nicht. Wann er ein Werkzeug aufruft, entscheidet der Assistent selbst.'];
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_inactive'] = 'nicht aktiv';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_locked_tool'] = 'gesperrt';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_gone_group'] = 'Nicht mehr vorhanden';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_none'] = 'Es gibt noch keine Verbindung zu einem MCP-Server. Administratoren legen sie über „Verbindungen“ über der Liste der Assistenten an.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_open_connections'] = 'Verbindungen öffnen';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_privacy'] = 'Datenschutz-Modus an: Diese Werkzeuge verlangen Angaben zu einer Person (%s). Der Server entfernt E-Mail-Adressen und Telefonnummern aus den Nachrichten, und der Assistent fragt nicht nach persönlichen Daten; die Werkzeuge bekommen diese Angaben daher nicht.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model'] = 'Das gewählte Modell kann laut KI-Dienst keine Werkzeuge aufrufen. Wählen Sie ein anderes Modell, sonst bleiben die Werkzeuge ungenutzt.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_models'] = 'Werkzeuge von Verbindungen (z. B. Buchungen) brauchen ein starkes Sprachmodell: Es muss mehrere Schritte planen, Angaben aus dem Gespräch übernehmen und Werkzeuge der Reihe nach aufrufen. Schwächere Modelle fragen erneut nach, lassen Schritte aus oder geben auf. Mit dem Buchungsablauf der Vorlage apaleo getestet: %s.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_tested'] = 'Das gewählte Modell gehört dazu.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_model_untested'] = 'Das gewählte Modell gehört nicht dazu: Prüfen Sie die Abläufe Ihrer Verbindungen im Chat, bevor Besucher sie nutzen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['mcp_unusable'] = 'Angehakt, aber nicht nutzbar: %d (gesperrt, nicht mehr vorhanden oder Verbindung nicht aktiv). Der Chat benutzt sie nicht.';

// Installed skills (edition Plus)
$GLOBALS['TL_LANG']['tl_neo_agent']['skill_list'] = ['Skills', 'Skills aus GitHub oder als Datei installieren (nur Administratoren).'];
$GLOBALS['TL_LANG']['tl_neo_agent']['skills_legend'] = 'Installierte Skills';
$GLOBALS['TL_LANG']['tl_neo_agent']['skills'] = ['Skills', 'Welche installierten Skills dieser Assistent benutzen darf. Ab Werk ist nichts angehakt. Das Modell sieht sie im Katalog und lädt einen Skill selbst, wenn eine Frage in seinen Bereich fällt.'];

// Consent category the cookie consent extension never asks for (reason at the field).
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_unoffered'] = 'Im Modul „%s“ steht kein aktiver Dienst in dieser Kategorie, deshalb fragt sein Banner Besucher nie nach der Einwilligung für den Chat: Wo das Banner erscheint, bleibt der Chat nicht verfügbar; auf Seiten ohne Banner startet er ohne Nachfrage. Legen Sie den Dienst „KI-Chat-Assistent“ in dieser Kategorie an oder wählen Sie eine andere Kategorie.';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_add'] = 'Dienst jetzt anlegen';
$GLOBALS['TL_LANG']['tl_neo_agent']['consent_ask_admin'] = 'Ein Administrator kann ihn im Modul „%s“ anlegen.';

// Languages and starter questions in the assistant's form (LanguageCallbacks::overview).
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_legend'] = 'Sprachen und Einstiegsfragen';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_intro'] = 'Ein Assistent hat eine Sprache: Begrüßung, Einstiegsfragen, Fragen & Antworten und die Texte des Chatfensters stehen in dieser Sprache. Für jede weitere Sprache der Website einen eigenen Assistenten anlegen (kopieren, Sprache ändern) und im Chat-Modul dieser Sprache wählen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_none'] = 'Noch keine Sprache: Der Chat zeigt die Vorgabetexte und keine Einstiegsfragen.';
$GLOBALS['TL_LANG']['tl_neo_agent']['starters_none'] = 'Keine Einstiegsfragen';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_edit'] = 'Texte und Einstiegsfragen bearbeiten';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_add'] = 'Sprache hinzufügen';
$GLOBALS['TL_LANG']['tl_neo_agent']['languages_answer'] = 'Antwort:';
