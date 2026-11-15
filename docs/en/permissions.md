---
title: Permissions
summary: Which Contao permissions a user group needs for the assistants, their language records, the front-end module and the keys of SuperAgent, and what only administrators may do (preview, connections, skills, tool log).
audience: operations
language: en
updated: 2026-11-12
---

# Permissions

Administrators can do everything without any further setting. For other users,
all permissions are set in the back end under **User groups** (or **Users**),
with the names Contao shows there. The extension has no switches of its own.

## Administrators only

Some parts are reserved to administrators, whatever the user group allows:

| What | Why |
| --- | --- |
| **Connections**, **Skills**, **Tool log** (buttons above the list) | Through a connection every visitor works with the access of another system; a skill changes the rules of the model. Other users get "access denied" and do not see the buttons. |
| Seeing an assistant in **Preview only** | Only a logged-in back-end administrator sees the chat in Contao's preview, and the chat endpoints answer only with an administrator's session. Editors therefore cannot test an assistant before it is visible to all visitors. |

## Per function

| Function | Contao permission | Setting |
| --- | --- | --- |
| Opening the list of assistants | **Back end modules** | *NEO Digital*: **SuperAgent** |
| Creating, editing, deleting assistants | **Allowed fields**, table `tl_neo_agent` | the fields below |
| | **Table permissions** (Contao 5.7 and later), `tl_neo_agent` | Create, Update, Delete as needed |
| Language records (**Languages**; in the assistant's form **Edit texts and starter questions**, **Add language**), **Create with suggestions for "…"** | **Allowed fields**, table `tl_neo_agent_language` | the fields below |
| | **Table permissions** (Contao 5.7 and later), `tl_neo_agent_language` | Create, Update, Delete as needed |
| Choosing the **Image** | **Filemounts** | the folders with the images |
| Choosing a **Knowledge file** | **Filemounts** | the folder with the file |
| Creating the front-end module | **Back end modules** | *Layout*: **Themes** |
| | **Theme modules** | **Front end modules** |
| | **Front end modules** | **AI assistant (chat widget)** |
| | **Allowed fields**, table `tl_module` | Assistant `[agent]`, GA4 event "chat started" `[nsa_ga_event]`, plus Contao's fields of the module |
| Placing it in the page layout | **Theme modules** | **Page layouts** |
| AI connection, SerpAPI key, licence key, terms | **Back end modules** | *NEO Digital*: **Access keys and licences** |
| | **Allowed fields**, table `tl_neo_access` | the fields of the AI connection, SerpAPI key `[superagent_serpapi_key]`, the licence row and the terms of SuperAgent (see the documentation of the Basic bundle) |
| | **Table permissions** (Contao 5.7 and later), `tl_neo_access` | Update |
| **Add the service now** at **Consent category** (adds the AI chat to NEO Cookie Consent) | **Back end modules** | *NEO Digital*: **Cookie consent**; without it the field names an administrator instead of the link |

As with Contao's own permissions, a user gets the permissions of the user, those
of the groups, or both, depending on **Permission inheritance**.

## The fields

| Table | Fields |
| --- | --- |
| `tl_neo_agent` | Label `[title]`, Active `[published]`, Visible to `[visibility]`, Consent category `[consent_category]`, Display name `[assistant_name]`, Image `[avatar]`, Position `[widget_side]`, Model `[model]`, Reasoning effort `[reasoning_effort]`, Temperature `[temperature]`, Answer length (max tokens) `[max_tokens]`, Form of address `[tone_address]`, Answer length `[answer_length]`, Tone `[tone_style]`, Knowledge source `[grounding_source]`, Knowledge text `[grounding_text]`, Knowledge file `[grounding_file]`, No medical advice `[no_medical]`, Privacy mode `[privacy_mode]`, Allow voice input `[voice_input]`, Transcription model `[transcription_model]`, Voice input also in privacy mode `[voice_in_privacy]`, Allow web search `[web_search]`, Search service `[web_search_mode]`, Approved sources `[web_search_sources]`, Sources per question `[web_search_max]`, Always refer to `[referral_company]`, Preferred own solution `[preferred_solution]`, What the solution is for `[preferred_solution_note]`, Escalation email `[escalation_email]`, Forward to (name) `[escalation_name]`, Contact page `[contact_page]`, Additional fields in the form `[form_fields]`, Of these, required `[form_required]`, Preselected country code `[phone_country]`, Ask for consent to be contacted `[form_consent]`, Privacy policy `[privacy_page]`, Newsletter checkbox `[newsletter_module]`, Skills `[skills]`, Tools `[mcp_tools]`, System prompt `[system_prompt]` |
| `tl_neo_agent_language` | Language `[language]`, Subtitle `[subtitle]`, Greeting `[greeting]`, Safety note `[fact_note]`, Input placeholder `[input_placeholder]`, Starter questions `[starter_questions]`, Questions & answers `[canned]`, and the texts of the window (`[ui_…]`) |

**Label** of an assistant is a required field: allow it, otherwise the
assistant cannot be saved. **Visible to** decides whether visitors see the
assistant: give it only to people who may publish.

## Example group "Editors: chat"

For editors who keep the texts and the answers current; publishing, keys and
connections stay with an administrator:

1. **Back end modules**: **SuperAgent**.
2. **Allowed fields** → `tl_neo_agent`: Display name, Image, Personality &
   answer style, Knowledge source, Knowledge text, Knowledge file, Referral
   fields; `tl_neo_agent_language`: all fields.
3. **Table permissions** (Contao 5.7 and later): `tl_neo_agent` → Update;
   `tl_neo_agent_language` → Create, Update.
4. **Filemounts**: the folder with images and knowledge files.
5. Save.

Then log in with a test user, open an assistant and its **Languages**, and
check that the fields of the group are there.

## Several websites in one installation

Each assistant can be placed on any website through its front-end module; the
AI connection, the SerpAPI key, the licence and the terms exist once for the
installation. The licence is checked per domain.
