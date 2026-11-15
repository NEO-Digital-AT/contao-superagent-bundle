---
titel: Anpassen
kurzfassung: Das Chatfenster von SuperAgent an die Website anpassen – CSS-Variablen für Farben, Schriften, Ecken, Größe, Position und Icons, das Template, der Hinweis der Gratisfassung und die Schnittstelle zur Einwilligung.
zielgruppe: betrieb
sprache: de
stand: 2026-11-14
---

# Anpassen

Das Fenster kommt hell, ohne Marke und in Arial. Jeder Wert, den eine Website
üblicherweise anpasst, ist eine CSS-Variable `--nsa-*` an `.nsa`. Gesetzt wird
sie in einem Stylesheet der Website; anderes CSS ist nicht nötig:

```css
.nsa {
    --nsa-fab-bg: #0b6e4f;
    --nsa-head-bg: #0b6e4f;
    --nsa-user-bg: #0b6e4f;
    --nsa-accent: #0b6e4f;
    --nsa-font-body: inherit;   /* Schrift der Seite statt Arial */
    --nsa-radius-panel: 8px;
    --nsa-radius-bubble: 8px;
    --nsa-fab-size: 52px;
    --nsa-icon-chat: url("/files/icons/chat.svg");
}
```

Text braucht 4,5:1 gegen seinen Hintergrund, Rahmen von Feldern und Knöpfen
3:1. Das Fenster liest die Tokens des Themes nicht; ein Theme kann es also
nicht versehentlich verändern.

## Farben

| Variable | Vorgabe | Wofür |
| --- | --- | --- |
| `--nsa-fab-bg`, `--nsa-fab-ink` | `#1f2328`, `#ffffff` | Chat-Knopf |
| `--nsa-panel-bg`, `--nsa-panel-border` | `#ffffff`, `#d1d9e0` | Fenster |
| `--nsa-head-bg`, `--nsa-head-ink`, `--nsa-head-sub` | `#1f2328`, `#ffffff`, `#d1d9e0` | Kopf, Name, Untertitel |
| `--nsa-avatar-bg`, `--nsa-avatar-border`, `--nsa-avatar-ink` | durchscheinendes Weiß, `#ffffff` | Bild oder Icon im Kopf |
| `--nsa-text`, `--nsa-fg` | `#1f2328` | Text; Überschriften im Einwilligungs- und im Weiterleiten-Formular |
| `--nsa-muted`, `--nsa-faint` | `#59636e` | Nebentext, Hinweise unter der Eingabe |
| `--nsa-ai-bg`, `--nsa-ai-ink` | `#f6f8fa`, `#1f2328` | KI-Hinweis unter dem Kopf |
| `--nsa-bot-bg`, `--nsa-bot-ink`, `--nsa-bot-border` | `#f6f8fa`, `#1f2328`, `#d1d9e0` | Antworten des Assistenten |
| `--nsa-user-bg`, `--nsa-user-ink` | `#1f2328`, `#ffffff` | Nachrichten des Besuchers |
| `--nsa-chip-bg`, `--nsa-chip-hover-bg`, `--nsa-chip-border`, `--nsa-chip-ink` | `#ffffff`, `#f6f8fa`, `#818b98`, `#1f2328` | Einstiegsfragen und der Knopf *Seite öffnen* |
| `--nsa-field-bg`, `--nsa-field-border`, `--nsa-field-ink`, `--nsa-placeholder` | `#ffffff`, `#818b98`, `#1f2328`, `#6e7781` | Eingabefelder; Mikrofon-Knopf |
| `--nsa-rec-bg`, `--nsa-rec-ink` | `#b42318`, `#ffffff` | Mikrofon-Knopf während der Aufnahme |
| `--nsa-wave` | `var(--nsa-rec-bg)` | Pegelwelle neben dem Stopp-Knopf |
| `--nsa-error` | `#b42318` | Pflichtmarke, Feldfehler im Weiterleiten-Formular |
| `--nsa-accent`, `--nsa-accent-ink` | `#1f2328`, `#ffffff` | Senden- und Hauptknöpfe, Fokusrahmen |
| `--nsa-ring` | `rgba(9, 105, 218, 0.3)` | Fokusring |
| `--nsa-hover` | `#0969da` | Hover-Farbe des Weiterleiten-Links |
| `--nsa-icon-bg`, `--nsa-icon-border`, `--nsa-icon-ink` | `#f6f8fa`, `#d1d9e0`, `#1f2328` | Icon der Bitte um Einwilligung |
| `--nsa-line` | `#d1d9e0` | Trennlinien |
| `--nsa-dot` | `#818b98` | Tipp-Punkte |

