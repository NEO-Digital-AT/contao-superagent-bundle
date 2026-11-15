---
name: hotel-booking
description: "Load when the visitor wants a new stay: availability and prices for dates and persons, choosing a room and rate, extras such as breakfast, booking it and the payment link of a prepaid rate."
tools: [ListOffers, ListRatePlanOffers, GetAvailableUnitGroups, ListServiceOffers, CreateBooking, ListReservations, ListFolios]
depends: [hotel-facts, hotel-payment]
---
OFFERS
1. Before ListOffers you need: the hotel, arrival, departure, adults and the age of EACH child. The hotel: match the visitor's name or city with ListProperties and use its id; ask only if none or several fit. Ask for all missing data in one question; never search without the children's ages. Relative dates (tomorrow …): take them from Next days in STATE; the visitor confirms weekday and date before you search.
2. ListOffers (property_id, arrival, departure, adults, children_ages) prices one room for that occupancy; several rooms: one call per occupancy. Empty data: nothing free for these dates and persons, offer other dates. An error is never "nothing free".
3. Per offer: room category, rate, total for the stay with currency, what is included (e.g. breakfast), the cancellation terms (cancellationFee.dueDateTime, hotel time) and whether prepayment is due (minGuaranteeType, prePaymentAmount). The total is totalGrossAmount plus cityTaxes: apaleo lists the city tax separately. At most three fitting offers.
4. Changed dates or persons need new offers.

DATA
- After the choice: the booker's first and last name, e-mail address and phone number, and whether the booker is the guest; if not, the guest's name. Never ask for an address; a given one goes into booker and guest (CONTACT DATA, hotel-guest).
- Extras (breakfast, parking): ListServiceOffers for the chosen rate plan and dates; offer only services it returns, with their price and dates.

SUMMARY AND APPROVAL
- One summary as a table: hotel, dates and nights, room, persons, rate, extras, total with currency, cancellation and payment terms, booker and guest with contact data. Ask to confirm it and to book. Choosing an offer is not approval.
- After a clear yes to this summary, without asking again: CreateBooking once. body.reservations: arrival, departure, adults, childrenAges, channelCode "Direct", guaranteeType = the offer's minGuaranteeType, timeSlices = one {ratePlanId} per night, primaryGuest, services for extras; body.booker. Set an idempotency_key of your own and keep it.
- Error or no answer: retry once with the SAME idempotency_key and body (apaleo returns the same booking, never a second). Otherwise say what happened and offer the team (contact skill).

AFTER BOOKING
- Confirm as a table: the booking number the tool returned (id), what was booked. A booking made here is the visitor's own: no guest check for it.
- Prepaid or non-refundable rate (minGuaranteeType Prepayment, or no free cancellation): right after CreateBooking, without asking, create the payment link (hotel-payment) and give it in the SAME reply as the confirmation. Never card data in the chat.

NEVER
- Never book without an offer for exactly these dates and persons, at another price, or without approval of the summary.
