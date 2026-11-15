---
titel: So arbeitet der Assistent
kurzfassung: Wie SuperAgent eine Antwort erzeugt – Modell, Skills und Werkzeuge, was das Modell bekommt, Katalog zuerst und Inhalte auf Abruf, die Grenze der Schritte, Modelle ohne Werkzeugaufrufe und die drei Endpunkte des Chatfensters.
zielgruppe: betrieb
sprache: de
stand: 2026-11-12
---

# So arbeitet der Assistent

## Modell, Skills, Werkzeuge

Das Modell entscheidet, was als Nächstes geschieht; die Skills sagen, wie; die
Werkzeuge führen es aus. Die Erweiterung führt die Schleife, führt Werkzeuge im
Rahmen der Lizenz und der Einstellungen aus und schreibt dem Besucher selbst
nichts.

Die Regeln sind kleine Skill-Dateien (`skills/` des Pakets, englisch): ein Kern,
der immer gilt, und je ein Skill pro Bereich (Kontakt, Navigation,
Internetsuche, Vermittlung, Medizin, Datenschutz). Installierte Skills (Stufe
Plus) kommen dazu (siehe [Installierte Skills](usage/skills.md)).

## Was das Modell bekommt

- den Kern-Skill;
- SETTINGS: Name, Anrede, Antwortlänge, Tonfall, was verfügbar ist, die
  Vorgaben des Sprachdatensatzes und die **Zusätzlichen Anweisungen** des
  Assistenten;
- STATE: Datum, Seite, Sprache der Seite;
- den CATALOG: eine Zeile je noch nicht geladenem Skill und je Werkzeug;
- die bisher geladenen Skills;
- die Liste der Seiten (bis zu 150; eine größere Website bekommt stattdessen
  ein Werkzeug, das die Liste durchsucht);
- die Fragen & Antworten des Sprachdatensatzes, die Einstiegsfragen mit
  Antwort und die Wissensquelle.

## Katalog zuerst, Inhalte auf Abruf

Das Modell lädt einen Skill mit `load_module`, bevor es in dessen Bereich
handelt; die Werkzeuge des Skills kommen mit. Eine einzelne
Werkzeugdefinition lädt es mit `load_tools`. Die Skills für medizinische Fragen
und für Datenschutz gelten ab dem ersten Schritt, wenn sie im Assistenten
eingeschaltet sind.

Höchstens `neo_super_agent.max_steps` Modellaufrufe je Antwort (Vorgabe 12;
Laden zählt mit). Ein Schritt, der nur erneut lädt, was schon geladen ist, zählt
bis zu dreimal je Antwort nicht mit: Manche Modelle laden jedes Werkzeug einmal
vor dem ersten Aufruf. Im letzten Schritt bekommt das Modell keine Werkzeuge und
schreibt seine Antwort.

## Gedächtnis des Gesprächs

Jede Nachricht ist eine neue Anfrage. Damit der Assistent nicht erneut fragt,
was er schon herausgefunden hat, trägt die Antwort ein Gedächtnis: die
Ergebnisse der Werkzeuge von Verbindungen (die neuesten zwölf, jedes bei
4 000 Zeichen abgeschnitten, zusammen höchstens 24 000) und die geladenen Skills
und Werkzeuge. Die nächste Antwort beginnt mit diesen Skills und Werkzeugen und
bekommt die Ergebnisse im Abschnitt EARLIER TOOL RESULTS ihrer Anweisungen; eine
Buchung aus dem Gespräch ist dort bekannt.

Auf dem Server wird davon nichts gespeichert: Das Gedächtnis reist mit dem
Gespräch als Kennzeichen, verschlüsselt und gesichert mit dem Anwendungsschlüssel,
wie das Kennzeichen der Gastprüfung. Das Fenster hält es im `sessionStorage` und
schickt es mit der nächsten Nachricht; **Neues Gespräch** verwirft es. Ein
Kennzeichen eines anderen Assistenten, ein verändertes oder eines, das älter als
zwei Stunden ist, gilt als leer.

Die Skill-Dateien werden beim Lesen geprüft (Kopf, höchstens 2 800 Zeichen je
Skill, bekannte Werkzeuge und Abhängigkeiten). Ein ungültiges Paket hält den
Chat an, statt mit der Hälfte seiner Regeln zu laufen.

## Modelle ohne Werkzeugaufrufe

Modelle, die keine Werkzeuge aufrufen können, bekommen alle Skills auf einmal
und setzen Markierungszeilen ans Ende der Antwort (`[[HANDOFF: …]]`,
`[[PAGE: id now|offer]]`). Der Server macht daraus dieselben Aktionen und
entfernt sie, bevor der Besucher die Antwort sieht. Die Internetsuche
funktioniert mit einem solchen Modell nicht.

