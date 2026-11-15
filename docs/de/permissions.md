---
titel: Rechte
kurzfassung: Welche Contao-Rechte eine Benutzergruppe für die Assistenten, ihre Sprachdatensätze, das Frontend-Modul und die Schlüssel von SuperAgent braucht, und was nur Administratoren dürfen (Vorschau, Verbindungen, Skills, Protokoll).
zielgruppe: betrieb
sprache: de
stand: 2026-11-12
---

# Rechte

Administratoren können alles ohne weitere Einstellung. Für andere Benutzer
werden alle Rechte im Backend unter **Benutzergruppen** (oder **Benutzer**)
gesetzt, mit den Bezeichnungen, die Contao dort zeigt. Die Erweiterung hat
keine eigenen Schalter.

## Nur für Administratoren

Einige Teile sind Administratoren vorbehalten, gleich was die Benutzergruppe
erlaubt:

| Was | Warum |
| --- | --- |
| **Verbindungen**, **Skills**, **Protokoll** (Schaltflächen über der Liste) | Über eine Verbindung arbeitet jeder Besucher mit dem Zugang eines anderen Systems; ein Skill ändert die Regeln des Modells. Andere Benutzer bekommen „Zugriff verweigert“ und sehen die Schaltflächen nicht. |
| Einen Assistenten auf **Nur Vorschau** sehen | Nur ein angemeldeter Backend-Administrator sieht den Chat in Contaos Vorschau, und die Endpunkte des Chats antworten nur mit der Sitzung eines Administrators. Redakteure können einen Assistenten deshalb nicht testen, bevor er für alle Besucher sichtbar ist. |

## Je Funktion

| Funktion | Contao-Recht | Einstellung |
| --- | --- | --- |
| Liste der Assistenten öffnen | **Backend-Module** | *NEO Digital*: **SuperAgent** |
| Assistenten anlegen, bearbeiten, löschen | **Erlaubte Felder**, Tabelle `tl_neo_agent` | die Felder unten |
| | **Tabellenrechte** (ab Contao 5.7), `tl_neo_agent` | Anlegen, Bearbeiten, Löschen nach Bedarf |
| Sprachdatensätze (**Sprachen**; im Formular des Assistenten **Texte und Einstiegsfragen bearbeiten**, **Sprache hinzufügen**), **Mit Vorschlägen anlegen bei „…“** | **Erlaubte Felder**, Tabelle `tl_neo_agent_language` | die Felder unten |
| | **Tabellenrechte** (ab Contao 5.7), `tl_neo_agent_language` | Anlegen, Bearbeiten, Löschen nach Bedarf |
| **Bild** wählen | **Filemounts** | die Ordner mit den Bildern |
| **Wissensdatei** wählen | **Filemounts** | der Ordner mit der Datei |
| Frontend-Modul anlegen | **Backend-Module** | *Layout*: **Themes** |
| | **Theme-Module** | **Frontend-Module** |
| | **Frontend-Module** | **KI-Assistent (Chat-Widget)** |
| | **Erlaubte Felder**, Tabelle `tl_module` | Assistent `[agent]`, GA4-Ereignis „Chat gestartet“ `[nsa_ga_event]`, dazu Contaos Felder des Moduls |
| Ins Seitenlayout einbinden | **Theme-Module** | **Seitenlayouts** |
| KI-Verbindung, SerpAPI-Schlüssel, Lizenzschlüssel, Bedingungen | **Backend-Module** | *NEO Digital*: **Zugänge und Lizenzen** |
| | **Erlaubte Felder**, Tabelle `tl_neo_access` | die Felder der KI-Verbindung, SerpAPI-Schlüssel `[superagent_serpapi_key]`, die Lizenzzeile und die Bedingungen von SuperAgent (siehe Dokumentation des Basic-Bundles) |
| | **Tabellenrechte** (ab Contao 5.7), `tl_neo_access` | Bearbeiten |
| **Dienst jetzt anlegen** an **Einwilligungs-Kategorie** (legt den KI-Chat in NEO Cookie Consent an) | **Backend-Module** | *NEO Digital*: **Cookie-Hinweis**; ohne das nennt das Feld statt des Links einen Administrator |

