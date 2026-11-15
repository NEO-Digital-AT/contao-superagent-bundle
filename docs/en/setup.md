---
title: Setup
summary: Setting up SuperAgent once for the installation – editions and licence, AI connection and API keys, the terms, the front-end module, the mail sender, the consent tool and the parameters of the server configuration.
audience: operations
language: en
updated: 2026-11-13
---

# Setup

Do the steps in this order. Steps 2 and 3 take place on **NEO Digital ›
Access keys and licences** (page of the Basic bundle); the list of assistants
has a button of the same name. Setting up the assistants themselves is
described in the [User manual](usage/README.md).

## 1. Editions and licence

**Licence keys:** <https://store.neo-digital.at/extensions/superagent>

| | Without licence (free) | Basic | Plus | Pro |
| --- | --- | --- | --- | --- |
| Chat with answers from the knowledge source and the questions & answers | yes | yes | yes | yes |
| AI notice, safety rules (medical, privacy), languages, conversation across pages, design through CSS variables | yes | yes | yes | yes |
| Credit line "powered by" in the chat window | shown | – | – | – |
| **Navigation**: the assistant opens pages of the website | – | yes | yes | yes |
| **Contact**: forwarding form in the chat, prefilled by the assistant; opening the contact page | – | yes | yes | yes |
| **Web search** on approved websites | – | yes | yes | yes |
| **Tools of MCP servers**: connections with sign-in, tools with locks, choice per assistant, tool log | – | – | yes | yes |
| **Installed skills** from GitHub or a file, choice per assistant | – | – | yes | yes |
| **Voice input**: the visitor speaks, the text appears in the input field | – | – | – | yes |

**Entering the licence.** On *Access keys and licences*, section
**Licences**, enter the key in the row *SuperAgent* and save. Which edition
applies is decided only by the signed confirmation of the licence service for
the domain, on the server. The status of the row says what each edition adds.
The browser checks the confirmation before it leaves out the credit line; that
needs HTTPS.

The key is checked with the licence service when it is saved and once a day.
Only the key, the extension code, the domains of the website and the versions
are sent, no visitor data. If the service cannot be reached, the last
confirmation stays valid until its grace period ends.

Settings of features the licence does not include are not shown in the back
end: the sections *Voice input*, *Web search*, *Escalation & consent*,
*Installed skills* and *Tools of the connections*, and the buttons
**Connections** and **Skills** above the list. Their stored values stay and
appear again with a licence that includes them.

## 2. AI connection and API keys

On *Access keys and licences*, section **AI connection**, choose the
**Route** and enter the **Key** (see the documentation of the Basic bundle).
The connection is shared by all extensions; each assistant chooses its own
model. Until a key is stored, the website does not show the chat, and the list
of assistants says so ("No AI key is stored yet. …").

| Provider | Where the key comes from | Notes |
| --- | --- | --- |
| **Requesty** ([requesty.ai](https://www.requesty.ai/)) | Account › *API keys* › new key | Route **Requesty, EU route (processing in the EU)** for processing within the EU. Model ids have the form `provider/model-name`. For voice input, approve a transcription model under *Transcriptions* at Requesty. |
| **OpenAI** ([platform.openai.com](https://platform.openai.com/)) | *API keys* › new key | Route *OpenAI (direct)*; the model is an id of the model list of the account, without a provider prefix |
| **Own address** | – | Route *Own address (OpenAI-compatible)*. The service needs `POST {address}/chat/completions` and `GET {address}/models`; for voice input also `POST {address}/audio/transcriptions` and, for the list in the back end, `GET {address}/models/transcription`. |
| **SerpAPI** ([serpapi.com](https://serpapi.com/)) | Account › API key | Only for web search through a search engine. Section **Web search (SerpAPI)**, field **SerpAPI key**. Without it, an assistant set to SerpAPI reads the approved sources directly. |

All keys are stored encrypted (libsodium, keyed by the app secret) and never
shown again.

## 3. Terms

The terms of the extension are confirmed once per installation on *Access keys
and licences*, section **Terms of the extensions**: one passage for all
editions and one per edition (answers of the AI, safety rules, privacy, costs;
web search and forwarding; MCP servers, skills and knowledge sources; actions
with consequences and voice input). They are shown in the back-end language;
the German text is binding.

- An assistant can be made visible to **All visitors** only after the
  confirmation. Until then it stays in the preview ("Please confirm the terms
  on "Access keys and licences" first. …").
- The features of an edition stay locked until its passage is confirmed ("The
  features of the edition … are locked until its terms are confirmed.").
- A higher edition or a changed passage asks again; the list of assistants
  says so with a link.

## 4. The chat on the website

1. Create an assistant (see [User manual](usage/assistant.md)). A new
   assistant starts in **Preview only**.
2. Open **Layout › Themes › Front end modules**, click **New** and choose the
   module type **AI assistant (chat widget)** (group *NEO Digital*).
3. Section *Configuration*:
   - **Assistant**: which assistant the widget shows. Empty: the first active
     assistant.
   - **GA4 event "chat started"** (optional): the name of a Google Analytics 4
     event, e.g. `chat_ai`, sent when the first message is sent, only after
     statistics consent. Only the event name is sent, never content.
4. Add the module to the page layout, usually in the footer section. One module
   is enough for all pages of the layout; the language records follow the
   page language.

For websites in several languages: one assistant with one language record per
language, or one assistant per language (own knowledge, own forwarding address)
with one module per language. Both ways step by step:
[Several languages on the website](usage/languages.md#several-languages-on-the-website).

## 5. Mail sender

Forwarded requests go through the mailer of Contao. The sender is the
environment variable `MAILER_FROM` when it is set; otherwise set the parameter
in `config/config.yaml`:

```yaml
parameters:
    neo_super_agent.from_email: 'office@example.com'
```

The mail shows the name of the assistant as sender name. The visitor's address
is the reply-to address; the mail contains the message and the conversation so
far.

## 6. Consent tool

Each assistant has a **Consent category** (functional, statistics, marketing or
none). The chat waits for consent in that category when the page has one of
these consent interfaces:

- `window.cookieConsent.allows(category)` with the event
  `cookie-consent:change` on `window`, as NEO Cookie Consent provides it;
- for any other consent tool, `window.superAgentConsent(category)` with the
  event `superagent:consent-change` on `window`, from a small script of the
  website ([Customising](customising.md#consent)).

Without either of them, or with the category *No consent needed (chat available
at once)*, the chat is available at once. Without consent nothing is sent and
nothing is kept in the browser. Details: [Customising](customising.md#consent).

With NEO Cookie Consent the category needs an active service there, the
service *AI chat assistant* in the same category; otherwise its banner never
asks for it. Then the field **Consent category** says so in red, with the link
**Add the service now** (for users who may open the module *Cookie consent*),
which adds the service in this category and opens it. In the chat window
visitors read that the chat is not available at the moment, instead of a
consent request that cannot be answered. Where the banner does not appear at
all (no service needs consent), the chat starts without asking.

## 7. Server configuration

| Parameter (`config/config.yaml`, section `parameters`) | Default | Meaning |
| --- | --- | --- |
| `neo_super_agent.from_email` | empty | Sender of forwarded requests when `MAILER_FROM` is not set |
| `neo_super_agent.max_steps` | `12` | Model calls per reply at most (loading a skill counts); in the last step the model gets no tools and writes its answer |

Own templates for connections to MCP servers go into
`config/superagent/mcp-templates/<id>/` (see
[Connections to MCP servers](usage/connections.md#templates)).
