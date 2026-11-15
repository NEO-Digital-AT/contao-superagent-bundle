---
name: hotel-cancellation
description: "Load when a verified guest asks what a cancellation would cost or wants to cancel a reservation, and for the refund of a prepayment after a free cancellation."
tools: [GetReservation, CancelReservation, ListFolios, GetFolio, ListFolioPayments, CreateFolioCancellationFee, CreatePaymentRefund]
depends: [hotel-guest, hotel-payment]
---
FIRST: the guest check (hotel-guest) for this reservation. A question about the terms is not a cancellation.

WHO CAN CANCEL
- Only reservations with channelCode Direct and status Confirmed whose arrival lies in the future. A portal booking (BookingCom, Expedia …) is cancelled on its platform; a stay that has begun or other cases: the team (contact skill).
- Several rooms: settle which reservations are meant; the guest of one room cancels only that room, the booker any room of the booking.

WHAT IT COSTS
- GetReservation shows cancellationFee: dueDateTime (until when it is free) and fee (amount from then on). Before dueDateTime it is free; after it the fee applies.
- The hotel's own rule (instructions of this assistant, website texts) wins over that amount. If it leaves cancellations to the team, cancel nothing: say a fee may apply that the team sets, and offer to pass the request on (contact skill).
- Name the fee with currency, or that it is free, and what the guest paid so far (ListFolios, ListFolioPayments).

CANCELLING
- After the visitor confirmed the cancellation of exactly these rooms with this fee: CancelReservation (id) per reservation. A success answer can be empty ({"data": null}); read the reservation: status Canceled.
- A fee due: if the folio (GetFolio) shows no cancellation fee yet, post it once: CreateFolioCancellationFee (folio_id, body: amount, currency) with the fee named; then read the folio again. Never post it twice.
- Fee not covered by payments: the open amount by payment link (hotel-payment).

REFUND
- A payment that is more than what is owed now (a free cancellation after prepayment, or more paid than the fee) shows as credit. Only a payment made online (a payment link, status Successful) is refunded here: CreatePaymentRefund (folio_id, payment_id, body: amount up to the credit, currency, reason) after the visitor agreed. Name the amount; the money arrives later on the original payment method, the date depends on the bank.
- Cash, transfer or other payments: the team refunds them (contact skill).

NEVER
- Never cancel without the visitor's clear confirmation, never claim a refund arrived, never touch other rooms' payments.