Wie bei Contaos eigenen Rechten bekommt ein Benutzer die Rechte des Benutzers,
die der Gruppen oder beide, je nach **Rechtevererbung**.

## Die Felder

| Tabelle | Felder |
| --- | --- |
| `tl_neo_agent` | Bezeichnung `[title]`, Aktiv `[published]`, Sichtbar für `[visibility]`, Einwilligungs-Kategorie `[consent_category]`, Anzeigename `[assistant_name]`, Bild `[avatar]`, Position `[widget_side]`, Modell `[model]`, Denkaufwand `[reasoning_effort]`, Temperatur `[temperature]`, Antwortlänge (Max. Tokens) `[max_tokens]`, Anredeform `[tone_address]`, Antwortlänge `[answer_length]`, Tonfall `[tone_style]`, Wissensquelle `[grounding_source]`, Wissenstext `[grounding_text]`, Wissensdatei `[grounding_file]`, Keine medizinischen Auskünfte `[no_medical]`, Datenschutz-Modus `[privacy_mode]`, Spracheingabe erlauben `[voice_input]`, Umschrift-Modell `[transcription_model]`, Spracheingabe auch im Datenschutzmodus `[voice_in_privacy]`, Internetsuche erlauben `[web_search]`, Suchdienst `[web_search_mode]`, Freigegebene Quellen `[web_search_sources]`, Quellen je Frage `[web_search_max]`, Immer verweisen auf `[referral_company]`, Bevorzugte eigene Lösung `[preferred_solution]`, Wofür die Lösung passt `[preferred_solution_note]`, Eskalations-E-Mail `[escalation_email]`, Weiterleiten an (Name) `[escalation_name]`, Kontaktseite `[contact_page]`, Zusätzliche Felder im Formular `[form_fields]`, Davon Pflichtfelder `[form_required]`, Vorgewählte Ländervorwahl `[phone_country]`, Einwilligung zur Kontaktaufnahme verlangen `[form_consent]`, Datenschutzerklärung `[privacy_page]`, Newsletter-Häkchen `[newsletter_module]`, Skills `[skills]`, Werkzeuge `[mcp_tools]`, Zusätzliche Anweisungen (optional) `[system_prompt]` |
| `tl_neo_agent_language` | Sprache `[language]`, Untertitel `[subtitle]`, Begrüßung `[greeting]`, Hinweistext `[fact_note]`, Eingabe-Platzhalter `[input_placeholder]`, Einstiegsfragen `[starter_questions]`, Fragen & Antworten `[canned]` und die Texte des Fensters (`[ui_…]`) |

**Bezeichnung** eines Assistenten ist ein Pflichtfeld: Erlauben Sie es, sonst
lässt sich der Assistent nicht speichern. **Sichtbar für** entscheidet, ob
Besucher den Assistenten sehen: nur an Personen vergeben, die veröffentlichen
dürfen.

## Beispielgruppe „Redaktion: Chat“

Für Redakteure, die Texte und Antworten aktuell halten; Veröffentlichen,
Schlüssel und Verbindungen bleiben bei einem Administrator:

1. **Backend-Module**: **SuperAgent**.
2. **Erlaubte Felder** → `tl_neo_agent`: Anzeigename, Bild, die Felder unter
   Persönlichkeit & Antwortstil, Wissensquelle, Wissenstext, Wissensdatei, die
   Felder der Vermittlung; `tl_neo_agent_language`: alle Felder.
3. **Tabellenrechte** (ab Contao 5.7): `tl_neo_agent` → Bearbeiten;
   `tl_neo_agent_language` → Anlegen, Bearbeiten.
4. **Filemounts**: der Ordner mit Bildern und Wissensdateien.
5. Speichern.

Danach mit einem Testbenutzer anmelden, einen Assistenten und seine
**Sprachen** öffnen und prüfen, ob die Felder der Gruppe da sind.

## Mehrere Websites in einer Installation

Jeder Assistent lässt sich über sein Frontend-Modul auf jeder Website
einbinden; KI-Verbindung, SerpAPI-Schlüssel, Lizenz und Bedingungen gibt es
einmal für die Installation. Die Lizenz wird je Domain geprüft.