## Schriften

| Variable | Vorgabe | Wofür |
| --- | --- | --- |
| `--nsa-font-body` | `Arial, "Helvetica Neue", Helvetica, sans-serif` | aller Text; `inherit` übernimmt die Schrift der Seite |
| `--nsa-font-display` | `var(--nsa-font-body)` | Name des Assistenten, Überschriften |
| `--nsa-font-size` | `15px` | Grundgröße; alles andere richtet sich danach |

## Ecken

| Variable | Vorgabe | Wofür |
| --- | --- | --- |
| `--nsa-radius-panel` | `20px` | Fenster |
| `--nsa-radius-bubble` | `16px` | Nachrichten |
| `--nsa-radius-input` | `12px` | Eingabefelder |
| `--nsa-radius-button` | `999px` | Knöpfe |
| `--nsa-radius-chip` | `999px` | Einstiegsfragen, *Seite öffnen* |
| `--nsa-radius-fab` | `50%` | Chat-Knopf |
| `--nsa-radius-avatar` | `50%` | Bild des Assistenten |

`--nsa-radius-xl` und `--nsa-radius-md` früherer Fassungen setzen weiterhin
Fenster und Eingabefelder.

## Größe und Position

| Variable | Vorgabe | Wofür |
| --- | --- | --- |
| `--nsa-fab-size` | `60px` | Chat-Knopf |
| `--nsa-panel-width` | `380px` | Fenster (nie breiter als der Bildschirm) |
| `--nsa-log-height` | `min(46vh, 380px)` | Höhe des Nachrichtenbereichs |
| `--nsa-right`, `--nsa-left`, `--nsa-bottom` | `clamp(16px, 3vw, 28px)`, `auto`, `clamp(16px, 3vw, 28px)` | Abstand von den Bildschirmrändern |
| `--nsa-gap` | `10px` | Abstand zwischen Nachrichten |
| `--nsa-z` | `290` | Stapelreihenfolge |
| `--nsa-fab-shadow`, `--nsa-panel-shadow` | weiche dunkle Schatten | Schatten von Knopf und Fenster; `none` schaltet sie ab |
| `--nsa-ease-out` | `cubic-bezier(0.22, 1, 0.36, 1)` | Bewegung beim Öffnen (aus bei `prefers-reduced-motion`) |

Die Position **Unten links** des Assistenten setzt die Klasse `nsa--left`, die
`--nsa-left` und `--nsa-right` tauscht.

Mit Knöpfen anderer Erweiterungen in derselben Ecke verschieben die
Schaltflächen am Bildschirmrand von NEO Basic das Widget: Sie setzen
`data-neo-dock-place` (`menu` oder `stack`) und addieren `--neo-dock-x` und
`--neo-dock-y` zu seinem Abstand von den Rändern. Im Menü ist der Chat-Knopf
ausgeblendet, und das Fenster öffnet sich `--neo-dock-menu-gap` (Vorgabe
`12px`) über der Einstellungs-Schaltfläche. Das Aussehen der
Einstellungs-Schaltfläche kommt aus den `--neo-dock-*`-Variablen von NEO Basic.

## Icons

Jedes Icon ist eine Bildadresse in einer Variablen; das Bild gibt die Form, die
Farbe kommt vom Element (wie bei einer Icon-Schrift). Am besten eignen sich
SVG-Dateien mit durchsichtigem Hintergrund.

| Variable | Erscheint |
| --- | --- |
| `--nsa-icon-chat` | Chat-Knopf |
| `--nsa-icon-close` | Knopf zum Schließen, Chat-Knopf bei offenem Fenster |
| `--nsa-icon-send` | Senden-Knopf |
| `--nsa-icon-bot` | Kopf und Antworten, wenn kein Bild gesetzt ist |
| `--nsa-icon-new` | *Neues Gespräch* |
| `--nsa-icon-mail` | Weiterleiten-Link |
| `--nsa-icon-consent` | Bitte um Einwilligung |
| `--nsa-icon-settings` | Knopf, der die Einstellungen der Einwilligung öffnet |
| `--nsa-icon-arrow` | Knopf *Seite öffnen* |
| `--nsa-icon-mic` | Mikrofon-Knopf (Spracheingabe) |

`--nsa-msg-avatar: flex` zeigt Bild oder Icon neben jeder Antwort (Vorgabe
`none`). `--nsa-ok` und `--nsa-font-mono` werden nicht mehr verwendet und
bleiben, damit ältere Stylesheets nicht brechen.

