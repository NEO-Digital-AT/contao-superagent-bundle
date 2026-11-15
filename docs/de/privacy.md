---
titel: Datenschutz
kurzfassung: Was der Chat von SuperAgent wohin schickt und was bleibt – Nachrichten, Weiterleitung, Newsletter, Werkzeuge der Verbindungen, Spracheingabe, IP-Adresse, Schlüssel – und was in die Datenschutzerklärung der Website gehört.
zielgruppe: betrieb
sprache: de
stand: 2026-11-12
---

# Datenschutz

## Was wohin geht

| Daten | Gehen an | Bleiben |
| --- | --- | --- |
| Nachrichten, bisheriges Gespräch, Adresse, Titel und Sprache der Seite | den Server der Website → den KI-Dienst (Route unter *Zugänge und Lizenzen*) → den Anbieter des Modells | nicht auf dem Server der Website; beim KI-Dienst nach dessen Bedingungen |
| Suchbegriffe aus der Frage (nur Internetsuche über SerpAPI) | SerpAPI | nach dessen Bedingungen |
| E-Mail-Adresse, Nachricht und Gespräch, dazu Name, Telefon, Firma und Einwilligung, wenn das Formular danach fragt (nur wenn der Besucher weiterleitet) | die Eskalationsadresse, per E-Mail | in diesem Postfach |
| E-Mail-Adresse für den Newsletter (nur wenn der Besucher das Häkchen setzt) | das am Assistenten gewählte Newsletter-Modul (Brevo oder Contaos Newsletter), Double-Opt-in | nach diesem Newsletter |
| Gespräch für die nächste Seite | den Tab des Besuchers im Browser (`sessionStorage`, nur nach Einwilligung) | bis der Tab geschlossen wird oder **Neues Gespräch** |
| Gedächtnis des Gesprächs: Ergebnisse der Werkzeuge von Verbindungen und die geladenen Skills (nur Werkzeuge von Verbindungen, Plus) | mit der Antwort als verschlüsseltes Kennzeichen in den Tab des Besuchers (`sessionStorage`), mit der nächsten Nachricht zurück an den Server, von dort mit den Anweisungen an den KI-Dienst; der Besucher kann es nicht lesen | nicht auf dem Server; im Tab, bis er geschlossen wird oder **Neues Gespräch**; gilt nach zwei Stunden als leer |
| Persönliche Daten in Nachrichten, ohne Datenschutz-Modus | der Server prüft die Nachricht vor dem Aufruf des KI-Dienstes; mit E-Mail-Adresse, Telefonnummer, IBAN, Kartennummer, Sozialversicherungsnummer oder Geburtsdatum fragt das Fenster zuerst den Besucher; erst nach **Ja, senden** geht die Nachricht so weiter, wie sie geschrieben ist | für die Rückfrage wird nichts aufbewahrt; danach wie jede Nachricht |
| Persönliche Daten in Nachrichten, mit Datenschutz-Modus | auf dem Server vor dem Aufruf des KI-Diensts entfernt, auch aus früheren Nachrichten; das Protokoll nennt nur die Art, z. B. `email` | der Browser behält die bereinigte Nachricht |
| Angaben, die ein Werkzeugaufruf braucht, und sein Ergebnis (nur Werkzeuge der Verbindungen, Plus) | den Server → den MCP-Server der Verbindung; das Ergebnis → den KI-Dienst; Kennungen je Verbindung maskiert oder zurückgehalten | bei diesem Dienst nach dessen Bedingungen; das Protokoll der Werkzeuge behält nur Assistent, Verbindung, Werkzeug, Zeit, Dauer und Ergebnis, 30 Tage lang |
| Sprachaufnahme (nur Spracheingabe, Pro; mit Datenschutz-Modus nur mit **Spracheingabe auch im Datenschutzmodus**) | den Server → den KI-Dienst → den Anbieter des Umschrift-Modells, ungefiltert; der Text kommt ins Eingabefeld zurück, mit Datenschutz-Modus zuerst gefiltert | nicht auf dem Server (nur vorübergehende Upload-Datei); beim KI-Dienst nach dessen Bedingungen |
| IP-Adresse des Besuchers | nur den Server der Website, um die Anfragen je Minute zu begrenzen | eine Minute, als Hash im Cache der Anwendung |
| Gastprüfung (nur Verbindungen, deren Vorlage eine hat): Reservierungsnummer, Name und E-Mail oder Telefon, die der Besucher nennt | der Server vergleicht sie mit dem Datensatz, den das Buchungssystem liefert; das Modell erfährt nur, ob sie passen | nichts aufbewahrt; Fehlversuche werden 24 Stunden lang unter einem Hash aus IP-Adresse und Assistent gezählt; was bestätigt ist, reist mit dem Gespräch als signiertes Kennzeichen (zwei Stunden) |
| Eine Datei, die ein Werkzeug liefert, z. B. eine Rechnung (nur Werkzeuge von Verbindungen, Plus) | der Server → der Browser des Besuchers, per Download; nicht an den KI-Dienst (das Modell erfährt nur, dass die Datei bereitliegt) | 15 Minuten im Anwendungs-Cache unter einem zufälligen Kennzeichen, beim ersten Download gelöscht |
| Ereignis „Chat gestartet“ (nur wenn im Frontend-Modul gesetzt, nur mit Statistik-Einwilligung) | Google Analytics: nur der Name des Ereignisses, kein Inhalt | nach Google Analytics |
| Lizenzschlüssel, Code der Erweiterung, Domains der Website, Versionen (nur mit Lizenzschlüssel, beim Speichern und einmal täglich) | den Lizenzdienst | Daten von Besuchern werden nicht übertragen |

Die IP-Adresse des Besuchers geht nicht an den KI-Dienst; die Anfrage stellt
der Server der Website. Freigegebene Websites, die die Internetsuche liest,
bekommen keine Daten von Besuchern. Lädt ein Administrator einen Skill, wird
GitHub nur aus dem Backend erreicht, ohne Daten von Besuchern. API-Schlüssel,
Werte von Headern und Tokens werden verschlüsselt gespeichert (libsodium, mit
dem App-Secret als Schlüssel).

## Wofür der Betreiber zuständig ist

Die Bedingungen der Erweiterung (bestätigt unter *Zugänge und Lizenzen*) sagen:
Der Betreiber ist für die Datenschutzerklärung, die Einwilligung der Besucher
und den Auftragsverarbeitungsvertrag mit dem KI-Dienst verantwortlich. Die
mitgelieferten Texte sind Vorlagen, keine Rechtsberatung.

- Mit dem KI-Dienst einen Auftragsverarbeitungsvertrag schließen (Art. 28
  DSGVO) und die Verarbeitung ins Verzeichnis der Verarbeitungstätigkeiten
  aufnehmen.
- Die **Einwilligungs-Kategorie** jedes Assistenten passend zum Cookie-Banner
  und zur Datenschutzerklärung wählen.
- Den **Datenschutz-Modus** eingeschaltet lassen, außer ein Assistent braucht
  persönliche Daten, z. B. für ein Werkzeug einer Verbindung.

## Datenschutzerklärung

Ein Textbaustein für die Datenschutzerklärung in elf Sprachen, mit den zu
ersetzenden Platzhaltern und den Sätzen, die wegfallen können, steht unter
[Text für die Datenschutzerklärung](privacy-texts.md).
