---
name: hotel-stay
description: "Load when a verified guest wants something added to or changed in an existing stay: breakfast, parking or other extras, other dates, more or fewer persons, another room category."
tools: [GetReservationServiceOffers, GetReservationServices, BookService, GetReservationOffers, AmendReservation, GetReservation]
depends: [hotel-guest, hotel-payment]
---
FIRST: the guest check (hotel-guest) for this reservation. Several rooms: settle which reservation is meant.

EXTRAS
- GetReservationServiceOffers (id = reservation) lists what can be added to this stay, with prices and dates. Offer only what it returns; empty: not available for this stay.
- Services are quantities per date on the reservation: "breakfast for one on Monday" is count 1 on that date. Children's variants only for children, adult variants only for adults; ask for missing ages. A seasonal service only on its dates.
- Name the service, dates, quantity and total with currency. When the visitor agreed to exactly that: BookService (id, body: serviceId, count, dates). A success answer can be empty ({"data": null}); then read GetReservationServices and confirm only what is there.
- Payment for the extras: follow hotel-payment (one link for all extras added in this conversation, not one per extra).

OTHER DATES, PERSONS OR ROOM
- Only for channelCode Direct; a portal booking is changed on its platform.
- GetReservationOffers (id, arrival, departure, adults, children_ages) shows what the stay would cost with the change. Name the new total, the difference to the current total and the terms; act only after the visitor agreed to exactly that.
- AmendReservation (id, body: arrival, departure, adults, childrenAges, timeSlices with the ratePlanId of the chosen offer per night). Then read the reservation and confirm the new dates and total.
- Room category: the offers for the stay with the wanted category (unit_group_ids); never a category without an offer.

ALWAYS
- Early check-in or late check-out: only as a service the offers list; otherwise the team decides (contact skill).
- Never compute prices yourself, never book twice after an unclear answer: read the reservation first.
