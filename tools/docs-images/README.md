# Documentation images

The images in `docs/<lang>/bilder/` are screenshots of the chat window and of
the back end of a test installation, marked with the NEO annotation layer
(frames and numbers). `capture.cjs` takes them again, for example after a
change to the chat or to a form. This folder is not part of the Composer
package (`.gitattributes`: `export-ignore`).

## What the test installation needs

- Contao 5.7 with this bundle, Basic and NEO Cookie Consent.
- Two root pages, one with the language `en` and one with `de`, with URL
  prefixes (`/en/`, `/de/`). With a single root page the script sets its
  language to the language of the images for the time of the run.
- One assistant per language (SuperAgent 1.0: one assistant, one language),
  each in a front-end module **AI assistant (chat widget)** in the layout of
  its language, visible to all visitors, named neutrally (the images show
  "Holiday flat" with the display name "Lakeview" and "Ferienwohnung" with
  "Seeblick"). Each language record has two starter questions, one of them
  with an answer, for example "When can I check in?" / "Ab wann kann ich
  einchecken?" with the answer "Check-in from 3 pm, later on request.
  Check-out by 10 am." / "Anreise ab 15 Uhr, später nach Absprache. Abreise
  bis 10 Uhr.". An escalation address, **Forward to (name)** "Anna" and the
  additional fields Name and Phone.
- An AI key on *Access keys and licences* that works: `chat-answer.png` sends
  one real question (the starter question with an answer). The terms
  confirmed, and a licence for Plus or Pro (the buttons *Connections* and
  *Skills* appear only with it).
- The sample records of `sample.sql`: a connection *Appointments* with four
  tools in all states (one released, three locked), a skill loaded from
  GitHub and not taken over yet, two calls in the tool log. Nothing in them
  is ever contacted.
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

A list of image names after the language takes only those, e.g.
`capture.cjs en chat-window,assistant-model`.

For the time of the run the script sets the back-end language of the
administrator, the name of the sample connection (*Appointments* / *Termine*)
and web search with two approved sources at the assistant, and for
`access-terms.png` it sets the confirmation of the terms aside; all of it is
restored at the end. Do not run two captures at the same time. The forms of a
new connection and a new skill are filled in, not saved, and their drafts are
deleted again: no request leaves the installation.

The help texts below the fields of the back end get a little more space in the
images, so that the frames round label and control do not touch them.

| Variable | Default | Meaning |
| --- | --- | --- |
| `NSA_BASE` | `http://127.0.0.1:8000` | Address of the test installation |
| `NSA_ADMIN_USER`, `NSA_ADMIN_PASSWORD` | `admin`, – | Administrator |
| `NSA_MYSQL` | `mysql contao` | Command for the database of the test installation |
| `NSA_PAGE` | `/<lang>/` | Page the chat is photographed on |
| `NSA_AGENT` | the first assistant with a language record in the language of the images | ID of the assistant |
| `NSA_CONNECTION` | the connection with the tool `list_free_slots` | ID of the connection for `connection-tools.png` |
| `NSA_SKILL` | the first skill that is loaded and not taken over | ID of the skill for `skill-review.png` |
| `NSA_ANNOTATE` | `../NEO-Claude-Plugins/plugins/neo-doku/scripts/annotate.js` | Annotation layer |

| Image | Shows |
| --- | --- |
| `chat-window.png` | The chat window: new conversation (1), AI notice (2), starter questions (3), input (4), microphone (5), forward button (6), chat button (7) |
| `chat-answer.png` | A starter question clicked (1), the reply (2), the button that shows the starter questions again (3) |
| `chat-forwarding.png` | The forwarding form: name (1), e-mail (2), phone (3), message (4), consent (5), send (6) |
| `access-licence.png` | Access keys and licences: licence key (1), its status (2), the terms section (3), route (4), key (5), test connection (6) |
| `access-terms.png` | The terms of a new installation: extension and edition (1), passage (2), checkbox (3), confirm (4) |
| `module-form.png` | The front-end module: module type (1), assistant (2), GA4 event (3) |
| `assistant-list.png` | The list: new (1), access keys (2), connections (3), skills (4), tool log (5), edit (6), copy (7) |
| `assistant-form.png` | The top of the form: active (1), visible to (2), consent category (3), display name (4), image (5), position (6), the language (7), its button (8) |
| `assistant-model.png` | Model (1), what it can do (2), temperature (3), answer length (4) |
| `assistant-answers.png` | Form of address (1), answer length (2), tone (3), knowledge source (4), no medical advice (5), privacy mode (6) |
| `assistant-voice-search.png` | Voice input (1–3), web search (4–7) |
| `assistant-escalation.png` | Escalation & consent (1–9) |
| `assistant-tools.png` | Skills (1), tools per connection (2), note on the model (3) |
| `language-texts.png` | The language record: language (1), subtitle (2), greeting (3), safety note (4), input placeholder (5) |
| `language-record.png` | Starter questions (1), the buttons of a row (2), questions & answers (3), load suggestions (4) |
| `language-widget.png` | A text of the window (1) |
| `connection-form.png` | A new connection: template (1), name (2), address (3), sign-in (4), personal data (5) |
| `connection-tools.png` | A saved connection: read tools again (1), a released tool (2), a locked tool (3), needs personal data (4), confirmation (5) |
| `tool-log.png` | The tool log: a refused call (1), a call that ran (2) |
| `skill-form.png` | A new skill: source (1), address on GitHub (2), load and check (3) |
| `skill-review.png` | A loaded skill: result of the check (1), the text (2), the checkbox (3), save (4) |

Check every image after taking it: frames on the right element, numbers
readable and not covering text, no real names, addresses or keys.
