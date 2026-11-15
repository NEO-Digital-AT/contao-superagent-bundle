---
title: Installation
summary: Requirements, installing SuperAgent with the Contao Manager or Composer, what the installation adds, back-end languages, updating and removing.
audience: operations
language: en
updated: 2026-11-12
---

# Installation

## Requirements

| What | Needed for |
| --- | --- |
| Contao 5.3 to 6.0 (`contao/core-bundle ^5.3 \|\| ^6.0`) | everything; Composer refuses Contao 4 |
| PHP 8.2 to 8.5, as far as the Contao version supports it (Contao 5.7 needs 8.3, Contao 6.0 needs 8.4), with the extensions `ctype`, `intl` and `mbstring` | everything |
| `neo-digital/contao-basic-bundle` | installed automatically; it holds the AI connection, the SerpAPI key, the licence and the terms on **Access keys and licences** (and needs `sodium`) |
| An account with an OpenAI-compatible AI provider (e.g. Requesty, OpenAI) | every answer; until a key is stored, the website does not show the chat |
| A working mailer of Contao (`MAILER_DSN`) | forwarding requests to the team |
| Optional: the PHP extension `zip` | installing skills from a ZIP file (otherwise `SKILL.md` files only) |
| Optional: a SerpAPI account | web search through a search engine |
| Optional: `neo-digital/contao-brevo-bundle` or `contao/newsletter-bundle` | the newsletter checkbox of the forwarding form |
| HTTPS | the licence check in the browser; over plain HTTP the credit line stays even with a licence |

## Installing with the Contao Manager

1. Open the Contao Manager.
2. Search for `neo-digital/contao-superagent-bundle`.
3. Click **Add** and then **Apply changes**.
4. When the Contao Manager asks, run the database update and apply all changes
   it proposes.

## Installing with Composer

```bash
composer require neo-digital/contao-superagent-bundle
php vendor/bin/contao-console contao:migrate
```

`contao:migrate` creates the tables of the extension (assistants, language
records, connections, skills, tool log). Composer copies script, stylesheet
and icons to `public/bundles/neosuperagent/`; if that did not happen, run:

```bash
php vendor/bin/contao-console assets:install
```

## What the installation adds

- the front-end module **AI assistant (chat widget)** (group *NEO Digital*);
- the back-end module **NEO Digital › SuperAgent**, the list of assistants;
- on **Access keys and licences**: the use of the shared AI connection, the
  section **Web search (SerpAPI)**, the licence row *SuperAgent* and its terms.

Continue with [Setup](setup.md).

## Back-end languages

Back end and default texts of the chat are available in English, German,
French, Dutch, Italian, Polish, Czech, Hungarian, Slovenian, Croatian and
Spanish. A project file `translations/<domain>.<language>.yaml` overrides
single texts. Contao offers Hungarian and Croatian as back-end languages only
once they are enabled in `config/config.yaml`:

```yaml
contao:
    intl:
        enabled_locales: ['+hu', '+hr']
```

## Updating

1. Update the package with the Contao Manager or with
   `composer update neo-digital/contao-superagent-bundle`.
2. Run the database update (`contao:migrate`).
3. Read `CHANGELOG.md` of the new version. When a version needs new
   permissions or a step after the update, the changelog says so.
4. If a version changes a passage of the terms, the list of assistants asks
   for the confirmation again (see [Setup](setup.md#3-terms)).

Keys that an earlier version kept per assistant (route and key of the AI
connection, SerpAPI key)
are taken over to *Access keys and licences* once by the database update.
The suggested questions of the language records become their starter
questions, without an answer; until the database update has run, the chat
shows them as before.

## Removing

1. Remove the front-end module **AI assistant (chat widget)** from the page
   layouts.
2. Before removing: empty the licence key of SuperAgent on *Access keys and
   licences* and save, to remove the licence from the website.
3. Remove the package with the Contao Manager or with
   `composer remove neo-digital/contao-superagent-bundle`.
4. Run the database update. Contao then offers to delete the tables of the
   extension: assistants, texts, connections with their stored keys and
   tokens, skills and the tool log.
