# IROZAY DE PLUG — User Guide

Your complete Sales, Inventory & Business Management System. This guide covers
every module now that all 11 phases are built.

## Logging in & roles

Three roles exist, each seeing a different slice of the sidebar (and blocked at
the server level from typing in a restricted URL directly — not just hidden menus):

| Role | Can access |
|---|---|
| **Admin** | Everything, including Users & Roles and Settings |
| **Manager** | Everything except Users & Roles and Settings |
| **Sales Staff** | POS, Customers, and viewing product availability only |

An account set to "Deactivated" (Users & Roles → edit a user) cannot log in at all.

## Daily workflow

**Making a sale:** POS → search or browse products → for accessories, add a
quantity; for iPhones, click "Select IMEI" and pick the exact unit → optionally
select or quick-add a customer → choose payment method and amount paid →
Complete Sale. You land on the sale's receipt-ready detail page.

**Receiving stock:** Purchases → New Purchase → pick a supplier and date → add
line items (quantity + price) → for any iPhone line, you must capture each
unit's individual IMEI/color/condition/etc. before you can mark the purchase
received → Mark as Received posts the stock automatically.

**Handling a return:** open the original sale → Process Return → select the
item(s) and give a reason → the refund amount is calculated automatically from
what was actually sold. A returned iPhone becomes status `RETURNED` (not
automatically resellable — inspect it and decide whether to sell it again).

**Checking the day:** Dashboard shows today's sales, profit, expenses, net
profit, stock levels, and a real sales chart (Today/7-day/30-day/custom range).

## Module reference

- **Inventory** — every product, filterable by category/status/low-stock. Add,
  edit, and adjust stock for non-iPhone items here.
- **iPhones** — every individual IMEI-tracked device, grouped by model with
  in-stock/reserved/sold counts. Reserve a unit for a customer without selling
  it yet; cancel the reservation if the sale falls through.
- **Apple Watches / Accessories** — same Inventory screen, pre-filtered.
- **Purchases** — every stock-in event, with supplier, cost, and payment tracked.
- **Customers** — profiles with full purchase history, total spent, and
  outstanding balance (computed live from unpaid sale balances).
- **Suppliers** — contact info and full purchase history per supplier.
- **Expenses** — categorized (Rent, Electricity, Internet, Transport, Delivery,
  Packaging, Staff, Marketing, Other), feeds directly into Net Profit.
- **Reports** — Sales, Profit, Inventory, iPhone, Purchase, Expense, Customer,
  Supplier, Payment Method, and Best-Selling Products, each with a date filter
  and (mostly) a CSV export button for opening in Excel.
- **Users & Roles** (Admin only) — create staff accounts, assign roles,
  deactivate anyone who leaves. "View Roles & Permissions" shows exactly what
  each role can do. "View Audit Log" shows a running history of every
  significant action anyone has taken in the system.
- **Settings** (Admin only) — business name, phone, address, receipt footer,
  warranty message, return policy, and payment method list.

## Things worth knowing

- **Nothing important is ever silently deleted.** Sales, purchases, and
  customers with any history can't be removed — only deactivated or marked
  inactive — so your records stay complete for reporting and audits.
- **Every stock change is logged.** Inventory → Stock Movements shows a
  complete, permanent ledger of every increase and decrease, who did it, and why.
- **The audit log (Users & Roles → View Audit Log)** records logins, sales,
  edits, and deletions across the whole system — useful if you ever need to
  trace who did what and when.
- **A sale can complete with a balance owing** (partial/credit payment) — the
  unpaid amount shows on the customer's profile as their outstanding balance,
  but nothing currently reminds you to collect it; that's a manual follow-up.

## If something looks wrong

Check `storage/logs/laravel.log` for the technical error, or come back here and
paste it — most issues so far have been either a stale message left over from an
earlier build phase (cosmetic, not a functional bug) or a route ordering issue
(now fixed as of Phase 11). Both categories were swept for and corrected in this
phase; if you find another, it's genuinely useful to report since it likely
means an edge case wasn't covered.