## Template

`contao/templates/frontend_module/neo_agent.html.twig` der Erweiterung nach
`templates/frontend_module/` der Website kopieren. Die Attribute `data-nsa-*`
bleiben stehen; das Skript arbeitet mit ihnen. Die Zeile `data-nsa-ai`
(KI-Hinweis) setzt das Skript wieder ein, wenn ein eigenes Template sie
weglässt.

Das Widget trägt außerdem die Attribute der Schaltflächen am Bildschirmrand
von NEO Basic: `data-neo-dock="assistant"`, `data-neo-dock-corner`
(`bottom-right` oder `bottom-left`), `data-neo-dock-order="10"` und
`data-neo-dock-label` (die Beschriftung des Chat-Knopfs) an `[data-nsa]`,
`data-neo-dock-launcher` am Chat-Knopf und `template[data-neo-dock-icon]` mit
dem Symbol des Menüeintrags. In einem eigenen Template behalten: Ohne sie
behält der Chat-Knopf seinen eigenen Platz und wird nicht mit den Knöpfen
anderer Erweiterungen in seiner Ecke angeordnet.

## Hinweis der Gratisfassung

Ohne Lizenz endet das Fenster mit dem Hinweis „powered by“ und dem Logo des
Herstellers. Gezeichnet wird er vom gemeinsamen Skript des Basic-Bundles
(`bundles/neobasic/credit.js`), nicht vom Template, und er lässt sich nicht mit
Stylesheets ausblenden. Er übernimmt die Farben des Fensters (`--nsa-panel-bg`,
`--nsa-line`, `--nsa-muted`), solange sie lesbar sind, sonst feste. Solange er
abgeschnitten oder verdeckt ist, nimmt der Chat keine Eingabe an. Wer ihn
entfernt, verstößt gegen die Lizenzbedingungen. Die Lizenzprüfung im Browser
braucht HTTPS; über einfaches HTTP bleibt der Hinweis auch mit Lizenz stehen.

## Einwilligung

Der Chat wartet auf die **Einwilligungs-Kategorie** des Assistenten, wenn die
Seite ein Einwilligungswerkzeug hat. Er fragt:

1. `window.cookieConsent.allows(kategorie)`, erneut geprüft beim Ereignis
   `cookie-consent:change` auf `window`. NEO Cookie Consent stellt das ohne
   weitere Einrichtung bereit; der Knopf im Chat, der um Einwilligung bittet,
   trägt `data-ncc-open` und öffnet dessen Einstellungen.
2. Sonst `window.superAgentConsent(kategorie)`, erneut geprüft beim Ereignis
   `superagent:consent-change` auf `window`. Für ein anderes
   Einwilligungswerkzeug stellt ein kleines Skript der Website die Funktion
   bereit und löst das Ereignis aus, sobald sich die Wahl des Besuchers ändert:

   ```js
   window.superAgentConsent = function (category) {
       return true; // oder false: Hat der Besucher dieser Kategorie zugestimmt?
   };
   // nach jeder Änderung der Wahl:
   window.dispatchEvent(new Event('superagent:consent-change'));
   // der Einwilligungs-Knopf des Chats wurde geklickt: die Einstellungen des Werkzeugs öffnen
   window.addEventListener('superagent:consent-request', function (e) {
       // e.detail.category: die Kategorie, auf die der Chat wartet
   });
   ```

   `category` ist der Wert des Felds: `functional`, `statistics`,
   `marketing` oder eine eigene Kategorie. `null` zurückgeben, wenn das
   Werkzeug nie nach dieser Kategorie fragt.

Fragt das Einwilligungswerkzeug nie nach der Kategorie (NEO Cookie Consent:
`window.cookieConsent.categories` enthält sie nicht; ein anderes Werkzeug:
`null`), zeigt das Fenster statt der Bitte um Einwilligung „Der Chat ist derzeit
nicht verfügbar: Die Cookie-Einstellungen dieser Website enthalten ihn noch
nicht.“ (Text *Einwilligung: Chat nicht verfügbar* des Sprach-Datensatzes), und
die Konsole des Browsers zeigt eine Warnung mit der Kategorie.

Ohne Einwilligungswerkzeug oder mit der Kategorie *Keine Einwilligung nötig*
ist der Chat sofort verfügbar. Das Google-Analytics-Ereignis des Frontend-Moduls
wird nur gesendet, wenn ein Einwilligungswerkzeug `statistics` erlaubt, auf
dieselbe Weise abgefragt.
