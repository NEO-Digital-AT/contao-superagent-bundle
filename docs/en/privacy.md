---
title: Privacy
summary: What the SuperAgent chat sends where and what is kept – messages, forwarding, newsletter, tools of connections, voice input, IP address, keys – and what belongs in the privacy policy of the website.
audience: operations
language: en
updated: 2026-11-12
---

# Privacy

## What goes where

| Data | Goes to | Kept |
| --- | --- | --- |
| Messages, conversation so far, address, title and language of the page | the server of the website → the AI service (route on *Access keys and licences*) → the provider of the model | not on the server of the website; at the AI service according to its terms |
| Search terms from the question (only web search via SerpAPI) | SerpAPI | according to its terms |
| E-mail address, message and conversation, and name, phone, company and consent if the form asks for them (only when the visitor forwards) | the escalation address, by e-mail | in that mailbox |
| E-mail address for the newsletter (only when the visitor ticks the box) | the newsletter module chosen at the assistant (Brevo or Contao's newsletter), double opt-in | according to that newsletter |
| Conversation for the next page | the visitor's browser tab (`sessionStorage`, only after consent) | until the tab is closed or **New conversation** |
| Memory of the conversation: results of the tools of connections and the loaded skills (only tools of connections, Plus) | with the reply to the visitor's browser tab as an encrypted token (`sessionStorage`), back to the server with the next message, from there with the instructions to the AI service; the visitor cannot read it | not on the server; in the tab until it is closed or **New conversation**; counts as empty after two hours |
| Personal data in messages, without privacy mode | the server checks the message before the call to the AI service; with an e-mail address, phone number, IBAN, card number, social security number or date of birth the window asks the visitor first; only after **Yes, send** the message goes on as written | nothing is kept for the question; afterwards as any message |
| Personal data in messages, with privacy mode | removed on the server before the call to the AI service, also from earlier messages; the log names only the type, e.g. `email` | the browser keeps the cleaned message |
| Details a tool call needs, and its result (only tools of connections, Plus) | the server → the MCP server of the connection; the result → the AI service; identifiers masked or held back per connection | at that service according to its terms; the tool log keeps only assistant, connection, tool, time, duration and result for 30 days |
| Voice recording (only voice input, Pro; with privacy mode only with **Voice input also in privacy mode**) | the server → the AI service → the provider of the transcription model, unfiltered; the text comes back into the input field, with privacy mode filtered first | not on the server (temporary upload file only); at the AI service according to its terms |
| IP address of the visitor | the server of the website only, to limit the requests per minute | one minute, as a hash in the application cache |
| Guest check (only connections whose template has one): reservation number, name and e-mail or phone the visitor gave | the server compares them with the record the booking system returns; the model gets only whether they match | nothing kept; failed attempts are counted under a hash of IP address and assistant for 24 hours; what was verified travels with the conversation as a signed token (two hours) |
| A file a tool delivered, e.g. an invoice (only tools of connections, Plus) | the server → the visitor's browser, by download; not to the AI service (the model learns only that the file is ready) | 15 minutes in the application cache under a random token, deleted at the first download |
| Event "chat started" (only when set in the front-end module, only with statistics consent) | Google Analytics: the event name only, no content | according to Google Analytics |
| Licence key, extension code, domains of the website, versions (only with a licence key, on saving and once a day) | the licence service | no visitor data is sent |

The IP address of the visitor is not passed to the AI service; the server of
the website makes the request. Approved websites read for web search receive no
visitor data. When an administrator loads a skill, GitHub is reached from the
back end only, without visitor data. API keys, header values and tokens are
stored encrypted (libsodium, keyed by the app secret).

## What the operator is responsible for

The terms of the extension (confirmed on *Access keys and licences*) say: the
operator is responsible for the privacy policy, the consent of visitors and the
data processing agreement with the AI service. The supplied texts are
templates, not legal advice.

- Conclude a data processing agreement with the AI service (Art. 28 GDPR) and
  list the processing in the record of processing activities.
- Choose the **Consent category** of each assistant to match the cookie banner
  and the privacy policy.
- Keep **Privacy mode** on unless an assistant needs personal data, e.g. for a
  tool of a connection.

## Privacy policy

A text block for the privacy policy in eleven languages, with the placeholders
to replace and the optional sentences to remove, is on
[Text for the privacy policy](privacy-texts.md).