## Endpunkte

Das Chatfenster spricht mit drei Adressen. Jede ist auf 20 Anfragen je Minute
und IP-Adresse begrenzt und prüft und begrenzt jede Eingabe. Ein Assistent auf
*Nur Vorschau* beantwortet nur Anfragen, die die Sitzung eines angemeldeten
Backend-Administrators tragen.

### `POST /api/superagent/chat`

Anfrage: `{message, confirmed?, guest?, memory?, history?, lang?, agent?, page?: {url, title, root}}`;
Nachrichten des Besuchers werden bei 2 000 Zeichen abgeschnitten, Antworten des
Assistenten im Verlauf bei 4 000, der Verlauf bei 30 Einträgen (was der
Assistent sagte, während seine Werkzeuge liefen, gehört dort zu seiner Antwort).

Ohne Datenschutz-Modus sucht der Server zuerst nach E-Mail-Adresse,
Telefonnummer, IBAN, Kartennummer, Sozialversicherungsnummer oder Geburtsdatum
in der Nachricht. Findet er etwas und ist `confirmed` nicht `true`, antwortet er
`{confirm: ["email", "phone", …]}` (die gefundenen Arten), ruft keinen KI-Dienst
auf und speichert nichts; das Fenster zeigt seinen Hinweis und sendet die
Nachricht erst mit `confirmed: true` erneut, wenn der Besucher bestätigt.

Antwort: `{reply, handoff, navigate, message?}`, oder `503 {"error":
"unavailable", message?}`, wenn der KI-Dienst keine Antwort gibt.

- `handoff`: Text zum Vorbefüllen des Weiterleiten-Formulars, oder `null`;
- `navigate`: `{url, title, now}`, wenn der Assistent eine Seite öffnet (`now:
  true`) oder anbietet (`now: false`);
- `message`: die Nachricht des Besuchers so, wie sie an den KI-Dienst ging, wenn
  der Datenschutz-Modus persönliche Daten entfernt hat; das Skript behält diese
  Fassung;
- `guest`: was der Besucher mit der Gastprüfung einer Verbindung nachgewiesen hat,
  ein vom Server signiertes Kennzeichen; das Skript behält es mit dem Gespräch und
  schickt es als `guest` zurück;
- `files`: `[{url, name, size}]`, wenn ein Werkzeug eine Datei geliefert hat (ein
  PDF, z. B. eine Rechnung); das Fenster zeigt je Datei einen Download-Knopf. Das
  Modell bekommt die Datei nie.

Mit `Accept: text/event-stream` (so sendet es das Fenster) kommt die Antwort als
Stream: Ereignisse `step` (`thinking`, `pages`, `form`, `search`), dann `done`
mit demselben Objekt, oder `error`. Fehler vor der Antwort (Grenze, kein
Assistent) bleiben JSON. Die Antwort setzt `X-Accel-Buffering: no` für nginx;
bei einem Hosting, das Antworten puffert, kommen die Schritte mit der Antwort,
und der Chat funktioniert genauso.

### `GET /api/superagent/file/{token}`

Eine Datei des Chats, einmal: Die Antwort liefert die Datei als Anhang
(`Cache-Control: no-store`); die Datei wird dabei gelöscht. Ein benutztes oder
unbekanntes Kennzeichen oder eines, das älter als 15 Minuten ist, bekommt `410`
mit einem kurzen Hinweis in der Sprache von `?lang=`. Das Kennzeichen (48
hexadezimale Zeichen) ist der einzige Schlüssel.

### `POST /api/superagent/escalate`

Anfrage: `{email, message, name?, phone?, company?, consent?, newsletter?,
transcript?, lang?, agent?}`; die Nachricht wird bei 4 000 Zeichen
abgeschnitten, das Gesprächsprotokoll bei zwölf Runden.

Antwort: `{ok: true}`; `400 {"error": "missing"|"invalid", fields: […]}`, wenn
ein Pflichtfeld leer oder die Telefonnummer keine ist; `403 {"error":
"licence"}` ohne eine Lizenz, die Kontakt umfasst.

### `POST /api/superagent/transcribe`

Anfrage: Formulardaten `audio` (WebM, Ogg, MP4/M4A, AAC, MP3 oder WAV,
höchstens 4 MB), `agent?`, `lang?`.

Antwort: `{text}` (im Datenschutz-Modus mit Markierungen wie `[removed: e-mail
address]` statt persönlicher Daten); `403 {"error": "disabled"}`, wenn die
Spracheingabe aus ist oder der Datenschutz-Modus an ist ohne **Spracheingabe
auch im Datenschutzmodus**; `403 {"error": "licence"}` ohne Pro; `503
{"error": "unavailable"}`, wenn die Umschrift scheitert.
