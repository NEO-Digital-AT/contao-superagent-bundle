---
titel: SuperAgent – Dokumentation
kurzfassung: Installation, Einrichtung, Rechte, Bedienung, Anpassen, Arbeitsweise des Assistenten, Datenschutz und Fehlersuche des KI-Chat-Assistenten für Contao 5.
zielgruppe: anwender
sprache: de
stand: 2026-11-12
---

# SuperAgent – Dokumentation

SuperAgent ergänzt eine Contao-Website um einen KI-Chat-Assistenten. Ein
Chat-Knopf öffnet ein Fenster, in dem Besucher Fragen stellen; ein Sprachmodell
antwortet aus der Wissensquelle der Website (ihre `llms.txt`, ein Text oder eine
Datei) und den im Backend eingetragenen Fragen und Antworten. Mit Lizenz öffnet
der Assistent auch Seiten, leitet Anfragen an das Team weiter, sucht auf
freigegebenen Websites, nutzt Werkzeuge verbundener MCP-Server und installierte
Skills und nimmt Spracheingabe an.

## Für Administratoren und Agenturen

In der Reihenfolge, in der man sie braucht:

| Seite | Worum es geht |
| --- | --- |
| [Installation](installation.md) | Voraussetzungen, Installation mit dem Contao Manager oder Composer, Aktualisieren, Entfernen |
| [Einrichtung](setup.md) | Stufen und Lizenz, KI-Verbindung, Bedingungen, das Frontend-Modul, Absender, Einwilligungswerkzeug, API-Schlüssel |
| [Rechte](permissions.md) | Welche Contao-Rechte ein Redakteur braucht; was nur Administratoren dürfen |
| [Anpassen](customising.md) | CSS-Variablen, Template, Hinweis der Gratisfassung, Schnittstelle zur Einwilligung |
| [So arbeitet der Assistent](how-it-works.md) | Modell, Skills und Werkzeuge, was das Modell bekommt, die Endpunkte |
| [Datenschutz](privacy.md) | Was wohin geht, was gespeichert wird, was in die Datenschutzerklärung gehört |
| [Text für die Datenschutzerklärung](privacy-texts.md) | Ein Textbaustein in elf Sprachen, mit Anleitung |
| [Fehlersuche](troubleshooting.md) | Häufige Probleme: was man sieht, warum, was zu tun ist |

## Für die Redaktion

| Seite | Worum es geht |
| --- | --- |
| [Bedienungsanleitung](usage/README.md) | Einen Assistenten einrichten, seine Texte je Sprache, was Besucher sehen, Verbindungen zu MCP-Servern, installierte Skills |
