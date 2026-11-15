---
titel: Fehlersuche
kurzfassung: Häufige Probleme mit SuperAgent – Chat nicht sichtbar, keine Antwort, fehlende Funktionen, Weiterleitung, Newsletter, Spracheingabe, Verbindungen zu MCP-Servern, Skills, Hinweis der Gratisfassung, fehlende Rechte – mit Ursache und Lösung.
zielgruppe: betrieb
sprache: de
stand: 2026-11-12
---

# Fehlersuche

Hinweise über der Liste der Assistenten nennen die häufigsten Lücken: kein
KI-Schlüssel, Bedingungen nicht bestätigt, eine Sprache der Website ohne eigene
Texte. Fehler des KI-Diensts, einer Weiterleitungs-Mail oder eines
Werkzeugaufrufs stehen in der Protokolldatei der Installation (`var/logs/`).
Fehlende Menüeinträge oder Felder sind fast immer fehlende Rechte; siehe
[Rechte](permissions.md).

## Der Chat auf der Website

| Was Sie sehen | Warum | Was zu tun ist |
| --- | --- | --- |
| Kein Chat-Knopf auf der Website | Kein KI-Schlüssel („Noch kein KI-Schlüssel hinterlegt. Bis dahin zeigt die Website den Chat nicht an.“); kein aktiver Assistent; das Frontend-Modul steht nicht im Seitenlayout; oder der Assistent steht auf **Nur Vorschau** | Schlüssel hinterlegen; **Aktiv** setzen; das Modul **KI-Assistent (Chat-Widget)** ins Layout setzen; **Sichtbar für** auf **Alle Besucher** stellen. |
| Der Chat erscheint nur bei mir | **Sichtbar für** steht auf **Nur Vorschau**: Nur ein angemeldeter Administrator sieht ihn in Contaos Vorschau | **Alle Besucher** wählen (nach Bestätigung der Bedingungen). |
| **Alle Besucher** lässt sich nicht wählen | Die Bedingungen sind nicht bestätigt | Unter *Zugänge und Lizenzen* bestätigen ([Einrichtung](setup.md#3-bedingungen)). |
| Das Fenster bittet um Einwilligung und bleibt dabei | Die Einwilligungs-Kategorie des Assistenten ist im Einwilligungswerkzeug nicht erteilt | Die Kategorie im Einwilligungswerkzeug erlauben oder eine andere **Einwilligungs-Kategorie** wählen. |
| Das Fenster sagt „Der Chat ist derzeit nicht verfügbar …“ | Das Einwilligungswerkzeug fragt nie nach der Kategorie des Assistenten: In NEO Cookie Consent steht kein aktiver Dienst darin (das Feld **Einwilligungs-Kategorie** sagt das in Rot); die Konsole des Browsers nennt die Kategorie | **Dienst jetzt anlegen** am Feld, oder den Dienst *KI-Chat-Assistent* in dieser Kategorie im Modul *Cookie-Hinweis* anlegen; oder eine andere **Einwilligungs-Kategorie** wählen. |
| Der Chat zeigt seinen Fehlerhinweis statt einer Antwort | Der KI-Dienst gab keine Antwort (Schlüssel, Route, Modell, Kontingent) | Unter *Zugänge und Lizenzen* im Abschnitt **KI-Verbindung** auf **Verbindung prüfen** klicken; das Modell des Assistenten prüfen; das Protokoll lesen. |
| Die Antwort kommt erst am Ende auf einmal, ohne die Statustexte | Das Hosting puffert die Antwort des Servers | Der Chat funktioniert genauso; für nginx setzt die Antwort bereits `X-Accel-Buffering: no`. |
| Der Assistent kennt eine Seite nicht | Die Seite ist keine reguläre Seite unter dem Startpunkt, nicht veröffentlicht, geschützt oder auf `noindex`; die Liste wird alle zehn Minuten aufgefrischt | Die Seite prüfen; bis zu zehn Minuten warten. |
| Das Fenster ist auf einer Seite in einer anderen Sprache englisch | Die Sprache der Seite hat keinen Datensatz und ist keine der elf Sprachen | Einen Sprachdatensatz dafür anlegen. |
| Der Hinweis der Gratisfassung bleibt, obwohl eine Lizenz besteht | Die Website läuft ohne HTTPS, oder die Lizenz deckt die Domain nicht ab | HTTPS verwenden; die Lizenzzeile unter *Zugänge und Lizenzen* prüfen. |
| Der Chat nimmt keine Eingabe an | Der Hinweis der Gratisfassung ist abgeschnitten oder vom Design verdeckt | Ihn sichtbar machen; er gehört zur kostenlosen Lizenz. |
| Der Chat bricht bei jeder Frage mit einem Fehler ab | Das Skill-Paket der Erweiterung ist ungültig (ein Skill länger als 2 800 Zeichen, ein unbekanntes Werkzeug oder eine unbekannte Abhängigkeit) | Das Protokoll lesen; das Paket neu installieren. |

## Funktionen

| Was Sie sehen | Warum | Was zu tun ist |
| --- | --- | --- |
| Die Abschnitte *Internetsuche*, *Eskalation & Einwilligung*, *Spracheingabe*, *Installierte Skills* oder *Werkzeuge der Verbindungen* fehlen | Die Lizenz umfasst die Funktion nicht | So gewollt; eine Lizenz der passenden Stufe zeigt sie wieder mit ihren gespeicherten Werten. |
| „Die Funktionen der Stufe … sind gesperrt, bis ihre Bedingungen bestätigt sind.“ | Der Abschnitt der Stufe ist nicht bestätigt | Unter *Zugänge und Lizenzen* bestätigen. |
| Die Internetsuche funktioniert nicht | Das Modell kann keine Werkzeuge nutzen („Dieses Modell kann keine Werkzeuge nutzen. …“); oder keine freigegebenen Quellen; oder SerpAPI ohne Schlüssel | Ein Modell wählen, das Werkzeuge nutzen kann; Quellen eintragen; den SerpAPI-Schlüssel hinterlegen oder *Nur die freigegebenen Quellen lesen* verwenden. |
| „Noch kein SerpAPI-Schlüssel hinterlegt. Bis dahin liest der Assistent nur die freigegebenen Quellen.“ | Kein SerpAPI-Schlüssel | Im Abschnitt **Internetsuche (SerpAPI)** hinterlegen. |
| Keine Weiterleitung im Chat | Keine **Eskalations-E-Mail** | Eintragen; ohne sie verweist der Assistent auf die Kontaktwege der Website. |
| Weitergeleitete Anfragen kommen nicht an | Mailer nicht eingerichtet oder kein Absender (`MAILER_FROM` oder `neo_super_agent.from_email`) | Mailer und Absender einrichten ([Einrichtung](setup.md#5-absender)). |
| „Meinten Sie …?“ unter dem E-Mail-Feld | Ein Tippfehler bei einem bekannten Anbieter ist wahrscheinlich | Der Besucher korrigiert die Adresse oder behält sie. |
| Die E-Mail-Adresse wird abgelehnt, obwohl sie richtig getippt ist | Ihre Domain hat keinen MX-, A- oder AAAA-Eintrag | Eine Adresse verwenden, deren Domain E-Mail empfängt. |
| Kein Newsletter-Häkchen im Formular | Dem gewählten Modul fehlt ein Verteiler, eine Vorlage oder eine Bestätigungsseite; kein Brevo-Schlüssel; das Modul *Abonnieren* hat keinen Verteiler oder steht auf keiner veröffentlichten Seite | Der Hinweis bei **Newsletter-Häkchen** nennt den Grund; ihn beheben. |
| Kein Mikrofon-Knopf | Keine Pro-Lizenz; **Spracheingabe erlauben** aus; Datenschutz-Modus an ohne **Spracheingabe auch im Datenschutzmodus**; oder der Browser hat keinen Zugriff auf das Mikrofon | Diese Punkte prüfen; der Besucher erlaubt das Mikrofon im Browser. |
| Die Spracheingabe scheitert | Kein Umschrift-Modell an der Route freigegeben (Requesty: *Transcriptions*), oder die Aufnahme ist länger als 60 Sekunden oder größer als 4 MB | Ein Modell freigeben; kürzer aufnehmen. |

## Verbindungen und Skills

| Was Sie sehen | Warum | Was zu tun ist |
| --- | --- | --- |
| Keine Schaltflächen **Verbindungen**, **Skills**, **Protokoll** | Kein Administrator, oder keine Lizenz für Plus | So gewollt. |
| „Bitte eine Adresse mit https:// eingeben.“ | Die Adresse des MCP-Servers ist nicht HTTPS | Die HTTPS-Adresse eintragen. |
| „Die Werkzeuge konnten nicht gelesen werden: …“ | Server nicht erreichbar, Anmeldung fehlt oder wurde abgelehnt | Adresse und Anmeldung prüfen; bei OAuth auf **Verbinden** klicken. |
| Jeder MCP-Server ist unerreichbar | Der Server schickt seinen ausgehenden Verkehr über einen Proxy in einem privaten Netz; private Netze sind gesperrt | Dieser Aufbau wird nicht unterstützt. |
| „Die Anmeldung beim Dienst ist fehlgeschlagen: …“ | Der Dienst hat die OAuth-Anmeldung abgelehnt oder braucht eine eigene App | Beim Dienst die App mit der Weiterleitungsadresse der Verbindung anlegen; Client-ID und Secret eintragen. |
| „Sperre nicht aufgehoben: …“ | Das Häkchen zur Bestätigung wurde nicht gesetzt | **Ich habe die freigegebenen Werkzeuge geprüft und verstehe das Restrisiko.** anhaken und speichern. |
| Ein angehaktes Werkzeug wird nicht benutzt | Gesperrt, nicht mehr beim Server, Verbindung nicht aktiv oder nicht bestätigt, oder das Modell kann keine Werkzeuge aufrufen | Siehe die Hinweise unter der Liste der Werkzeuge am Assistenten; das Protokoll nennt den Grund. |
| Protokoll: „persönliche Daten nicht gesendet“ | **Persönliche Daten** der Verbindung steht auf **Nicht senden** und der Aufruf enthielt eine Kennung | So gewollt; den Datenschutz-Modus oder die Einstellung der Verbindung ändern, wenn das Werkzeug die Daten braucht. |
| „Dieser Server kann keine ZIP-Dateien lesen (die PHP-Erweiterung zip fehlt). …“ | Keine Erweiterung `zip` | Die `SKILL.md` direkt hochladen oder `zip` installieren. |
| „Der Text hat … Zeichen, erlaubt sind höchstens …. …“ | Der Skill ist länger als 2 800 Zeichen | In kleinere Skills teilen. |
| „Der geladene Text ist noch nicht übernommen. …“ | Das Häkchen wurde nicht gesetzt | **Ich habe den Inhalt gelesen und übernehme ihn.** anhaken und speichern. |
