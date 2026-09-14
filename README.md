# RentOS — Rental Home Management Platform

RentOS is a **multi-tenant rental and property management system**. It connects platform operators, property companies, home owners, staff, and renters in one product — covering the full lifecycle:

**List → search → apply → screen → lease → move-in → bill → maintain → renew / move-out → review.**

This repository is a production-grade Laravel + Filament application with a public listing site, three role-based admin portals, a Sanctum REST API, AI helpers, and Unicode-safe lease/invoice PDFs.

---

## Table of contents

1. [Highlights](#highlights)
2. [Tech stack](#tech-stack)
3. [First-party packages](#first-party-packages)
4. [Quick start](#quick-start)
5. [Demo accounts](#demo-accounts)
6. [User roles](#user-roles)
7. [Permission matrix](#permission-matrix)
8. [Feature catalog](#feature-catalog)
9. [Screens and URLs](#screens-and-urls)
10. [REST API](#rest-api)
11. [Database schema](#database-schema)
12. [Domain services](#domain-services)
13. [Scheduled jobs](#scheduled-jobs)
14. [Configuration](#configuration)
15. [Architecture](#architecture)
16. [Project structure](#project-structure)
17. [Tests](#tests)
18. [Roadmap](#roadmap)
19. [Author](#lets-build-something-exceptional)

---

## Highlights

- **SaaS multi-tenancy** — organizations (individual owners or property companies) isolated by `organization_id`, with Filament tenancy on the owner panel (`/owner/{org-slug}`).
- **Nine roles** — Super Admin, Org Admin, Home Owner, Assistant / Property Manager, Tenant, Vendor, Agent, Guarantor, Inspector.
- **Granular staff delegation** — assistants get per-permission toggles (finances, lease signing, listing publish, and more).
- **Public marketplace** — search published homes, filter by beds / pets / furnished / rent, open a listing, apply, book a viewing, save favorites and searches.
- **Leasing engine** — applications, AI risk scoring, document uploads, lease templates, send-for-signature, digital sign, activate, roommates on `lease_tenants`, notices, renewals.
- **Billing ledger** — rent invoices, deposits (hold / deduct / refund), late fees, partial payments, expenses, payouts, double-entry `ledger_entries`. Amounts stored in **minor units** (cents / poisha).
- **Maintenance workflow** — Submitted → Acknowledged → Scheduled → In Progress → Resolved → Rated, comments, priority SLA timers.
- **Operations** — viewings, inspections, document vault, staff permissions, availability blocks, in-app inbox, announcements, reviews, referrals.
- **AI** — listing copy, dynamic rent suggestion, application screening, owner FAQ chatbot via [laravel-ai-hub](https://packagist.org/packages/imrandevbd/laravel-ai-hub) (13 providers, failover, cost tracking).
- **Unicode PDFs** — Bengali / Arabic / Hindi / Latin lease and invoice documents via [laravel-unicode-pdf](https://packagist.org/packages/imrandevbd/laravel-unicode-pdf).
- **Unicode slugs** — organization-scoped unique slugs via [laravel-universal-slug](https://packagist.org/packages/imrandevbd/laravel-universal-slug).
- **Mobile-ready API** — Laravel Sanctum tokens for owner/tenant apps.

---

## Tech stack

| Layer | Choice |
|---|---|
| Backend | Laravel 13, PHP 8.3+ (developed on PHP 8.5) |
| Admin UI | Filament 4 (three panels) |
| Auth | Session (panels) + Laravel Sanctum (API) |
| Database | SQLite (default local) or MySQL 8+ |
| Queue / cache | Database driver by default; Redis-ready |
| Frontend (public) | Blade + Tailwind CSS 4 + Vite 8 |
| AI | `imrandevbd/laravel-ai-hub` |
| PDF | `imrandevbd/laravel-unicode-pdf` (native engine) |
| Slugs | `imrandevbd/laravel-universal-slug` |
| Scaffolding | `imrandevbd/laravel-filament-master` |
| Payments (designed) | Stripe, SSLCommerz, bKash, Nagad, card / bank / cash |

---

## First-party packages

These are first-class parts of RentOS, not optional add-ons.

### laravel-ai-hub

Used in:

- `App\Services\Ai\ListingAiService` — generate listing title/description; suggest market rent
- `App\Services\Ai\ScreeningAiService` — explainable application risk score (0–100)
- `App\Services\Ai\SupportChatService` — owner FAQ assistant
- Filament actions: **AI listing copy**, **AI write**, **AI screen**
- Owner page: **AI assistant** (`/owner/{org}/ai-assistant`)
- Super Admin: **AI Hub Studio** at `/ai-hub` (keys, models, playground, spend)

Jobs are tagged (`listing-copy`, `dynamic-pricing`, `application-screening`, `support-chat`) so cost shows up in AI Hub analytics.

If no provider key is configured, services fall back to safe heuristics so the UI still works.

### laravel-unicode-pdf

Used in:

- `App\Services\Documents\LeasePdfService`
- `App\Services\Documents\InvoicePdfService`
- Views: `resources/views/pdf/lease.blade.php`, `resources/views/pdf/invoice.blade.php`

Preset is chosen from organization country (`bengali` for BD, `arabic` for AE, `hindi` for IN, `latin` otherwise). Download actions sit on owner and tenant lease/invoice tables.

### laravel-universal-slug

`HasUniversalSlug` (and `#[Slug]`) on:

- Plan, Organization, Property, PropertyUnit, Listing, Vendor, Amenity

Property / listing / vendor slugs are unique **inside the organization** (`uniqueScope`).

### laravel-filament-master

Registered on the Super Admin panel. Visual Studio pages are **disabled** in `config/filament-master.php` because they still target Filament v3 form APIs. CLI remains available:

```bash
php artisan master:resource Property
php artisan master:all Listing
php artisan master:sync
```

A Filament 4 compatibility shim lives at `app/Support/FilamentV3CommandCompat.php`.

---

## Quick start

### Requirements

- PHP 8.3+ with `mbstring`, `pdo_sqlite` or `pdo_mysql`
- Composer 2
- Node 20+ (for the public Tailwind build)
- Optional: MySQL 8+, Redis, AI provider API keys, Noto fonts for extra PDF scripts

### Install

```bash
composer install
copy .env.example .env          # Windows
# cp .env.example .env          # macOS / Linux
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

### MySQL instead of SQLite

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rentos
DB_USERNAME=root
DB_PASSWORD=
```

```sql
CREATE DATABASE rentos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then `php artisan migrate --seed`.

---

## Demo accounts

Seeded by `database/seeders/RentosDemoSeeder.php`. Password for all: **`password`**.

| Role | Portal | Email | What you see |
|---|---|---|---|
| Super Admin | `/admin` | `admin@rentos.test` | All orgs, users, plans, listing moderation, tickets, AI Hub |
| Home Owner | `/owner` | `owner@rentos.test` | Rahman Homes portfolio, leases, invoices, AI tools |
| Property Manager | `/owner` | `staff@rentos.test` | Same org, delegated staff permissions |
| Tenant | `/tenant` | `tenant@rentos.test` | Active Dhanmondi lease, open rent invoice, maintenance ticket |

Demo data includes:

- SaaS plans: Starter / Growth / Portfolio
- Organization **Rahman Homes** (Dhaka, BDT)
- Occupied 2-bed at Dhanmondi Lake Residences (leased) with held security deposit
- Featured vacant Gulshan studio (published on the public site)
- Approved application, active lease, open monthly invoice
- Viewing request, saved search, favorite, notice of entry, inbox thread
- Move-in inspection checklist, tenant ID document, calendar block
- Tenant review, referral code `SARA2026`
- Vendor (QuickFix Plumbing), maintenance ticket, announcement, staff task, support ticket

---

## User roles

### Super Admin (platform owner)

Manages the SaaS across every organization.

- Create / suspend organizations and users
- Global subscription plans and feature limits
- Platform analytics (orgs, users, properties, published listings)
- Content moderation: approve / reject listings
- Amenity catalog
- Support tickets / helpdesk
- Audit log table (who changed what)
- Impersonation log table (ready for support takeovers)
- White-label fields on organizations (logo, color, custom domain)
- AI Hub Studio, payment/SMS/email settings (config + `platform_settings`)
- API keys for future Zapier / accounting integrations (settings store)

### Organization / Company Admin

Sits above individual owners when the tenant is a property management company.

- Multiple owners / portfolios under one org
- Assign staff to properties or regions (`property_ids` on membership)
- Org-level financial reporting and payouts

### Home Owner (landlord)

- Portfolio dashboard: properties, active leases, open receivables, open maintenance
- Add / edit properties (house, apartment, condo, townhouse, room, studio, commercial)
- Pricing: base rent, deposit, utilities included, seasonal rules JSON, featured boost
- Publish / unpublish listings; AI-generated copy
- Screen applications (credit-style risk score + notes + supporting documents)
- Digital lease create / send / sign / activate; PDF download
- Rent collection: invoices, record payment, payment history, late-fee rules
- Security deposit hold / deduction / refund
- Owner payouts (platform fee from `config/rentos.php`)
- Ledger viewer
- Maintenance inbox, vendor assign, workflow actions, comments
- Expenses per property (accounting export-ready)
- Announcements to a building or whole org; tenant inbox
- In-app conversations
- Staff tasks with due dates
- Document vault, inspections, reviews
- Staff invite with per-permission toggles
- Availability / calendar blocks
- Renewal fields: `renewal_offer_days`, `auto_renew`, `escalation_percent`
- Multi-currency (`BDT`, `USD`, `AUD`, `EUR`, `GBP`, `INR`)

### Home Owner Assistant / Property Manager / Staff

- Scoped by organization membership and optional property list
- Day-to-day: messages, showings, maintenance, listing edits
- Process applications when `process_applications` is granted
- Restricted finance view unless `view_finances` is on
- Can be marked `is_point_of_contact` for tenants
- Activity is written to `audit_logs`

### Tenant / Renter

- Public search + listing detail
- Save favorites (public listing + tenant portal + API)
- Saved searches with daily match alerts
- Viewing / tour booking (in-person or video)
- Submit application (income, employer, pets, background-check consent)
- View / download / **sign** lease PDF
- Pay invoices from the portal or API; payment history
- Submit maintenance with priority; comment; rate when resolved
- Building announcements and in-app messages
- Notice to vacate
- Document uploads on own leases
- Reviews (revealed when both sides have reviewed)
- Referral codes
- Roommate split via `lease_tenants.rent_share`

### Optional roles (schema-ready)

| Role | Purpose |
|---|---|
| Vendor / contractor | Job assignments, quotes, completion photos, ratings |
| Real estate agent / broker | List on behalf of owners, commission tracking |
| Guarantor / co-signer | Linked to an application; sign lease sections |
| Inspector | Scheduled move-in / move-out / safety inspections |

---

## Permission matrix

Assistant (and agent) permissions are defined in `config/rentos.php` and `App\Enums\AssistantPermission`.

| Capability | Super Admin | Org Admin | Owner | Assistant | Tenant | Vendor |
|---|---|---|---|---|---|---|
| Manage all organizations / plans | Yes | — | — | — | — | — |
| Impersonate / global audit | Yes | — | — | — | — | — |
| Org settings & branding | Yes | Yes | Yes | — | — | — |
| Property CRUD | All | Org | Org | Assigned | — | — |
| Publish listings | Moderate | Yes | Yes | `manage_listings` | Browse public | — |
| Process applications | View | Yes | Yes | `process_applications` | Submit own | — |
| Sign / activate leases | — | Yes | Yes | `sign_leases` | Sign own | — |
| View full P&L | Platform | Yes | Yes | `view_finances` | Own invoices | Own jobs |
| Record rent payments | — | Yes | Yes | `collect_rent` | Pay own | — |
| Approve maintenance cost | View | Yes | Yes | `approve_maintenance` | Submit / rate | Update assigned |
| Manage vendors | — | Yes | Yes | `manage_vendors` | — | Self profile |
| Message / announce | — | Yes | Yes | `message_tenants` | Inbox | Job chat |
| Document vault | Platform | Yes | Yes | `manage_documents` | Own docs | Job evidence |
| Schedule showings | — | Yes | Yes | `schedule_showings` | Request | — |
| Log inspections | — | Yes | Yes | `log_inspections` | Co-sign report | — |
| Invite staff | Yes | Yes | Yes | `manage_staff` | — | — |
| AI Hub Studio `/ai-hub` | Yes | — | — | — | — | — |
| Owner AI assistant page | — | Yes | Yes | Yes | — | — |

`User::hasPermission()` treats Super Admin, Org Admin, and Owner as full access inside their scope.

---

## Feature catalog

Legend: **Live** = Filament / public / API already wired. **Ready** = tables and models exist; UI can be finished on the same schema.

### 1. Search and discovery

| Feature | Status | Notes |
|---|---|---|
| Public home + featured listings | Live | `/` |
| Keyword / city search | Live | `/listings?q=` |
| Min bedrooms, max rent, pet-friendly, furnished filters | Live | Query string |
| Listing detail | Live | Address, rent, deposit, amenities, description |
| Apply from listing page | Live | Tenant session; also Sanctum API |
| Natural-language search hook | Ready | Home search box; AI parse can plug into `ListingAiService` |
| Favorites | Live | Public toggle, tenant list, `GET/POST/DELETE /api/.../favorite` |
| Saved searches + alert frequency | Live | Public “Save this search”, tenant CRUD, daily `SendSavedSearchAlertsJob` |
| Viewing / tour booking (in-person or video) | Live | Public request, owner confirm/complete, tenant cancel |
| Calendar blocks / iCal source | Live | Owner **Availability**; `source` can be manual / iCal / booking |
| Map / radius / school overlay | Later | Lat/lng already on `properties` |

### 2. Properties and listings

| Feature | Status | Notes |
|---|---|---|
| Property CRUD | Live | Owner panel + building wizard / overview / layout |
| Types: single-family, apartment, condo, townhouse, room, studio, commercial | Live | `PropertyType` |
| Status: draft, active, occupied, vacant, maintenance, archived | Live | |
| Address, geo, beds/baths, sqft, year built | Live | |
| Amenities JSON + amenity catalog | Live | Owner picker + Super Admin catalog |
| Photos, video tour, floor plan paths | Ready | columns on `properties` |
| House rules | Live | Editable on the building |
| Seasonal / dynamic pricing JSON | Ready | `pricing_rules` |
| Feature / boost listing | Live | `is_featured`, `featured_until` |
| Units for multi-unit buildings | Live | Floors → units → spaces via `BuildingGenerator` |
| AI listing copy | Live | Owner property + listing actions |
| AI rent suggestion | Live | Listing **AI rent** action |
| Platform listing moderation | Live | Super Admin |

### 3. Applications and screening

| Feature | Status | Notes |
|---|---|---|
| Submit application | Live | Public form, tenant list, API |
| Owner application inbox | Live | |
| AI risk score + notes | Live | Fallback income-to-rent heuristic |
| Application documents + verification status | Live | Owner **Upload document** action |
| Background / credit check fields | Live | consent + reviewer notes |
| Approve → create draft lease | Live | Owner action |
| Reject application | Live | Owner action with notes |
| Withdraw application | Live | Tenant action |

### 4. Leases and compliance

| Feature | Status | Notes |
|---|---|---|
| Lease templates (jurisdiction + clauses) | Live | Owner CRUD; selectable on lease |
| Draft / sent / partially signed / active / renewal / ended | Live | `LeaseStatus` |
| Send for signature | Live | Owner **Send** action |
| Activate lease | Live | Marks property occupied, listing leased, creates deposit + first rent invoice |
| Roommate / co-tenant split | Ready | `lease_tenants` |
| E-signature records (IP, timestamp) | Live | Owner + tenant **Sign**; `lease_signatures` |
| Unicode lease PDF | Live | Owner + tenant download |
| Notice to vacate / notice of entry / renewal offer | Live | Tenant vacate, owner inbox, daily renewal job |
| Auto-renew + rent escalation + late-fee rules | Live | Lease form fields |
| Guarantor role | Ready | `UserRole::Guarantor` |

### 5. Payments and financials

| Feature | Status | Notes |
|---|---|---|
| Invoices: rent, deposit, late fee, utility, maintenance, other | Live | |
| Invoice line items | Live | |
| Record payment (cash / card / bKash / Nagad / SSLCommerz / bank) | Live | Owner + tenant + API |
| Payment history | Live | Owner **Payments** table |
| Partial pay, overdue, void | Live | `InvoiceStatus` |
| Unicode invoice PDF | Live | |
| Late fees + grace days | Live | `ApplyLateFeesJob`, lease % rules |
| Monthly rent generation | Live | `GenerateMonthlyRentInvoicesJob` |
| Security deposit hold / deduct / refund / forfeit | Live | Owner **Deposits** + `DepositService` |
| Property expenses | Live | Owner panel |
| Owner payouts (gross, platform fee, net) | Live | **Schedule payout** from collected rent |
| Ledger (debit/credit, property + lease) | Live | Owner **Ledger** + written by `BillingService` |
| Multi-currency | Live | org + record `currency` |
| Payment plans / escrow / split deposit | Later | schema can extend invoices |

### 6. Maintenance

| Feature | Status | Notes |
|---|---|---|
| Tenant submit ticket | Live | Portal + API |
| Owner inbox, assign staff / vendor | Live | |
| Priority: emergency (4h), urgent (24h), routine (72h) SLA | Live | set on create |
| Status lifecycle actions | Live | Acknowledge, schedule, start, resolve |
| Photos JSON, estimated / actual cost, rating | Live | resolve cost + tenant **Rate** |
| Comments thread | Live | Owner and tenant comment actions |
| Vendor directory by trade | Live | |
| Preventive schedules | Later | can hang off tasks / inspections |

### 7. Communication

| Feature | Status | Notes |
|---|---|---|
| Building / org announcements | Live | Owner compose; tenant read inbox |
| Unified conversations + messages | Live | Owner **Inbox**, tenant **Messages** |
| Channels: in-app (email / SMS / push later) | Live | `messages.channel` defaults to `in_app` |
| Owner AI chatbot | Live | `/owner/{org}/ai-assistant` |
| Support tickets | Live | Super Admin |

### 8. Documents, inspections, reviews

| Feature | Status | Notes |
|---|---|---|
| Polymorphic document vault | Live | Attach to building or lease; tenant uploads on own leases |
| Expiry dates on documents | Live | `expires_on` |
| Move-in / move-out / routine / safety inspections | Live | Checklist repeater + complete action |
| Inspection PDF path | Ready | column present |
| Mutual-reveal reviews | Live | Tenant reviews building; owner reviews tenant; reveal action |
| Referral program | Live | Tenant generates codes (`SARA2026` in demo) |

### 9. Operations and staff

| Feature | Status | Notes |
|---|---|---|
| Tasks / to-dos with assignee and due date | Live | |
| Staff invite + permissions JSON | Live | Owner **Staff** with default assistant permissions |
| Point of contact flag | Live | Staff form toggle |
| Audit trail | Live | Writer + Super Admin **Audit logs** table |
| Impersonation log | Ready | `impersonation_logs` |

### 10. Platform / SaaS

| Feature | Status | Notes |
|---|---|---|
| Plans with monthly/yearly price, features, limits | Live | |
| Org status: trial, active, suspended, cancelled | Live | |
| Amenity catalog | Live | Super Admin |
| Branding: logo, primary color, custom domain | Ready | org columns |
| Platform settings key/value (global or per org) | Ready | `platform_settings` |
| Supported countries / currencies / gateways | Live config | `config/rentos.php` |

### 11. AI differentiators

| Feature | Status | Notes |
|---|---|---|
| Listing description generation | Live | |
| Photo enhancement suggestions | Later | vision via AI Hub `->image()` |
| Dynamic pricing recommendation | Live | Listing **AI rent** |
| Tenant screening risk score | Live | bias-aware prompt |
| Owner / tenant FAQ chatbot | Live owner | tenant can reuse the same service |
| Lease clause summarization | Later | feed `lease.terms` to AI Hub |
| Fraud / document authenticity | Later | `application_documents.verification_status` |
| Predictive maintenance | Later | age + history on properties |

### 12. Integrations (designed)

- Accounting: QuickBooks / Xero export from expenses + ledger
- Calendar: Google / Outlook / iCal (`calendar_blocks.source`)
- Smart home: locks, thermostats, leak sensors (later)
- Zapier / webhooks (later)
- Identity / KYC and credit bureaus (later)
- Gateways: Stripe, SSLCommerz, bKash, Nagad (record method today; live webhooks later)

---

## Screens and URLs

### Public site

| Page | URL |
|---|---|
| Marketing home + featured homes | `/` |
| Browse / filter listings | `/listings` |
| Listing detail, apply, viewing, favorite | `/listings/{listing}` |
| Save current search | `POST /saved-searches` |
| Health check | `/up` |

### Super Admin — `/admin`

| Screen | Path |
|---|---|
| Login | `/admin/login` |
| Dashboard + platform stats | `/admin` |
| Organizations | `/admin/organizations` |
| Users | `/admin/users` |
| SaaS plans | `/admin/plans` |
| Listing moderation | `/admin/listing-moderations` |
| Support tickets | `/admin/support-tickets` |
| Amenity catalog | `/admin/amenities` |
| Audit logs | `/admin/audit-logs` |
| AI Hub Studio | `/ai-hub` |

### Owner / staff — `/owner/{organization-slug}`

| Screen | Path suffix |
|---|---|
| Login | `/owner/login` |
| Dashboard (properties, leases, receivables, tickets) | `/` |
| Properties | `/properties` |
| Listings | `/listings` |
| Applications | `/applications` |
| Lease templates | `/lease-templates` |
| Leases | `/leases` |
| Viewings | `/viewings` |
| Notices | `/notices` |
| Invoices | `/invoices` |
| Payments | `/payments` |
| Deposits | `/security-deposits` |
| Expenses | `/expenses` |
| Payouts | `/payouts` |
| Ledger | `/ledger-entries` |
| Maintenance | `/maintenance-requests` |
| Vendors | `/vendors` |
| Tasks | `/tasks` |
| Inspections | `/inspections` |
| Document vault | `/documents` |
| Availability | `/calendar-blocks` |
| Inbox | `/conversations` |
| Announcements | `/announcements` |
| Reviews | `/reviews` |
| Staff | `/staff` |
| AI assistant | `/ai-assistant` |

### Tenant — `/tenant`

| Screen | Path |
|---|---|
| Login / register | `/tenant/login`, `/tenant/register` |
| Dashboard (leases, balance due, tickets) | `/tenant` |
| My leases + sign + PDF | `/tenant/leases` |
| My invoices + pay + PDF | `/tenant/invoices` |
| Maintenance create / comment / rate | `/tenant/maintenance-requests` |
| Applications | `/tenant/applications` |
| Viewings | `/tenant/viewings` |
| Favorites | `/tenant/favorites` |
| Saved searches | `/tenant/saved-searches` |
| Notices (incl. notice to vacate) | `/tenant/notices` |
| Documents | `/tenant/documents` |
| Messages | `/tenant/conversations` |
| Announcements | `/tenant/announcements` |
| Reviews | `/tenant/reviews` |
| Referrals | `/tenant/referrals` |

---

## REST API

Base URL: `/api`. Authenticated routes use `Authorization: Bearer {token}`.

### Auth

| Method | Path | Auth | Description |
|---|---|---|---|
| `POST` | `/api/auth/login` | Public | Body: `email`, `password`, optional `device_name`. Returns `token` + user. |
| `POST` | `/api/auth/logout` | Sanctum | Revoke current token |
| `GET` | `/api/me` | Sanctum | Current user |

### Listings and applications

| Method | Path | Auth | Description |
|---|---|---|---|
| `GET` | `/api/listings` | Public | Paginated published listings; `?q=` search |
| `GET` | `/api/listings/{listing}` | Public | Detail + property + organization |
| `POST` | `/api/listings/{listing}/favorite` | Sanctum | Save favorite |
| `DELETE` | `/api/listings/{listing}/favorite` | Sanctum | Remove favorite |
| `GET` | `/api/favorites` | Sanctum | Saved homes |
| `GET` | `/api/saved-searches` | Sanctum | Alert searches |
| `POST` | `/api/saved-searches` | Sanctum | `{ name, criteria, alert_frequency }` |
| `GET` | `/api/applications` | Sanctum | Caller’s applications |
| `POST` | `/api/listings/{listing}/applications` | Sanctum | Apply (`monthly_income`, `employer`, `occupants`, `has_pets`, `consent_background_check`) |
| `GET` | `/api/viewings` | Sanctum | My tours |
| `POST` | `/api/listings/{listing}/viewings` | Sanctum | Request a showing |

### Tenant portal

| Method | Path | Auth | Description |
|---|---|---|---|
| `GET` | `/api/leases` | Sanctum | My leases |
| `POST` | `/api/leases/{lease}/sign` | Sanctum | Record tenant e-sign |
| `GET` | `/api/invoices` | Sanctum | My invoices |
| `POST` | `/api/invoices/{invoice}/pay` | Sanctum | `{ "amount": 3500000, "method": "bkash" }` — amount in minor units |
| `GET` | `/api/maintenance` | Sanctum | My tickets |
| `POST` | `/api/maintenance` | Sanctum | Create ticket (`property_id`, `title`, `description`, `priority`) |
| `GET` | `/api/announcements` | Sanctum | Building / org notices |
| `GET` | `/api/notices` | Sanctum | Lease notices |
| `POST` | `/api/notices` | Sanctum | Notice to vacate |
| `GET` | `/api/conversations` | Sanctum | Inbox |
| `POST` | `/api/conversations/{conversation}/messages` | Sanctum | Reply |

Example login:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login ^
  -H "Accept: application/json" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"tenant@rentos.test\",\"password\":\"password\"}"
```

---

## Database schema

Row-level multi-tenancy: almost every operational table has `organization_id`. Super Admin queries are unscoped.

### Platform

- `users` — name, email, phone, avatar, **role**, **status**, locale, timezone, DOB, national ID, last login, current org
- `plans` — slug, monthly/yearly price, currency, features JSON, limits JSON
- `organizations` — owner, plan, type (individual/company), status, country, currency, timezone, tax ID, logo, branding, custom domain, trial/suspend timestamps
- `organization_user` — role, permissions[], property_ids[], point-of-contact, invite/accept
- `platform_settings` — group + key + value, global or per org
- `audit_logs` — event, morphs auditable, old/new JSON, IP, user agent
- `support_tickets` — subject, body, priority, status, assignee
- `impersonation_logs` — admin, target, start/end, reason
- `personal_access_tokens` — Sanctum
- `ai_hub_request_logs`, `ai_hub_settings` — package telemetry

### Portfolio

- `amenities`
- `properties` — full address + geo, pricing, amenities, photos, rules, featured
- `property_units`
- `listings` — public title/slug/description, rent, status, AI flag, published_at
- `calendar_blocks`
- `favorites`
- `saved_searches`
- `viewings`

### Leasing

- `lease_templates`
- `applications` — reference `APP-********`, income, risk score, documents relation
- `application_documents`
- `leases` — number `LSE-YYYYMMDD-*****`, term, rent rules, PDF path
- `lease_tenants`
- `lease_signatures`
- `notices`
- `referrals`

### Finance

- `invoices` — number `INV-YYYYMM-******`
- `invoice_items`
- `payments` — reference `PAY-**********`, gateway IDs
- `security_deposits`
- `deposit_deductions`
- `expenses`
- `payouts`
- `ledger_entries`

### Operations

- `vendors`
- `maintenance_requests` — reference `MNT-********`, SLA timestamp
- `maintenance_comments`
- `conversations`, `conversation_participants`, `messages`
- `announcements`
- `inspections`
- `documents` (polymorphic)
- `reviews` (polymorphic reviewee)
- `tasks`

Money is always **integer minor units**. `App\Support\Money::format(3500000, 'BDT')` → `৳35,000.00`.

---

## Domain services

| Class | Responsibility |
|---|---|
| `App\Services\Billing\BillingService` | Create rent invoices, record payments, apply late fees, post ledger lines, generate invoice PDF |
| `App\Services\Billing\PayoutService` | Schedule owner payouts (gross − platform fee) and mark paid |
| `App\Services\Leasing\LeaseService` | Create lease from application; activate (occupy unit, deposit invoice, first rent) |
| `App\Services\Leasing\LeaseSigningService` | Send for signature; record IP-stamped e-sign |
| `App\Services\Deposits\DepositService` | Deduct from hold or refund remaining deposit |
| `App\Services\Messaging\MessagingService` | Start threads and reply in-app |
| `App\Services\Reviews\ReviewService` | Submit reviews; mutual reveal |
| `App\Services\Documents\LeasePdfService` | Unicode lease PDF download / store |
| `App\Services\Documents\InvoicePdfService` | Unicode invoice PDF download / store |
| `App\Services\Ai\ListingAiService` | Copy + pricing |
| `App\Services\Ai\ScreeningAiService` | Risk score |
| `App\Services\Ai\SupportChatService` | Chat FAQ |

Traits:

- `BelongsToOrganization` — auto-fills tenant from Filament
- `RecordsActivity` — writes `audit_logs` on create/update/delete

---

## Scheduled jobs

Defined in `routes/console.php`:

| Job | When | Purpose |
|---|---|---|
| `GenerateMonthlyRentInvoicesJob` | 1st of month 06:00 | Open rent invoice per active lease if none this month |
| `ApplyLateFeesJob` | Daily 07:00 | Overdue rent past grace → late-fee invoice |
| `SendSavedSearchAlertsJob` | Daily 08:00 | Match new listings against saved search criteria |
| `SendLeaseRenewalRemindersJob` | Daily 09:00 | Create renewal-offer notices when leases enter the offer window |
| `model:prune` on `AiRequestLog` | Daily | Drop old AI telemetry |

Run the scheduler:

```bash
php artisan schedule:work
# or a system cron: * * * * * php artisan schedule:run
```

Queue (database driver by default):

```bash
php artisan queue:work
```

---

## Configuration

### `.env` (RentOS-specific)

```env
APP_NAME=RentOS
APP_URL=http://localhost:8000

# AI Hub
AI_HUB_PROVIDER=gemini
AI_HUB_FAILOVER=true
AI_HUB_FILAMENT=true
GEMINI_API_KEY=
OPENAI_API_KEY=
ANTHROPIC_API_KEY=

# PDF
UNICODE_PDF_ENGINE=native
UNICODE_PDF_DEFAULT_FONT="Noto Sans"

# Product defaults (see config/rentos.php)
RENTOS_CURRENCY=BDT
RENTOS_TIMEZONE=Asia/Dhaka
RENTOS_NOTICE_DAYS=30
RENTOS_RENEWAL_OFFER_DAYS=60
RENTOS_LATE_FEE_GRACE_DAYS=5
RENTOS_LATE_FEE_PERCENT=5
RENTOS_PLATFORM_FEE_PERCENT=2.5
```

Or leave keys empty and save them encrypted in `/ai-hub`.

### `config/rentos.php`

- Default currency / timezone
- Notice, renewal, late-fee, platform-fee defaults
- Supported currencies and countries
- Gateway feature flags
- Human labels for every assistant permission

---

## Architecture

```
                    ┌─────────────────────────────────────────┐
                    │  Public Blade site   /  /listings        │
                    └──────────────────┬──────────────────────┘
                                       │
  Filament /admin   Filament /owner/{org}   Filament /tenant
  Super Admin       Owner / staff           Renter
         │                    │                    │
         └──────────────┬─────┴────────────┬───────┘
                        │                  │
                 Laravel 13 HTTP     Sanctum /api
                        │
        ┌───────────────┼────────────────┐
        │               │                │
   BillingService  LeaseService     AI + PDF services
        │               │                │
        └───────┬───────┴───────┬────────┘
                │               │
         Eloquent + SQLite/MySQL     laravel-ai-hub
         audit_logs / ledger         laravel-unicode-pdf
         queue jobs                  laravel-universal-slug
```

Security:

- Panel access via `User::canAccessPanel()` (role + active status)
- Owner data scoped by Filament tenant
- Tenant resources scoped to `auth()->id()`
- Passwords hashed; Sanctum tokens for API
- AI Hub gated to Super Admin (`AIHub::auth` + `viewAiHub` gate)
- Audit log of mutations on properties, listings, applications, leases, invoices, payments, maintenance

---

## Project structure

```
app/
  Enums/                 Roles, statuses, priorities, permissions
  Filament/
    Resources/           Super Admin
    Owner/               Owner panel resources, widgets, AI page
    Tenant/              Tenant portal
  Http/Controllers/
    PublicListingController.php
    Api/                 Auth, listings, applications, tenant portal
  Jobs/                  Monthly invoices, late fees
  Models/                Domain + Concerns (tenant scope, audit)
  Providers/Filament/    Admin, Owner, Tenant panel providers
  Services/              Billing, leasing, deposits, messaging, reviews, AI, PDF
  Support/Money.php      Minor-unit formatting
database/
  migrations/            Platform → property → leasing → finance → operations
  seeders/RentosDemoSeeder.php
resources/views/
  public/                Listing site
  pdf/                   Lease + invoice
  filament/              AI assistant page
routes/                  web.php, api.php, console.php
docs/RENTOS.md           Compact technical spec
```

---

## Tests

```bash
php artisan test
```

`tests/Feature/RentosSmokeTest.php` covers:

- Public home
- Listing detail (apply + viewing)
- Listings API
- Owner properties (tenanted URL)
- Owner viewings, notices, deposits, documents, inspections, inbox, payments, staff
- Tenant invoices, viewings, notices, favorites, announcements, saved searches, applications
- Public viewing request
- Favorites API
- Super Admin organizations
- Owner AI assistant

---

## Roadmap

### Phase 1 — this repository (MVP)

Multi-tenancy, roles, properties (floors/units/spaces), listings, public apply/viewing/favorites/saved searches, applications, lease templates + e-sign, invoicing, payments, deposits, payouts, ledger, maintenance comments/workflow, announcements, inbox, inspections, document vault, reviews, referrals, staff permissions, audit log, Sanctum API, AI copy/pricing/screening/chat, Unicode PDFs, demo seed.

### Phase 2

Live payment webhooks (Stripe, bKash, Nagad, SSLCommerz), auto-pay, SMS/email notification templates, third-party background checks, iCal two-way sync.

### Phase 3

Vendor marketplace, IoT / smart locks, QuickBooks / Xero export, map + polygon + commute filters, Flutter / React Native apps on the existing API, offline inspection checklists.

### Phase 4

Predictive maintenance, fraud models, white-label custom domains, 1099 / local tax packs, Schedule E style owner reports.

---

## License

This application is private project code unless you add a license file. Bundled packages (`laravel-ai-hub`, `laravel-filament-master`, `laravel-unicode-pdf`, `laravel-universal-slug`) are MIT © Imran Dev BD.

---

## Support

- In-app: Super Admin **Support tickets**
- Owner: **AI assistant** page (uses AI Hub when configured)
- Package docs: [AI Hub](https://packagist.org/packages/imrandevbd/laravel-ai-hub) · [Filament Master](https://packagist.org/packages/imrandevbd/laravel-filament-master) · [Unicode PDF](https://packagist.org/packages/imrandevbd/laravel-unicode-pdf) · [Universal Slug](https://packagist.org/packages/imrandevbd/laravel-universal-slug)
- Compact spec: [docs/RENTOS.md](docs/RENTOS.md)

---

## Let's Build Something Exceptional

I'm actively open to: **Remote Senior Full-Stack Roles** · **Freelance Contracts** · **Technical Partnerships** · **Long-Term Collaborations**

in **Laravel** · **WordPress** · **React/Next.js** · **AI-powered Platforms** · **Security Audits** · **SaaS Architecture**

- Timezone: **UTC+6** (Dhaka/Rangpur) — flexible overlap for US, EU & Asia
- Available: **Immediately** · Production-first · Fast delivery · Transparent communication

| Platform | Link |
|---|---|
| Portfolio | [imrandev.bd](https://imrandev.bd) |
| LinkedIn | [linkedin.com/in/imranbru99](https://linkedin.com/in/imranbru99) |
| GitHub | [github.com/imranbru99](https://github.com/imranbru99) |
| X / Twitter | [@imrandev_bd](https://x.com/imrandev_bd) |
| YouTube | [@ImranDevBD](https://youtube.com/@ImranDevBD) |
| Instagram | [@imranbru99](https://instagram.com/imranbru99) |
| Facebook | [ExpertImranDev](https://facebook.com/ExpertImranDev) |
| TikTok | [@imrandev_bd](https://tiktok.com/@imrandev_bd) |
| Threads | [@imranbru99](https://www.threads.net/@imranbru99) |
| Pinterest | [@imrandev_bd](https://pinterest.com/imrandev_bd) |
| WhatsApp | [+880 1576-918420](https://wa.me/8801576918420) |
| Email | [me@imrandev.bd](mailto:me@imrandev.bd) |
| All links | [linktr.ee/ExpertImranDev](https://linktr.ee/ExpertImranDev) |

> "Security isn't an add-on — it's the foundation. Scale, speed, and trust drive every line of code I write."
>
> — **Imran Ahmed**
