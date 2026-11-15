---
titel: Installation
kurzfassung: Voraussetzungen, Installation von SuperAgent mit dem Contao Manager oder Composer, was die Installation ergänzt, Backend-Sprachen, Aktualisieren und Entfernen.
zielgruppe: betrieb
sprache: de
stand: 2026-11-12
---

# Installation

## Voraussetzungen

| Was | Wofür |
| --- | --- |
| Contao 5.3 bis 6.0 (`contao/core-bundle ^5.3 \|\| ^6.0`) | alles; unter Contao 4 lehnt Composer ab |
| PHP 8.2 bis 8.5, soweit die Contao-Fassung sie unterstützt (Contao 5.7 braucht 8.3, Contao 6.0 braucht 8.4), mit den Erweiterungen `ctype`, `intl` und `mbstring` | alles |
| `neo-digital/contao-basic-bundle` | wird automatisch mitinstalliert; es hält KI-Verbindung, SerpAPI-Schlüssel, Lizenz und Bedingungen unter **Zugänge und Lizenzen** (und braucht `sodium`) |
| Ein Konto bei einem OpenAI-kompatiblen KI-Anbieter (z. B. Requesty, OpenAI) | jede Antwort; solange kein Schlüssel gespeichert ist, zeigt die Website den Chat nicht |
| Ein funktionierender Mailversand von Contao (`MAILER_DSN`) | Weiterleiten von Anfragen an das Team |
| Optional: die PHP-Erweiterung `zip` | Skills aus einer ZIP-Datei installieren (sonst nur `SKILL.md`-Dateien) |
| Optional: ein SerpAPI-Konto | Internetsuche über eine Suchmaschine |
| Optional: `neo-digital/contao-brevo-bundle` oder `contao/newsletter-bundle` | das Newsletter-Häkchen im Weiterleiten-Formular |
| HTTPS | die Lizenzprüfung im Browser; über reines HTTP bleibt der Hinweis auch mit Lizenz |

## Installation mit dem Contao Manager

1. Den Contao Manager öffnen.
2. Nach `neo-digital/contao-superagent-bundle` suchen.
3. Auf **Hinzufügen** und danach auf **Änderungen anwenden** klicken.
4. Wenn der Contao Manager fragt, die Datenbank aktualisieren und alle
   vorgeschlagenen Änderungen ausführen.

## Installation mit Composer

```bash
composer require neo-digital/contao-superagent-bundle
php vendor/bin/contao-console contao:migrate
```

`contao:migrate` legt die Tabellen der Erweiterung an (Assistenten,
Sprachdatensätze, Verbindungen, Skills, Werkzeugprotokoll). Composer kopiert
Skript, Stylesheet und Symbole nach `public/bundles/neosuperagent/`; falls das
nicht geschehen ist:

```bash
php vendor/bin/contao-console assets:install
```

## Was die Installation ergänzt

- das Frontend-Modul **KI-Assistent (Chat-Widget)** (Gruppe *NEO Digital*);
- das Backend-Modul **NEO Digital › SuperAgent**, die Liste der Assistenten;
- unter **Zugänge und Lizenzen**: die Nutzung der gemeinsamen KI-Verbindung,
  den Abschnitt **Internetsuche (SerpAPI)**, die Lizenzzeile *SuperAgent* und
  ihre Bedingungen.

Weiter mit [Einrichtung](setup.md).

## Backend-Sprachen

Backend und Standardtexte des Chats gibt es auf Englisch, Deutsch,
Französisch, Niederländisch, Italienisch, Polnisch, Tschechisch, Ungarisch,
Slowenisch, Kroatisch und Spanisch. Eine Projektdatei
`translations/<domain>.<sprache>.yaml` überschreibt einzelne Texte. Ungarisch
und Kroatisch bietet Contao als Backend-Sprache erst an, wenn sie in
`config/config.yaml` freigeschaltet sind:

```yaml
contao:
    intl:
        enabled_locales: ['+hu', '+hr']
```

## Aktualisieren

1. Das Paket mit dem Contao Manager aktualisieren oder mit
   `composer update neo-digital/contao-superagent-bundle`.
2. Die Datenbank aktualisieren (`contao:migrate`).
3. `CHANGELOG.md` der neuen Fassung lesen. Braucht eine Fassung neue Rechte
   oder einen Schritt nach dem Aktualisieren, steht es dort.
4. Ändert eine Fassung eine Passage der Bedingungen, fragt die Liste der
   Assistenten die Bestätigung neu ab (siehe
   [Einrichtung](setup.md#3-bedingungen)).

Schlüssel, die eine frühere Fassung je Assistent hielt (Route und Schlüssel der
KI-Verbindung, SerpAPI-Schlüssel), übernimmt die Datenbankaktualisierung einmal
nach *Zugänge und Lizenzen*. Die vorgeschlagenen Fragen der Sprachdatensätze
werden zu ihren Einstiegsfragen, ohne Antwort; bis die Datenbank aktualisiert
ist, zeigt der Chat sie wie bisher.

## Entfernen

1. Das Frontend-Modul **KI-Assistent (Chat-Widget)** aus den Seitenlayouts
   nehmen.
2. Vor dem Entfernen: den Lizenzschlüssel von SuperAgent unter *Zugänge und
   Lizenzen* leeren und speichern, damit die Lizenz von der Website genommen
   wird.
3. Das Paket mit dem Contao Manager entfernen oder mit
   `composer remove neo-digital/contao-superagent-bundle`.
4. Die Datenbank aktualisieren. Contao bietet dann an, die Tabellen der
   Erweiterung zu löschen: Assistenten, Texte, Verbindungen mit ihren
   gespeicherten Schlüsseln und Tokens, Skills und das Werkzeugprotokoll.
