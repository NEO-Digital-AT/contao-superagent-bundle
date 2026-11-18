---
titel: Einen Assistenten einrichten
kurzfassung: Jeder Abschnitt des Formulars eines SuperAgent-Assistenten – Veröffentlichung, Identität, Modell, Antwortstil, Wissensquelle, Sicherheit, Spracheingabe, Internetsuche, Vermittlung, Eskalation und Formular, Skills, Werkzeuge – und das Testen in der Vorschau, bevor Besucher ihn sehen.
zielgruppe: anwender
sprache: de
stand: 2026-11-18
---

# Einen Assistenten einrichten

## Einen Assistenten anlegen

1. **NEO Digital › SuperAgent** öffnen und auf **Neu** klicken (1 im Bild).
2. Die Abschnitte unten ausfüllen und speichern.
3. Ein neuer Assistent bekommt einen Sprachdatensatz in der ersten Sprache der
   Website, mit Vorschlägen gefüllt. Ein Assistent hat eine Sprache; für jede
   weitere Sprache der Website wird er kopiert (7) und die Sprache der Kopie
   geändert (siehe [Texte je Sprache](languages.md#mehrere-sprachen-auf-der-website)).

![Die Liste der Assistenten: Rahmen 1 bis 5 um die Knöpfe Neu, Zugänge und Lizenzen, Verbindungen, Skills und Protokoll über der Liste, Rahmen 6 und 7 um die Aktionen Bearbeiten und Kopieren des Assistenten „Ferienwohnung“.](../bilder/assistant-list.png)

Im Bild: 1 **Neu**, 2 **Zugänge und Lizenzen** (Lizenz, KI-Verbindung,
Bedingungen; siehe [Einrichtung](../setup.md)), 3 **Verbindungen** und 4
**Skills** (Plus, Administratoren), 5 **Protokoll** der Werkzeugaufrufe
(Administratoren), 6 den Assistenten **Bearbeiten**, 7 ihn **Kopieren**, etwa
für eine weitere Sprache.

Die Liste zeigt jeden Assistenten mit seiner Bezeichnung und, in Klammern,
seinem Anzeigenamen; ein Assistent in der Vorschau trägt die Markierung
*Vorschau*. Warnungen über der Liste nennen, was
fehlt: kein KI-Schlüssel, Bedingungen nicht bestätigt, eine Sprache der Website
ohne eigene Texte („Für … (Startpunkt „…“) hat noch kein Assistent eigene Texte;
…“, mit **Mit Vorschlägen anlegen bei „…“**).

## Die Abschnitte des Formulars

### Allgemein und Veröffentlichung

| Feld | Bedeutung |
| --- | --- |
| **Bezeichnung** | Interner Name im Backend |
| **Aktiv** | Nur aktive Assistenten erscheinen auf der Website |
| **Sichtbar für** | **Nur Vorschau – nur angemeldete Administratoren sehen den Chat in der Vorschau** (Vorgabe für neue Assistenten) oder **Alle Besucher**. *Alle Besucher* ist wählbar, sobald die Bedingungen bestätigt sind. |
| **Einwilligungs-Kategorie** | Die Kategorie des Cookie-Hinweises, nach deren Zustimmung der Chat erscheint: **Keine Einwilligung nötig (Chat sofort verfügbar)**, **Funktional**, **Statistik**, **Marketing** (siehe [Einrichtung](../setup.md#6-einwilligungswerkzeug)). Mit NEO Cookie Consent und ohne aktiven Dienst in der Kategorie sagt das Feld das in Rot, mit dem Link **Dienst jetzt anlegen** |

### Identität

| Feld | Bedeutung |
| --- | --- |
| **Anzeigename** | Wie sich der Assistent vorstellt und im Kopf erscheint, z. B. „Mia“ |
| **Bild** | Erscheint statt des Roboter-Icons (quadratisch, z. B. 96 × 96 px); jedes Bild aus der Dateiverwaltung, auch aus geschützten Ordnern |
| **Position** | **Unten rechts** oder **Unten links** für Chat-Knopf und Fenster. Sitzen Knöpfe anderer Erweiterungen in derselben Ecke (Cookie-Einstellungen, Barrierefreiheit), werden sie gemeinsam so angeordnet, wie es im Seitenlayout eingestellt ist: eine Einstellungs-Schaltfläche mit Menü, übereinander oder nebeneinander ([Der Chat](chat.md#fenster)). |


### Sprachen und Einstiegsfragen

Zeigt die Sprache des Assistenten mit ihren Einstiegsfragen (und deren Antwort,
wo es eine gibt), nur zum Lesen. **Texte und Einstiegsfragen bearbeiten** öffnet
den Sprachdatensatz, mit Begrüßung, Einstiegsfragen, Fragen & Antworten und den
Texten des Fensters. **Sprache hinzufügen** erscheint nur, solange der Assistent
keinen Sprachdatensatz hat. Einzelheiten:
[Texte je Sprache](languages.md#einstiegsfragen).

![Der obere Teil des Formulars eines Assistenten: Rahmen 1 bis 6 um Aktiv, Sichtbar für, Einwilligungs-Kategorie, Anzeigename, Bild und Position, Rahmen 7 um die Sprache Deutsch mit zwei Einstiegsfragen, Rahmen 8 um den Knopf Texte und Einstiegsfragen bearbeiten.](../bilder/assistant-form.png)

Im Bild: 1 **Aktiv**, 2 **Sichtbar für**, 3 **Einwilligungs-Kategorie**, 4
**Anzeigename**, 5 **Bild** (**Auswahl ändern** öffnet die Dateiverwaltung), 6
**Position**, 7 die Sprache mit ihren Einstiegsfragen (eine Antwort steht grau
hinter der Frage), 8 **Texte und Einstiegsfragen bearbeiten**.

### Modell

| Feld | Bedeutung |
| --- | --- |
| **Modell** | Das Sprachmodell, aus der Live-Liste der Route. Lässt sich die Liste nicht laden, bleibt das Feld ein Textfeld. Darunter zeigt das Backend, was das Modell kann: „kann denken“, „kann Werkzeuge nutzen“, „bis zu … Tokens je Antwort“. |
| **Denkaufwand** | Nur bei Modellen, die denken: **Aus**, **Niedrig (empfohlen)**, **Mittel**, **Hoch**. Mehr Denken ist genauer, langsamer und teurer. |
| **Temperatur** | Nur bei Modellen, die nicht denken: wie frei das Modell formuliert; 0,2 bis 0,5 passt für einen Chat (Vorgabe 0,4) |
| **Antwortlänge (Max. Tokens)** | 300, 500, 800 (Vorgabe), 1 200, 2 000 oder 4 000 Tokens; Platz zum Denken kommt dazu |

Ein Modell ohne Werkzeuge zeigt: „Dieses Modell kann keine Werkzeuge nutzen. Die
Internetsuche funktioniert damit nicht; Navigation und Weiterleitung laufen über
Ersatzzeilen.“

![Der Abschnitt Modell: Rahmen 1 um das Feld Modell, Rahmen 2 um die Zeile darunter mit dem, was das Modell kann, und dem Hinweis, ob es mit Werkzeugen von Verbindungen getestet ist, Rahmen 3 um Temperatur, Rahmen 4 um Antwortlänge (Max. Tokens).](../bilder/assistant-model.png)

Im Bild: 1 **Modell**, 2 was das Modell kann und ob es zu den mit Werkzeugen von
Verbindungen getesteten Modellen gehört, 3 **Temperatur** (bei Modellen, die
denken: **Denkaufwand**), 4 **Antwortlänge (Max. Tokens)**.

### Persönlichkeit & Antwortstil

| Feld | Auswahl |
| --- | --- |
| **Anredeform** | **Förmlich (Sie)**, **Locker (du)** |
| **Antwortlänge** | **Kurz (2–3 Sätze)**, **Mittel (4–6 Sätze)**, **Ausführlich (bei Bedarf mit Aufzählung)** |
| **Tonfall** | **Professionell & sachlich**, **Freundlich & zugewandt**, **Fachlich & präzise** |

### Wissensbasis

**Wissensquelle**:

| Auswahl | Was der Assistent weiß |
| --- | --- |
| **llms.txt der Website (empfohlen)** | `llms-full.txt` im Web-Verzeichnis, sonst `llms.txt` (z. B. von der Erweiterung NEO LLMS geschrieben) |
| **llms.txt + eigener Zusatztext** | dasselbe, dazu der **Wissenstext** |
| **Text (eingegeben)** | der **Wissenstext** |
| **Datei aus der Dateiverwaltung** | die **Wissensdatei** (`txt`, `md`, `markdown`, `json`) |
| **Keine** | nur die Fragen & Antworten der Sprachdatensätze |

Die Fragen & Antworten des Sprachdatensatzes sind ebenfalls Wissen für das
Modell. Jede Antwort kommt vom Modell; ist der KI-Dienst nicht erreichbar,
zeigt das Fenster seinen Fehlerhinweis statt einer gespeicherten Antwort.

### Sicherheit

| Feld | Vorgabe | Bedeutung |
| --- | --- | --- |
| **Keine medizinischen Auskünfte** | an | Der Assistent lehnt Fragen zu Diagnosen, Behandlung, Medikamenten oder Gesundheitstipps in eigenen Worten ab, nach der Vorgabe im Sprachdatensatz (z. B. die Notrufnummer). |
| **Datenschutz-Modus** | an | Der Chat bittet, keine persönlichen Daten einzugeben. Der Server entfernt E-Mail-Adressen, Telefonnummern, IBANs, Kartennummern, österreichische Sozialversicherungsnummern und Geburtsdaten, bevor etwas an den KI-Dienst geht, auch aus früheren Nachrichten; der Browser behält nur die bereinigte Fassung. Gesundheitsangaben und andere Daten, die sich nicht an ihrer Form erkennen lassen, überlässt er einer Regel für das Modell. Ein Ort, ein Land oder eine Adresse ohne den Namen einer Person sind keine persönlichen Daten (z. B. eine Frage zum Wetter). Funktionen, die persönliche Daten brauchen (z. B. eine Buchung), sind dann nicht möglich. |

Eine Regel für ein Sprachmodell schließt nicht jeden Fehler aus.

![Die Abschnitte Persönlichkeit & Antwortstil, Wissensbasis und Sicherheit: Rahmen 1 bis 3 um Anredeform, Antwortlänge und Tonfall, Rahmen 4 um Wissensquelle, Rahmen 5 und 6 um Keine medizinischen Auskünfte und Datenschutz-Modus.](../bilder/assistant-answers.png)

Im Bild: 1 **Anredeform**, 2 **Antwortlänge**, 3 **Tonfall**, 4
**Wissensquelle**, 5 **Keine medizinischen Auskünfte**, 6 **Datenschutz-Modus**.

### Spracheingabe (Pro)

| Feld | Bedeutung |
| --- | --- |
| **Spracheingabe erlauben** | Ein Mikrofon-Knopf neben der Eingabe; Besucher sprechen höchstens 60 Sekunden |
| **Umschrift-Modell** | Aus der Liste der bei der Route freigegebenen Umschrift-Modelle (Vorgabe `mistral/voxtral-mini-latest`). Bei Requesty zuerst ein Modell unter *Transcriptions* freigeben; die Modelle von Mistral (Voxtral) und Scaleway verarbeiten in der EU. |
| **Spracheingabe auch im Datenschutzmodus** | Solange der Datenschutz-Modus an ist, gibt es keine Spracheingabe, und beide Felder oben sind ausgegraut, mit dem Grund daneben. Dieser Schalter erlaubt sie trotzdem: Die Aufnahme geht ungefiltert an den Umschrift-Dienst; gefiltert wird nur der Text. |

### Internetsuche (Basic)

| Feld | Bedeutung |
| --- | --- |
| **Internetsuche erlauben** | Der Assistent darf im Internet nachsehen, nur auf den freigegebenen Quellen |
| **Suchdienst** | **Nur die freigegebenen Quellen lesen (kostenlos)**: Die eingetragenen Adressen werden direkt gelesen. **Über SerpAPI suchen — eingeschränkt auf die freigegebenen Domains**: Eine Suchmaschine findet Seiten, nur auf den freigegebenen Domains; braucht den SerpAPI-Schlüssel (die Statuszeile sagt, ob er gespeichert ist). |
| **Freigegebene Quellen** | Eine Adresse je Zeile: eine Partner- oder Herstellerseite, deren `llms.txt`, ein Datenendpunkt. Beantwortet die Startseite die Frage nicht, folgt der Assistent passenden Unterseiten. |
| **Quellen je Frage** | 1 bis 5 (Vorgabe 3) |

Die Felder, die zu einem Schalter gehören, erscheinen, sobald er angehakt ist
(das Formular lädt neu).

![Die Abschnitte Spracheingabe und Internetsuche: Rahmen 1 bis 3 um Spracheingabe erlauben, Umschrift-Modell und Spracheingabe auch im Datenschutzmodus, Rahmen 4 bis 7 um Internetsuche erlauben, Suchdienst, Freigegebene Quellen mit zwei Adressen und Quellen je Frage.](../bilder/assistant-voice-search.png)

Im Bild: 1 **Spracheingabe erlauben**, 2 **Umschrift-Modell**, 3 **Spracheingabe
auch im Datenschutzmodus**, 4 **Internetsuche erlauben**, 5 **Suchdienst**, 6
**Freigegebene Quellen** (eine Adresse je Zeile; die Knöpfe neben einer Zeile
kopieren oder löschen sie), 7 **Quellen je Frage**.

### Vermittlung

| Feld | Bedeutung |
| --- | --- |
| **Immer verweisen auf** | Bei Einrichtung, Umsetzung, Integration, Migration, Betreuung und Kauf verweist der Assistent auf diesen Anbieter, nie auf den Hersteller einer Fremdlösung |
| **Bevorzugte eigene Lösung** | Wird zuerst genannt, wenn sie zur Frage passt; leer = keine Vorliebe |
| **Wofür die Lösung passt** | Ein bis zwei Sätze, damit der Assistent sie nur nennt, wenn sie passt |

### Eskalation & Einwilligung (Basic)

| Feld | Bedeutung |
| --- | --- |
| **Eskalations-E-Mail** | Adresse für weitergeleitete Anfragen. Leer: Der Assistent bietet keine Weiterleitung an und verweist auf die Kontaktwege der Website. |
| **Weiterleiten an (Name)** | Steht auf den Knöpfen im Chat, z. B. „An Max weiterleiten“. Leer: „An das Team“. |
| **Kontaktseite** | Diese Seite kann der Assistent öffnen, wenn ein Besucher eine Person erreichen will, zusätzlich zum Formular |
| **Zusätzliche Felder im Formular** | **Name**, **Telefon**, **Firma**. E-Mail-Adresse und Nachricht stehen immer im Formular und sind Pflicht. |
| **Davon Pflichtfelder** | Nur Felder, die links gewählt sind |
| **Vorgewählte Ländervorwahl** | Für das Telefonfeld; leer = nach der Sprache der Seite |
| **Einwilligung zur Kontaktaufnahme verlangen** | Ein Pflicht-Häkchen im Formular (bei neuen Assistenten an); die E-Mail an das Team vermerkt die Einwilligung |
| **Datenschutzerklärung** | Die Seite, auf die das Häkchen verlinkt; leer = ohne Link |
| **Newsletter-Häkchen** | **Aus** oder ein Newsletter-Modul der Website: ein Modul der Brevo-Erweiterung oder Contaos Modul *Abonnieren* (angeboten wird nur, was installiert ist). Es gelten dessen Listen oder Verteiler und dessen Bestätigungs-E-Mail. |

Ohne Eskalationsadresse sind die Felder des Formulars ausgegraut: „Wirkt nur mit
einer E-Mail-Adresse für die Weiterleitung oben: Ohne sie hat der Chat kein
Formular zum Weiterleiten.“ Probleme des gewählten Newsletter-Moduls stehen am
Feld, z. B. „Das gewählte Modul „Newsletter abonnieren“ steht auf keiner
veröffentlichten Seite. …“; bis sie behoben sind, zeigt das Formular kein
Newsletter-Häkchen.

![Der Abschnitt Eskalation & Einwilligung: Rahmen 1 bis 9 um Eskalations-E-Mail, Weiterleiten an (Name), Kontaktseite, Zusätzliche Felder im Formular mit angehaktem Name und Telefon, Davon Pflichtfelder, Vorgewählte Ländervorwahl, Einwilligung zur Kontaktaufnahme verlangen, Datenschutzerklärung und Newsletter-Häkchen.](../bilder/assistant-escalation.png)

Im Bild: 1 **Eskalations-E-Mail**, 2 **Weiterleiten an (Name)**, 3
**Kontaktseite** und 8 **Datenschutzerklärung** (**Auswahl ändern** öffnet den
Seitenbaum), 4 **Zusätzliche Felder im Formular**, 5 **Davon Pflichtfelder**, 6
**Vorgewählte Ländervorwahl**, 7 **Einwilligung zur Kontaktaufnahme
verlangen**, 9 **Newsletter-Häkchen**. Was Besucher davon sehen:
[Der Chat](chat.md#weiterleiten-an-das-team-basic).

### Installierte Skills und Werkzeuge (Plus)

- **Skills**: welche installierten Skills der Assistent benutzen darf; ab Werk
  ist nichts angehakt ([Installierte Skills](skills.md)).
- **Werkzeuge**: welche Werkzeuge der Verbindungen der Assistent benutzen darf,
  je Verbindung; ab Werk ist nichts angehakt, gesperrte Werkzeuge erscheinen
  nicht ([Verbindungen zu MCP-Servern](connections.md)). Hinweise unter der
  Liste sagen, wenn der Datenschutz-Modus an ist und ein angehaktes Werkzeug
  Angaben zu einer Person verlangt, wenn das Modell keine Werkzeuge aufrufen kann und
  wie viele angehakte Werkzeuge nicht nutzbar sind.

![Die Abschnitte Installierte Skills und Werkzeuge der Verbindungen: Rahmen 1 um Skills mit einem installierten Skill, Rahmen 2 um Werkzeuge mit der aufgeklappten Verbindung Termine und ihrem angehakten Werkzeug list_free_slots und der zugeklappten Verbindung Open-Meteo, Rahmen 3 um den Hinweis zum Sprachmodell.](../bilder/assistant-tools.png)

Im Bild: 1 **Skills**, 2 **Werkzeuge**, nach Verbindung gruppiert (ein Klick auf
den Namen einer Verbindung zeigt oder verbirgt ihre Werkzeuge), 3 der Hinweis
zum Sprachmodell (siehe [Welches Sprachmodell](connections.md#welches-sprachmodell)).

### Experten-Einstellungen

**Zusätzliche Anweisungen (optional)**: eigene Anweisungen an das Modell, nur
für Sonderfälle; Anrede, Länge und Tonfall sind oben eingestellt. `{name}`
steht für den Anzeigenamen.

## In der Vorschau testen

1. **Sichtbar für** auf **Nur Vorschau** und **Aktiv** an lassen.
2. Als Administrator im Backend anmelden und die Website über Contaos
   **Vorschau** öffnen.
3. Den Chat öffnen, Fragen stellen, Weiterleiten und Navigation ausprobieren.

Besucher sehen einen Assistenten in der Vorschau nicht, und die Endpunkte des
Chats behandeln ihn für sie als nicht vorhanden.

## Sichtbar schalten

1. Die Bedingungen unter *Zugänge und Lizenzen* bestätigen (siehe
   [Einrichtung](../setup.md#3-bedingungen)).
2. **Sichtbar für** auf **Alle Besucher** stellen und speichern.
