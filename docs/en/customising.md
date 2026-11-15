---
title: Customising
summary: Adapting the SuperAgent chat window to the website – CSS variables for colours, fonts, corners, size, position and icons, the template, the credit line of the free edition and the consent interface.
audience: operations
language: en
updated: 2026-11-14
---

# Customising

The window comes light, brand-free and in Arial. Every value a website usually
adapts is a `--nsa-*` CSS variable on `.nsa`. Set them in a stylesheet of the
website; no other CSS is needed:

```css
.nsa {
    --nsa-fab-bg: #0b6e4f;
    --nsa-head-bg: #0b6e4f;
    --nsa-user-bg: #0b6e4f;
    --nsa-accent: #0b6e4f;
    --nsa-font-body: inherit;   /* font of the page instead of Arial */
    --nsa-radius-panel: 8px;
    --nsa-radius-bubble: 8px;
    --nsa-fab-size: 52px;
    --nsa-icon-chat: url("/files/icons/chat.svg");
}
```

Keep text at 4.5:1 against its background and borders of fields and buttons at
3:1. The window does not read the theme's own tokens, so a theme cannot change
it by accident.

## Colours

| Variable | Default | Used for |
| --- | --- | --- |
| `--nsa-fab-bg`, `--nsa-fab-ink` | `#1f2328`, `#ffffff` | chat button |
| `--nsa-panel-bg`, `--nsa-panel-border` | `#ffffff`, `#d1d9e0` | window |
| `--nsa-head-bg`, `--nsa-head-ink`, `--nsa-head-sub` | `#1f2328`, `#ffffff`, `#d1d9e0` | header, name, subtitle |
| `--nsa-avatar-bg`, `--nsa-avatar-border`, `--nsa-avatar-ink` | translucent white, `#ffffff` | image or icon in the header |
| `--nsa-text`, `--nsa-fg` | `#1f2328` | text; headings in the consent and forwarding forms |
| `--nsa-muted`, `--nsa-faint` | `#59636e` | secondary text, notes below the input |
| `--nsa-ai-bg`, `--nsa-ai-ink` | `#f6f8fa`, `#1f2328` | AI notice below the header |
| `--nsa-bot-bg`, `--nsa-bot-ink`, `--nsa-bot-border` | `#f6f8fa`, `#1f2328`, `#d1d9e0` | replies of the assistant |
| `--nsa-user-bg`, `--nsa-user-ink` | `#1f2328`, `#ffffff` | messages of the visitor |
| `--nsa-chip-bg`, `--nsa-chip-hover-bg`, `--nsa-chip-border`, `--nsa-chip-ink` | `#ffffff`, `#f6f8fa`, `#818b98`, `#1f2328` | starter questions and the button *Open page* |
| `--nsa-field-bg`, `--nsa-field-border`, `--nsa-field-ink`, `--nsa-placeholder` | `#ffffff`, `#818b98`, `#1f2328`, `#6e7781` | input fields; microphone button |
| `--nsa-rec-bg`, `--nsa-rec-ink` | `#b42318`, `#ffffff` | microphone button while recording |
| `--nsa-wave` | `var(--nsa-rec-bg)` | level wave next to the stop button |
| `--nsa-error` | `#b42318` | required marker, field errors in the forwarding form |
| `--nsa-accent`, `--nsa-accent-ink` | `#1f2328`, `#ffffff` | send and main buttons, focus border |
| `--nsa-ring` | `rgba(9, 105, 218, 0.3)` | focus ring |
| `--nsa-hover` | `#0969da` | hover colour of the forward link |
| `--nsa-icon-bg`, `--nsa-icon-border`, `--nsa-icon-ink` | `#f6f8fa`, `#d1d9e0`, `#1f2328` | icon of the consent request |
| `--nsa-line` | `#d1d9e0` | dividing lines |
| `--nsa-dot` | `#818b98` | typing dots |

## Fonts

| Variable | Default | Used for |
| --- | --- | --- |
| `--nsa-font-body` | `Arial, "Helvetica Neue", Helvetica, sans-serif` | all text; `inherit` takes the font of the page |
| `--nsa-font-display` | `var(--nsa-font-body)` | name of the assistant, headings |
| `--nsa-font-size` | `15px` | base size; everything else scales with it |

## Corners

| Variable | Default | Used for |
| --- | --- | --- |
| `--nsa-radius-panel` | `20px` | window |
| `--nsa-radius-bubble` | `16px` | messages |
| `--nsa-radius-input` | `12px` | input fields |
| `--nsa-radius-button` | `999px` | buttons |
| `--nsa-radius-chip` | `999px` | starter questions, *Open page* |
| `--nsa-radius-fab` | `50%` | chat button |
| `--nsa-radius-avatar` | `50%` | image of the assistant |

`--nsa-radius-xl` and `--nsa-radius-md` of earlier versions still set the
window and the input fields.

## Size and position

