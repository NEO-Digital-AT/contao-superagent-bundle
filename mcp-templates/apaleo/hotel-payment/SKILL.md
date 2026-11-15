---
name: hotel-payment
description: "Load for payments: the open amount of a stay, a payment link for a prepayment or for extras, whether a payment arrived, cancelling an open payment link."
tools: [ListFolios, GetFolio, ListFolioPayments, GetFolioPayment, CreatePaymentLink, CancelPayment, ListCapturePolicies]
depends: [hotel-guest]
---
FIRST: the guest check (hotel-guest), unless the booking was made in this conversation.

WHAT IS OPEN
- ListFolios (reservation_ids) gives the folios of the reservation; GetFolio shows charges, payments and balance. A negative balance is the amount still to pay; pending payment links are already subtracted from what can still be paid (allowedPayment).
- ListFolioPayments (folio_id) shows payments and their status: Pending, Successful, Failed, Canceled. Only Successful counts as paid. A link is not a payment.

PAYMENT LINK
- When: a prepaid or non-refundable rate after booking (no question first), extras added in this conversation, or the visitor asks to pay the open amount.
- CreatePaymentLink on the guest folio of the reservation (folio_id), body: amount (amount and currency), countryCode of the payer, expiresAt (e.g. seven days, ISO time), description (what is paid), payerEmail; a new idempotency_key you keep. Amount: the prepayment due, the total of the extras, or the open balance — never more than allowedPayment.
- The answer holds only the payment id. Then GetFolioPayment (folio_id, payment_id) until it shows the url; give that url exactly, with amount and what it covers, in the same reply.
- Give the link alone on its own line as [Pay now](URL) in the visitor's language, with the amount above it.
- Error or no answer: GetFolioPayment or ListFolioPayments first; a pending link of the same amount is the one to give. Never create a second link for the same amount.
- Several rooms: one link per room's folio, or one for the room the visitor names.

CANCELLING A LINK
- Only when the visitor asks and the payment is still Pending: name the amount, then CancelPayment (folio_id, payment_id) after a yes.

NEVER
- Never ask for card numbers, security codes or bank logins in the chat; payment happens only on the link's page.
- Never say money arrived unless the payment is Successful. Refunds: hotel-cancellation; anything else: the team (contact skill).
