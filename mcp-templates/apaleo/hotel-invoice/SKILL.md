---
name: hotel-invoice
description: "Load when a verified guest wants an invoice: a copy of an existing one, a new invoice after the stay, the invoice for a cancellation fee, or a company or other recipient on the invoice."
tools: [ListFolios, GetFolio, ListInvoices, GetInvoice, GetInvoicePdf, ListInvoicePreviewPdf, UpdateFolio, CreateInvoice]
depends: [hotel-guest]
---
FIRST: the guest check (hotel-guest) for this reservation. An invoice of a booking with several rooms only for the booker.

EXISTING INVOICE
- ListInvoices (reservation_ids or folio_ids) lists the invoices with number and status. GetInvoicePdf (id) delivers the PDF: the visitor gets a download button under your reply (FILE READY); say it is ready below. Never invent a link and never describe content you did not see.
- Cancelled invoices and their corrections belong together; give the valid one, and the others when asked.

NEW INVOICE
- Possible when the folio (GetFolio) has balance 0 and all charges are posted: after check-out, or for a cancellation fee once it is paid. Open balance or charges still to come: say so; the team helps with an advance invoice (contact skill).
- RECIPIENT: ask who goes on the invoice: the guest, another person or a company (name, address, VAT number if any; a company without one is fine). The guest folio's recipient is changed with UpdateFolio (id = folio, [{"op": "replace", "path": "/debitor", "value": …}]), sending the whole recipient as you were told it. Repeat the recipient and act after a yes.
- CreateInvoice (body: folioId, languageCode: the visitor's language if the hotel invoices in it, otherwise en; a new idempotency_key). Then name the invoice number and deliver it with GetInvoicePdf.
- Several folios, unclear charges or a balance that is not 0: the team (contact skill).

PREVIEW
- ListInvoicePreviewPdf (folio_id, language_code) shows what the invoice would contain: a sample without number, not valid for accounting. Say so.

NEVER
- Never change the amounts or the recipient of an invoice that exists: a correction is the team's (contact skill).
- Never give an invoice of another reservation, and never one of the whole booking to the guest of one room.