| Variable | Default | Used for |
| --- | --- | --- |
| `--nsa-fab-size` | `60px` | chat button |
| `--nsa-panel-width` | `380px` | window (never wider than the screen) |
| `--nsa-log-height` | `min(46vh, 380px)` | height of the message area |
| `--nsa-right`, `--nsa-left`, `--nsa-bottom` | `clamp(16px, 3vw, 28px)`, `auto`, `clamp(16px, 3vw, 28px)` | distance from the screen edges |
| `--nsa-gap` | `10px` | space between messages |
| `--nsa-z` | `290` | stacking order |
| `--nsa-fab-shadow`, `--nsa-panel-shadow` | soft dark shadows | shadow of button and window; `none` turns them off |
| `--nsa-ease-out` | `cubic-bezier(0.22, 1, 0.36, 1)` | opening movement (off with `prefers-reduced-motion`) |

The position **Bottom left** of the assistant adds the class `nsa--left`, which
swaps `--nsa-left` and `--nsa-right`.

With buttons of other extensions in the same corner, the buttons at the edge
of the screen of NEO Basic move the widget: they set `data-neo-dock-place`
(`menu` or `stack`) and add `--neo-dock-x` and `--neo-dock-y` to its distance
from the edges. In the menu the chat button is hidden and the window opens
`--neo-dock-menu-gap` (default `12px`) above the settings button. The look of
the settings button comes from the `--neo-dock-*` variables of NEO Basic.

## Icons

Every icon is an image address in a variable; the image gives the shape, the
colour comes from the element (as with an icon font). SVG files with a
transparent background work best.

| Variable | Shown |
| --- | --- |
| `--nsa-icon-chat` | chat button |
| `--nsa-icon-close` | close button, chat button while open |
| `--nsa-icon-send` | send button |
| `--nsa-icon-bot` | header and replies, when no image is set |
| `--nsa-icon-new` | *New conversation* |
| `--nsa-icon-mail` | forward link |
| `--nsa-icon-consent` | consent request |
| `--nsa-icon-settings` | button that opens the consent settings |
| `--nsa-icon-arrow` | button *Open page* |
| `--nsa-icon-mic` | microphone button (voice input) |

`--nsa-msg-avatar: flex` shows the image or icon next to every reply (default
`none`). `--nsa-ok` and `--nsa-font-mono` are no longer used and are kept so
that older stylesheets do not break.

## Template

Copy `contao/templates/frontend_module/neo_agent.html.twig` of the extension to
`templates/frontend_module/` of the website. Keep the `data-nsa-*` attributes;
the script works with them. The line `data-nsa-ai` (AI notice) is put back by
the script if an own template leaves it out.

The widget also carries the attributes of the buttons at the edge of the
screen of NEO Basic: `data-neo-dock="assistant"`, `data-neo-dock-corner`
(`bottom-right` or `bottom-left`), `data-neo-dock-order="10"` and
`data-neo-dock-label` (the label of the chat button) on `[data-nsa]`,
`data-neo-dock-launcher` on the chat button, and `template[data-neo-dock-icon]`
with the icon of the menu entry. Keep them in an own template: without them
the chat button keeps its own place and is not arranged with the buttons of
other extensions in its corner.

## Credit line

Without a licence the window ends with the credit line "powered by" and the
logo of the manufacturer. The shared script of the Basic bundle draws it
(`bundles/neobasic/credit.js`), not the template, and it cannot be hidden with
stylesheets. It takes the window colours (`--nsa-panel-bg`, `--nsa-line`,
`--nsa-muted`) while they are readable, otherwise fixed ones. While it is cut
off or covered, the chat takes no input. Removing it breaks the licence terms.
The licence check in the browser needs HTTPS; over plain HTTP the credit line
stays even with a licence.

## Consent

The chat waits for the **Consent category** of the assistant when the page has
a consent tool. It asks:

1. `window.cookieConsent.allows(category)`, checked again on the event
   `cookie-consent:change` on `window`. NEO Cookie Consent provides it without
   further setup; the button in the chat that asks for consent carries
   `data-ncc-open` and opens its settings.
2. Otherwise `window.superAgentConsent(category)`, checked again on the event
   `superagent:consent-change` on `window`. For another consent tool, a small
   script of the website provides the function and dispatches the event
   whenever the visitor's choice changes:

   ```js
   window.superAgentConsent = function (category) {
       return true; // or false: has the visitor agreed to this category?
   };
   // after every change of the choice:
   window.dispatchEvent(new Event('superagent:consent-change'));
   // the consent button of the chat was clicked: open the settings of the tool
   window.addEventListener('superagent:consent-request', function (e) {
       // e.detail.category: the category the chat waits for
   });
   ```

   `category` is the value of the field: `functional`, `statistics`,
   `marketing` or an own category. Return `null` when the tool never asks for
   this category.

When the consent tool will never ask for the category (NEO Cookie Consent:
`window.cookieConsent.categories` does not contain it; another tool: `null`),
the window shows "The chat is not available at the moment: the cookie settings
of this website do not include it yet." (text *Consent: chat not available* of
the language record) instead of the consent request, and the browser console
shows a warning with the category.

Without a consent tool, or with the category *No consent needed*, the chat is
available at once. The Google Analytics event of the front-end module is sent
only when a consent tool grants `statistics`, asked the same way.
