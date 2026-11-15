# Templates for connections to MCP servers

A template prepares a connection to a known MCP server (edition Plus): address and
sign-in, how its tools are treated, and optionally a skill that describes the limits
of those tools. An administrator picks it when creating a connection; the fields are
filled and the rules apply when the tools are read.

Shipped: `apaleo` (the hotel system apaleo, see [apaleo](#apaleo) below). Templates of a
project go into `config/superagent/mcp-templates/<id>/` of the Contao installation; a
project template with the id of a shipped one replaces it. An invalid template is left
out and logged.

## Folder

```
<id>/template.json   the template (id: lower-case letters, digits, hyphens)
<id>/SKILL.md        optional: the skill the template brings
<id>/<name>/SKILL.md optional: one of several skills (key "skills")
```

## template.json

```json
{
    "name": "Example booking system",
    "url": "https://mcp.example.com/mcp",
    "auth": "oauth",
    "help": "https://example.com/docs/mcp",
    "oauth": {
        "authorization_endpoint": "https://identity.example.com/connect/authorize",
        "token_endpoint": "https://identity.example.com/connect/token",
        "registration": false,
        "scope": "offline_access mcp:tools offers.read",
        "resource": false,
        "app_help": "https://example.com/docs/apps"
    },
    "tools": {
        "kinds": { "List*": "read", "Get*": "read", "Delete*": "delete" },
        "locked": ["Delete*", "RunNightAudit"],
        "customer_data": ["ListReservations", "GetReservation"],
        "recommended": ["ListOffers"]
    },
    "skill": "SKILL.md"
}
```

| Key | Meaning |
| --- | --- |
| `name` | Shown in the choice and used as the name of a new connection. |
| `url` | Address of the MCP server, HTTPS. |
| `auth` | `none`, `key`, `headers` or `oauth`. |
| `help` | Optional: the provider's guide to its MCP server; linked next to the choice. |
| `oauth.authorization_endpoint`, `oauth.token_endpoint` | Optional, both or none: fixed endpoints instead of the discovery from the server (RFC 9728/8414). |
| `oauth.registration` | `false`: the service offers no automatic registration (or it must not be used); the administrator enters the client ID and secret of an own app. Default `true`. |
| `oauth.scope` | Scopes requested at sign-in, separated by spaces. Request as few as the tools need: the service then refuses everything else, whatever is unlocked. Include `offline_access` where the service issues refresh tokens only with it. |
| `oauth.resource` | `false`: no resource indicator (RFC 8707) is sent; for authorization servers that reject it. Default `true`. |
| `oauth.app_help` | Optional: where the administrator creates the own app; linked in the note at *Connect*. |
| `tools.kinds` | Kind of tools the server does not mark (or marks wrongly): `read`, `change`, `delete`. Names or patterns with `*`. |
| `tools.locked` | Locked when they first appear, whatever their kind. |
| `tools.customer_data` | Show customer data: locked when they first appear and marked in the list. |
| `tools.recommended` | Marked in the list. |
| `tools.unlocked` | Free when they first appear although they change something or show customer data. For services whose own permissions decide what a tool may do there (e.g. the permissions of an app); `locked` wins. |
| `skill` | Optional: the `SKILL.md` in the template folder. It is installed once with the connection and appears under *Skills*; editors tick it at the assistant. Same format and limits as any installed skill. |
| `skills` | Optional: several skills, a list of `<name>/SKILL.md` in the template folder (the folder is the name of the skill). Installed like `skill`; a skill may depend on another skill of the same template (`depends`). |
| `setup` | Optional: the steps an administrator follows at the service, per language: `{"en": ["…", "…"], "de": ["…"]}`; English for any other language. Shown as a numbered list in the form of the connection; `{redirect_uri}` becomes the redirect address of the website with a button that copies it, `https://` addresses become links, `**text**` is bold. |

## Guest check

For booking systems and shops whose tools show or change the data of whichever record they
are given. A website visitor is anonymous, so with `guest_check` the protected tools run only
after the visitor proved a record is theirs, and only for that record:

```json
"guest_check": {
    "lookups": [
        {"tool": "GetReservation", "arguments": {"id": "{reference}", "expand": ["booker"]}},
        {"tool": "ListReservations", "arguments": {"bookingIds": ["{reference}"], "expand": ["booker"]}}
    ],
    "id": "id",
    "group": "bookingId",
    "persons": ["primaryGuest", "booker", "additionalGuests"],
    "group_person": "booker",
    "name": ["lastName"],
    "first_name": "firstName",
    "contact": ["email", "phone"],
    "arguments": ["id", "reservationId", "reservationIds", "bookingId", "bookingIds", "folioId", "folioIds"],
    "protected": ["GetReservation", "ListReservations", "ListFolios", "GetFolio", "Update*"],
    "creates": ["CreateBooking"]
}
```

| Key | Meaning |
| --- | --- |
| `lookups` | Tools the server calls itself to find the record, in this order; `{reference}` is the number the visitor gave. The first lookup that finds records counts. |
| `id` | Key of the id of a record (default `id`). A record is any object in the result with this key and one of `persons`. |
| `group`, `group_person` | Optional: key of the group a record belongs to (e.g. the booking) and the person who covers the whole group (e.g. the booker). |
| `persons` | Keys of the persons on a record (an object or a list of objects). |
| `name`, `first_name`, `contact` | Fields of a person: surname (default `lastName`), first name (default `firstName`), contact fields (default `email`, `phone`). |
| `arguments` | Arguments that name a record, at any depth (also inside `body`); every value must be a verified record or something that came back with it. A protected call without one of them is refused (no searches across records), and so is a protected call with an argument the tool's input schema does not declare. |
| `protected` | Tools the check applies to (names or patterns with `*`). |
| `creates` | Optional: tools that make a record for the visitor (e.g. a booking); the ids in their result count as verified for the rest of the conversation. |

The connection then brings the tool `verify_guest`: the model passes the number, the name and
the e-mail address or phone number the visitor gave; the server looks the record up and
compares. The same person must match both (surname, or first name and surname, without case
or accents; e-mail without case; phone by its last nine digits). The model gets only whether
it matched, never stored data. Five misses in 24 hours lock the check for that IP address and
assistant (the count is kept under a hash in the cache). A protected result is not passed
on when it holds a record of someone else (an object with the `id` key and one of
`persons` that is not verified) or is not JSON; ids of objects in a result that belong to a
verified record (a folio of the reservation, an invoice of the folio) become usable. What was
verified travels with the conversation as a token signed by the server; it expires after two
hours and with **New conversation**.

Tools that do not only read (by the template or the server) are locked when they first
appear, as for a connection without a template, unless the template unlocks them
(`tools.unlocked`). Administrators can lock any tool in the list of the connection; a lock
set there is kept when the tools are read again.

## Files in results

A PDF in the result of a tool (an embedded resource with a blob, a text that is the file in
Base64, or a Base64 value inside a JSON text) never goes to the AI service. The model gets
`[file: …]` and a note that the file is ready; the visitor gets a download button below
the reply. The file is kept 15 minutes in the application cache under a random token and
handed out once (`GET /api/superagent/file/{token}`). A protected tool's file is passed on
when the call named only verified records.

## apaleo

`apaleo/` connects the MCP server of apaleo (`https://mcp.apaleo.com/mcp`) with an app of
the hotel's own (fine-grained client, OAuth with client ID and secret, scopes
`openid profile offline_access`). What the assistant may do is decided by the permissions
of that app in apaleo and by the tool list of the connection.

- **Free when they first appear:** reading the hotel's facts and offers, and the tools
  the skills use: new bookings, the reservation, its folios, payments and invoices, and
  the changes listed under `tools.unlocked` (data corrections, extras, other dates,
  cancellation with its fee, payment links, refunds of online payments, invoice
  recipient, new invoices).
- **Locked:** everything that deletes, the night audit, data queries, exports, accounting,
  reports, logs, guest profiles, users, groups, blocks, companies, stored payment accounts;
  every other tool that changes something (rates, settings, folio charges, check-in) is
  locked because the template does not unlock it.
- **Guest check:** every tool that shows or changes a reservation, its folios, payments or
  invoices. The visitor proves it with the reservation or booking number, the name and the
  e-mail address or phone number stored for the guest or booker. A booking made in the chat
  counts as verified (`creates`).
- **Skills** (installed with the connection, English): `hotel-facts`, `hotel-booking`,
  `hotel-guest`, `hotel-stay`, `hotel-payment`, `hotel-cancellation`, `hotel-invoice`.
  Payment only by payment link; no card data in the chat. Invoices are downloaded in the
  chat (see [Files in results](#files-in-results)).
- **Setup** in eleven languages, shown in the form of the connection.
- The connection's **Personal data** must be **Send**: a booking needs the visitor's
  e-mail address and phone number.
