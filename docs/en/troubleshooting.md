---
title: Troubleshooting
summary: Common problems with SuperAgent – chat not shown, no answer, features missing, forwarding, newsletter, voice input, connections to MCP servers, skills, credit line, missing permissions – with cause and solution.
audience: operations
language: en
updated: 2026-11-18
---

# Troubleshooting

Warnings above the list of assistants name the most common gaps: no AI key,
terms not confirmed, a website language without its own texts. Errors of the
AI service, of a forwarding mail or of a tool call are written to the log file
of the installation (`var/logs/`). Missing menu entries or fields are nearly
always missing permissions; see [Permissions](permissions.md).

## The chat on the website

| What you see | Why | What to do |
| --- | --- | --- |
| No chat button on the website | No AI key ("No AI key is stored yet. Until then the website does not show the chat."); no active assistant; the front-end module is not in the page layout; or the assistant is in **Preview only** | Store the key; set **Active**; add the module **AI assistant (chat widget)** to the layout; set **Visible to** to **All visitors**. |
| The chat appears only for me | **Visible to** is **Preview only**: only a logged-in administrator sees it in Contao's preview | Set **All visitors** (after confirming the terms). |
| **All visitors** cannot be chosen | The terms are not confirmed | Confirm them on *Access keys and licences* ([Setup](setup.md#3-terms)). |
| The window asks for consent and stays there | The consent category of the assistant is not given in the consent tool | Allow the category in the consent tool, or choose another **Consent category**. |
| The window says "The chat is not available at the moment …" | The consent tool never asks for the category of the assistant: NEO Cookie Consent has no active service in it (the field **Consent category** says so in red); the browser console names the category | **Add the service now** at the field, or add the service *AI chat assistant* in that category in the module *Cookie consent*; or choose another **Consent category**. |
| The chat shows its error notice instead of an answer | The AI service gave no answer (key, route, model, quota) | Run **Test connection** in the section **AI connection** on *Access keys and licences*; check the model of the assistant; read the log. |
| The answer comes at once at the end, without the status texts | The hosting buffers the response of the server | The chat works the same; for nginx the response already sets `X-Accel-Buffering: no`. |
| The assistant does not know a page | The page is not a regular page below the root page, not published, protected or set to `noindex`; the list is refreshed every ten minutes | Check the page; wait up to ten minutes. |
| The window is in English on a page in another language | The assistant of the module has no record in the language of the page, and that language is not one of the eleven | Create an assistant for that language (**Copy** one, change the **Language** of its record) and put its module in the layout of that language ([Several languages](usage/languages.md#several-languages-on-the-website)). |
| The credit line stays although a licence exists | The website runs without HTTPS, or the licence does not cover the domain | Use HTTPS; check the licence row on *Access keys and licences*. |
| The chat takes no input | The credit line of the free edition is cut off or covered by the design | Make it visible; it is part of the free licence. |
| The chat stops with an error for every question | The skill package of the extension is invalid (a skill longer than 2,800 characters, an unknown tool or dependency) | Read the log; install the package again. |

## Features

| What you see | Why | What to do |
| --- | --- | --- |
| Sections *Web search*, *Escalation & consent*, *Voice input*, *Installed skills* or *Tools of the connections* missing | The licence does not include the feature | Intended; a licence of the matching edition shows them again with their stored values. |
| "The features of the edition … are locked until its terms are confirmed." | The passage of the edition is not confirmed | Confirm it on *Access keys and licences*. |
| Web search does not work | The model cannot use tools ("This model cannot use tools. …"); or no approved sources; or SerpAPI without key | Choose a model that can use tools; enter sources; store the SerpAPI key or use *Read approved sources only*. |
| "No SerpAPI key is stored yet. Until then the assistant only reads the approved sources." | No SerpAPI key | Store it in the section **Web search (SerpAPI)**. |
| No forwarding in the chat | No **Escalation email** | Enter it; without it the assistant points to the contact options of the website. |
| Forwarded requests do not arrive | Mailer not set up, or no sender (`MAILER_FROM` or `neo_super_agent.from_email`) | Set up the mailer and the sender ([Setup](setup.md#5-mail-sender)). |
| "Did you mean …?" below the e-mail field | A typo of a well-known provider is likely | The visitor corrects or keeps the address. |
| The e-mail address is refused although it is typed right | Its domain has no MX, A or AAAA record | Use an address whose domain receives mail. |
| No newsletter checkbox in the form | The chosen module lacks a list, template or confirmation page; no Brevo key; the module *Subscribe* has no channel or is on no published page | The note at **Newsletter checkbox** names the reason; fix it. |
| No microphone button | No Pro licence; **Allow voice input** off; privacy mode on without **Voice input also in privacy mode**; or the browser has no microphone access | Check these; the visitor allows the microphone in the browser. |
| Voice input fails | No transcription model approved at the route (Requesty: *Transcriptions*), or the recording is longer than 60 seconds or larger than 4 MB | Approve a model; record shorter. |

## Connections and skills

| What you see | Why | What to do |
| --- | --- | --- |
| No buttons **Connections**, **Skills**, **Tool log** | Not an administrator, or no licence for Plus | Intended. |
| "Please enter an address starting with https://." | The address of the MCP server is not HTTPS | Enter the HTTPS address. |
| "The tools could not be read: …" | Server not reachable, sign-in missing or refused | Check address and sign-in; with OAuth click **Connect**. |
| Every MCP server unreachable | The server sends its outgoing traffic through a proxy in a private network; private networks are blocked | That setup is not supported. |
| "The sign-in at the service failed: …" | The service refused the OAuth sign-in, or needs an app of its own | Create the app at the service with the redirect address of the connection; enter client ID and secret. |
| "Lock not lifted: …" | The confirmation checkbox was not ticked | Tick **I have checked the released tools and understand the residual risk.** and save. |
| A ticked tool is not used | Locked, no longer at the server, connection not active or not confirmed, or the model cannot call tools | See the notes below the list of tools at the assistant; the tool log names the reason. |
| Tool log: "personal data not sent" | **Personal data** of the connection is **Do not send** and the call contained an identifier | Intended; switch privacy mode or the setting of the connection if the tool needs the data. |
| "This server cannot read ZIP files (the PHP extension zip is missing). …" | No `zip` extension | Upload the `SKILL.md` directly, or install `zip`. |
| "The text has … characters, at most … are allowed. …" | The skill is longer than 2,800 characters | Split it into smaller skills. |
| "The loaded text is not taken over yet. …" | The checkbox was not ticked | Tick **I have read the content and take it over.** and save. |
