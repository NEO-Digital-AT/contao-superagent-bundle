# Documentation images

The images in `docs/<lang>/bilder/` are screenshots of the chat window and of
the back end of a test installation, marked with the NEO annotation layer
(frames and numbers). `capture.cjs` takes them again, for example after a
change to the chat or to a form. This folder is not part of the Composer
package (`.gitattributes`: `export-ignore`).

## What the test installation needs

- Contao 5.7 with this bundle, Basic and NEO Cookie Consent; one root page.
- An AI key on *Access keys and licences* (any value: the images send no
  message), the terms confirmed, and a licence for Plus or Pro (the buttons
  *Connections* and *Skills* appear only with it).
- The assistant (`NSA_AGENT`, default 1) in the front-end module on the start
  page, visible to all visitors, named neutrally (the images show "Mia"),
  with a language record for `en` and for `de`. Each record has three starter
  questions, one of them with an answer, for example "What are your opening
  hours?" / "Wann haben Sie geöffnet?" with the answer "Monday to Friday,
  9 am to 5 pm." / "Montag bis Freitag, 9 bis 17 Uhr.".
- NEO Cookie Consent with the service *AI chat assistant* (category
  *Functional*), so that the window opens after "Accept all".
- An administrator (`NSA_ADMIN_USER`).
- Node.js with Playwright and its Chromium, and the plugin `neo-doku` of
  `NEO-Claude-Plugins` (`annotate.js`) next to this repository, or its path
  in `NSA_ANNOTATE`.

## Taking the images

```bash
NSA_ADMIN_PASSWORD=… node tools/docs-images/capture.cjs en
NSA_ADMIN_PASSWORD=… node tools/docs-images/capture.cjs de
```

The script sets the back-end language of the administrator and the language
of the first root page to the language of the images and restores both at
the end. Do not run two captures at the same time. The forms of a new
connection and a new skill are filled in but not saved: no request leaves
the installation.

| Variable | Default | Meaning |
| --- | --- | --- |
| `NSA_BASE` | `http://127.0.0.1:8000` | Address of the test installation |
| `NSA_ADMIN_USER`, `NSA_ADMIN_PASSWORD` | `admin`, – | Administrator |
| `NSA_MYSQL` | `mysql contao` | Command for the database of the test installation |
| `NSA_AGENT` | `1` | ID of the assistant |
| `NSA_ANNOTATE` | `../NEO-Claude-Plugins/plugins/neo-doku/scripts/annotate.js` | Annotation layer |

| Image | Shows |
| --- | --- |
| `chat-window.png` | The chat window: AI notice (1), starter questions (2), the button that shows them again (3) |
| `assistant-form.png` | The assistant's form: consent category (1), languages with their starter questions (2), button of a language (3), *Add language* (4) |
| `language-record.png` | The language record: starter questions (1), questions & answers (2) |
| `connection-form.png` | A new connection: name (1), address (2), sign-in (3), personal data (4) |
| `skill-form.png` | A new skill: source (1), address on GitHub (2) |

Check every image after taking it: frames on the right element, no real
names, addresses or keys.
