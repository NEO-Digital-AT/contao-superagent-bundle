# SuperAgent for Contao 5

An AI chat assistant for Contao websites. A chat button opens a window in which visitors ask questions; a language model answers from the knowledge source of the website (its `llms.txt`, a text or a file) and the questions and answers entered in the back end. Name, image, model, answer style, texts per language, safety rules and forwarding to the team are set in the back end; the look is changed with CSS variables.

Free without a licence key as a chat with a credit line. A licence removes the credit line and adds, depending on the edition, page navigation, forwarding to the team and web search (Basic), tools of connected MCP servers and installed skills (Plus) and voice input (Pro) — see [Setup](docs/en/setup.md#1-editions-and-licence).

*Deutsche Fassung weiter unten: [Deutsch](#superagent-für-contao-5-deutsch).*

## Features

- Answers from the knowledge source and the questions & answers of each language; the window always says that visitors write with an AI (EU AI Act, Art. 50).
- Safety rules on by default: no medical advice, privacy mode that removes personal data before anything goes to the AI service.
- Texts per language, with suggestions in eleven languages; the conversation stays open across pages.
- The assistant decides with skills and tools: it opens pages, prefills the forwarding form, searches approved websites, uses tools of MCP servers and installed skills.
- Connection templates (Plus), shipped: apaleo, with a guest check before the tools of a reservation, setup steps and skills for booking, changes, payment links, cancellation and invoices; files from tools (e.g. an invoice) are downloaded in the chat, not passed to the AI service.
- Waits for consent of a consent tool; rate-limited endpoints; overridable template.
- The chat button shares its corner with the buttons of other extensions (cookie settings, accessibility): one settings button with a menu, or stacked one above the other or side by side, as set in the page layout (buttons at the edge of the screen of NEO Basic).

## Requirements

- Contao 5.3 to 6.0 with PHP 8.2 to 8.5, as far as the Contao version supports it (Contao 5.7 needs PHP 8.3, Contao 6.0 PHP 8.4), with the extensions `ctype`, `intl` and `mbstring`.
- `neo-digital/contao-basic-bundle`, installed automatically: AI connection, keys and licence on **Access keys and licences**.
- An account with an OpenAI-compatible AI provider; a working mailer for forwarding.

## Installation

### Contao Manager

1. Search for `neo-digital/contao-superagent-bundle`.
2. Click **Add** and then **Apply changes**.
3. Run the database update.

### Composer

```bash
composer require neo-digital/contao-superagent-bundle
php vendor/bin/contao-console contao:migrate
```

Then enter the AI connection under **NEO Digital › Access keys and licences** and confirm the terms: [Setup](docs/en/setup.md).

## Documentation

The full documentation comes with the extension: [docs/en/README.md](docs/en/README.md).

- [Installation](docs/en/installation.md) – requirements, updating, removing, back-end languages
- [Setup](docs/en/setup.md) – editions and licence, AI connection, terms, front-end module, mail sender, consent tool
- [Permissions](docs/en/permissions.md) – what editors need, what only administrators may do
- [User manual](docs/en/usage/README.md) – assistant, texts per language, the chat, connections, skills
- [Customising](docs/en/customising.md) – CSS variables, template, consent interface
- [How the assistant works](docs/en/how-it-works.md) – skills, tools, endpoints
- [Privacy](docs/en/privacy.md) – what goes where; [text for the privacy policy](docs/en/privacy-texts.md)
- [Troubleshooting](docs/en/troubleshooting.md)

## Development

The unit tests (PHPUnit 10.5 or 11) cover the licence editions and their terms, the privacy filter and the mail guard, the prompt and one reply of the assistant against a simulated AI service (tools, pages, forwarding form, fallback without tool calling), the chat endpoints, the MCP client, its OAuth sign-in and the tool rules, the web search on the approved sources, skills, the back-end decisions and the migrations; the database is SQLite in memory with the tables of the DCA files.

```bash
composer install
composer test
```

The CI workflow `.github/workflows/tests.yml` runs the tests on PHP 8.2 to 8.5 (PHP 8.2 with Contao 5.3 and PHPUnit 10.5). To test against an existing Contao installation instead, point `NEO_TEST_AUTOLOAD` to its `vendor/autoload.php`.

## License

**Licence keys:** <https://store.neo-digital.at/extensions/superagent>

Without a licence key the extension is free on any number of websites, with the features of the free edition; the chat window then shows the credit line, which must stay visible and unchanged. A licence key for the domain (**Access keys and licences › Licences**) removes the credit line and adds the features of its edition. The key is checked with the licence service when it is saved and once a day; only the key, the extension code, the domains of the website and the versions are sent, no visitor data. If the service cannot be reached, the last confirmation stays valid until its grace period ends. The version available at the time of the order (for free use: of the download) for the then current Contao long-term support version works with it until its end of life; there is no entitlement to versions for later long-term support versions. Support by separate agreement. Redistribution, resale and offering the software as your own product or service are not permitted. Full terms: [LICENSE](LICENSE).

---

# SuperAgent für Contao 5 (Deutsch)

Ein KI-Chat-Assistent für Contao-Websites. Ein Chat-Knopf öffnet ein Fenster, in dem Besucher Fragen stellen; ein Sprachmodell antwortet aus der Wissensquelle der Website (ihre `llms.txt`, ein Text oder eine Datei) und den im Backend eingetragenen Fragen und Antworten. Name, Bild, Modell, Antwortstil, Texte je Sprache, Sicherheitsregeln und Weiterleitung an das Team werden im Backend eingestellt; das Aussehen ändern CSS-Variablen.

Ohne Lizenzschlüssel kostenlos als Chat mit Hinweis. Eine Lizenz entfernt den Hinweis und bringt je nach Stufe Navigation, Weiterleitung an das Team und Internetsuche (Basic), Werkzeuge verbundener MCP-Server und installierte Skills (Plus) und Spracheingabe (Pro), siehe [Einrichtung](docs/de/setup.md#1-stufen-und-lizenz).

## Funktionen

- Antworten aus der Wissensquelle und den Fragen & Antworten jeder Sprache; das Fenster sagt immer, dass Besucher mit einer KI schreiben (EU-KI-Verordnung, Art. 50).
- Sicherheitsregeln ab Werk eingeschaltet: keine medizinischen Auskünfte, Datenschutz-Modus, der persönliche Daten entfernt, bevor etwas an den KI-Dienst geht.
- Texte je Sprache, mit Vorschlägen in elf Sprachen; das Gespräch bleibt über Seiten hinweg offen.
- Der Assistent entscheidet mit Skills und Werkzeugen: Er öffnet Seiten, befüllt das Weiterleiten-Formular vor, sucht auf freigegebenen Websites, nutzt Werkzeuge von MCP-Servern und installierte Skills.
- Vorlagen für Verbindungen (Plus), mitgeliefert: apaleo, mit Gastprüfung vor den Werkzeugen einer Reservierung, Schritten der Einrichtung und Skills für Buchung, Änderungen, Zahlungslinks, Stornierung und Rechnungen; Dateien aus Werkzeugen (z. B. eine Rechnung) werden im Chat heruntergeladen, nicht an den KI-Dienst gegeben.
- Wartet auf die Einwilligung eines Einwilligungswerkzeugs; Endpunkte mit Anfragegrenze; überschreibbares Template.
- Der Chat-Knopf teilt sich seine Ecke mit den Knöpfen anderer Erweiterungen (Cookie-Einstellungen, Barrierefreiheit): eine Einstellungs-Schaltfläche mit Menü oder übereinander oder nebeneinander gestapelt, wie im Seitenlayout eingestellt (Schaltflächen am Bildschirmrand von NEO Basic).

## Voraussetzungen

- Contao 5.3 bis 6.0 mit PHP 8.2 bis 8.5, soweit die Contao-Fassung sie unterstützt (Contao 5.7 braucht PHP 8.3, Contao 6.0 PHP 8.4), mit den Erweiterungen `ctype`, `intl` und `mbstring`.
- `neo-digital/contao-basic-bundle`, wird automatisch mitinstalliert: KI-Verbindung, Schlüssel und Lizenz unter **Zugänge und Lizenzen**.
- Ein Konto bei einem OpenAI-kompatiblen KI-Anbieter; ein funktionierender Mailversand für die Weiterleitung.

## Installation

### Contao Manager

1. Nach `neo-digital/contao-superagent-bundle` suchen.
2. Auf **Hinzufügen** und danach auf **Änderungen anwenden** klicken.
3. Die Datenbank aktualisieren.

### Composer

```bash
composer require neo-digital/contao-superagent-bundle
php vendor/bin/contao-console contao:migrate
```

Danach die KI-Verbindung unter **NEO Digital › Zugänge und Lizenzen** eintragen und die Bedingungen bestätigen: [Einrichtung](docs/de/setup.md).

## Dokumentation

Die vollständige Dokumentation liegt der Erweiterung bei: [docs/de/README.md](docs/de/README.md).

- [Installation](docs/de/installation.md) – Voraussetzungen, Aktualisieren, Entfernen, Backend-Sprachen
- [Einrichtung](docs/de/setup.md) – Stufen und Lizenz, KI-Verbindung, Bedingungen, Frontend-Modul, Absender, Einwilligungswerkzeug
- [Rechte](docs/de/permissions.md) – was Redakteure brauchen, was nur Administratoren dürfen
- [Bedienungsanleitung](docs/de/usage/README.md) – Assistent, Texte je Sprache, der Chat, Verbindungen, Skills
- [Anpassen](docs/de/customising.md) – CSS-Variablen, Template, Schnittstelle zur Einwilligung
- [So arbeitet der Assistent](docs/de/how-it-works.md) – Skills, Werkzeuge, Endpunkte
- [Datenschutz](docs/de/privacy.md) – was wohin geht; [Text für die Datenschutzerklärung](docs/de/privacy-texts.md)
- [Fehlersuche](docs/de/troubleshooting.md)

## Entwicklung

Die Unit-Tests (PHPUnit 10.5 oder 11) prüfen die Lizenzstufen und ihre Bedingungen, den Datenschutz-Filter und den Schutz der E-Mail-Adressen, die Anweisungen an das Modell und eine Antwort des Assistenten gegen einen nachgestellten KI-Dienst (Werkzeuge, Seiten, Weiterleitungsformular, Ablauf ohne Werkzeugaufrufe), die Endpunkte des Chats, den MCP-Client, seine OAuth-Anmeldung und die Regeln für Werkzeuge, die Websuche in den freigegebenen Quellen, Skills, die Entscheidungen im Backend und die Migrationen; die Datenbank ist SQLite im Arbeitsspeicher mit den Tabellen aus den DCA-Dateien.

```bash
composer install
composer test
```

Der CI-Ablauf `.github/workflows/tests.yml` führt die Tests unter PHP 8.2 bis 8.5 aus (PHP 8.2 mit Contao 5.3 und PHPUnit 10.5). Gegen eine bestehende Contao-Installation testen: `NEO_TEST_AUTOLOAD` auf deren `vendor/autoload.php` setzen.

## Lizenz

**Lizenzschlüssel:** <https://store.neo-digital.at/extensions/superagent>

Ohne Lizenzschlüssel ist die Erweiterung auf beliebig vielen Websites kostenlos, mit den Funktionen der Gratisfassung; das Chatfenster zeigt dann den Hinweis, der sichtbar und unverändert bleiben muss. Ein Lizenzschlüssel für die Domain (**Zugänge und Lizenzen › Lizenzen**) entfernt den Hinweis und schaltet die Funktionen seiner Stufe frei. Der Schlüssel wird beim Speichern und einmal täglich beim Lizenzdienst geprüft; übertragen werden nur der Schlüssel, der Code der Erweiterung, die Domains der Website und die Versionen, keine Daten von Besuchern. Ist der Dienst nicht erreichbar, gilt die letzte Bestätigung bis zum Ende ihrer Schonfrist weiter. Die bei der Bestellung (bei unentgeltlicher Nutzung: beim Herunterladen) für die dann aktuelle Long-Term-Support-Version von Contao bereitstehende Fassung funktioniert mit dieser bis zu deren End of Life; ein Anspruch auf Fassungen für spätere Long-Term-Support-Versionen besteht nicht. Support nach gesonderter Vereinbarung. Weitergabe, Weiterverkauf und das Anbieten als eigenes Produkt oder als Dienst sind nicht erlaubt. Vollständige Bedingungen: [LICENSE](LICENSE).
