---
title: How the assistant works
summary: How SuperAgent produces an answer – model, skills and tools, what the model receives, catalogue first and contents on demand, the limit of steps, models without tool calls, and the three endpoints of the chat window.
audience: operations
language: en
updated: 2026-11-12
---

# How the assistant works

## Model, skills, tools

The model decides what happens next; the skills say how; the tools carry it
out. The extension runs the loop, executes tools within the licence and the
settings, and writes nothing to the visitor itself.

The rules are small skill files (`skills/` of the package, English): a core
that always applies and one skill per area (contact, navigation, web search,
referral, medical, privacy). Installed skills (edition Plus) join them (see
[Installed skills](usage/skills.md)).

## What the model receives

- the core skill;
- SETTINGS: name, form of address, answer length, tone, what is available, the
  guidance of the language record and the **System prompt** of the assistant;
- STATE: date, page, page language;
- the CATALOG: one line per skill not loaded yet and per tool;
- the skills loaded so far;
- the list of pages (up to 150; a larger website gets a tool to search the
  list instead);
- the questions & answers of the language record, the starter questions that
  have an answer, and the knowledge source.

## Catalogue first, contents on demand

The model loads a skill with `load_module` before it acts in its area; the
tools of the skill come with it. A single tool definition is loaded with
`load_tools`. The skills for medical questions and privacy apply from the first
step when they are switched on in the assistant.

At most `neo_super_agent.max_steps` model calls per reply (default 12; loading
counts). A step that only loads again what is loaded already does not count,
up to three times per reply: some models load every tool once before its first
call. In the last step the model gets no tools and writes its answer.

## Memory of the conversation

Each message is a new request. So that the assistant does not ask again for
what it found out, the reply carries a memory: the results of the tools of
connections (the newest twelve, each cut at 4,000 characters, together at most
24,000) and the skills and tools that were loaded. The next reply starts with
these skills and tools loaded and gets the results in the section EARLIER TOOL
RESULTS of its instructions; a booking made in the conversation is known there.

Nothing of it is stored on the server: the memory travels with the
conversation as a token, encrypted and authenticated with the application
secret, like the token of the guest check. The window keeps it in
`sessionStorage` and sends it with the next message; **New conversation**
drops it. A token of another assistant, a changed one or one older than two
hours counts as empty.

The licence of each request decides, not what the conversation brings along.
Without the connections of the licence (no licence, a lower edition, or a
licence that no longer covers the website) the results of the connections in
the memory and the proof of an earlier guest check are dropped before the
model is asked; no tool of a connection is offered or called, also in a
conversation that started with the licence.

The skill files are checked when they are read (header, at most 2,800
characters per skill, known tools and dependencies). An invalid package stops
the chat instead of running with half its rules.

## Models without tool calls

Models that cannot call tools get all skills at once and use marker lines at
the end of the reply (`[[HANDOFF: …]]`, `[[PAGE: id now|offer]]`). The server
turns them into the same actions and removes them before the visitor sees the
reply. Web search does not work with such a model.

## Endpoints

The chat window talks to three addresses. Each is limited to 20 requests per
minute per IP address and checks and caps every input. An assistant in *Preview
only* answers only requests that carry the session of a back-end administrator.

### `POST /api/superagent/chat`

Request: `{message, confirmed?, guest?, memory?, history?, lang?, agent?, page?: {url, title, root}}`;
messages of the visitor are cut at 2,000 characters, answers of the assistant
in the history at 4,000, the history at 30 entries (what the assistant said
while its tools ran is part of its answer there).

Without privacy mode the server first looks for an e-mail address, phone
number, IBAN, card number, social security number or date of birth in the
message. When it finds one and `confirmed` is not `true`, it answers
`{confirm: ["email", "phone", …]}` (the kinds found), calls no AI service and
stores nothing; the window shows its notice and sends the message again with
`confirmed: true` only when the visitor confirms.

Answer: `{reply, handoff, navigate, message?}`, or `503 {"error":
"unavailable", message?}` when the AI service gives no answer.

- `handoff`: text to prefill the forwarding form, or `null`;
- `navigate`: `{url, title, now}` when the assistant opens a page (`now:
  true`) or offers it (`now: false`);
- `message`: the visitor's message as sent to the AI service, when privacy mode
  removed personal data; the script keeps this version;
- `guest`: what the visitor proved with the guest check of a connection, a token
  signed by the server; the script keeps it with the conversation and sends it
  back as `guest`;
- `files`: `[{url, name, size}]` when a tool delivered a file (a PDF, e.g. an
  invoice); the window shows a download button for each. The model never gets
  the file.

With `Accept: text/event-stream` (as the window sends it) the answer is a
stream: `step` events (`thinking`, `pages`, `form`, `search`), then `done` with
the same object, or `error`. Errors before the answer (limit, no assistant)
stay JSON. The response sets `X-Accel-Buffering: no` for nginx; on hosting that
buffers responses the steps arrive with the answer and the chat works the same.

### `GET /api/superagent/file/{token}`

A file of the chat, once: the response carries the file as an attachment
(`Cache-Control: no-store`); the file is deleted on the way out. A used or
unknown token, or one older than 15 minutes, gets `410` with a short notice in
the language of `?lang=`. The token (48 hexadecimal characters) is the only key.

### `POST /api/superagent/escalate`

Request: `{email, message, name?, phone?, company?, consent?, newsletter?,
transcript?, lang?, agent?}`; the message is cut at 4,000 characters, the
transcript at twelve turns.

Answer: `{ok: true}`; `400 {"error": "missing"|"invalid", fields: […]}` when a
required field is empty or the phone number is not one; `403 {"error":
"licence"}` without a licence that includes contact.

### `POST /api/superagent/transcribe`

Request: form data `audio` (WebM, Ogg, MP4/M4A, AAC, MP3 or WAV, at most 4 MB),
`agent?`, `lang?`.

Answer: `{text}` (in privacy mode with markers such as `[removed: e-mail
address]` instead of personal data); `403 {"error": "disabled"}` when voice
input is off, or privacy mode is on without *Voice input also in privacy mode*;
`403 {"error": "licence"}` without Pro; `503 {"error": "unavailable"}` when the
transcription fails.
