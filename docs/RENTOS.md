# RentOS product & technical specification

Multi-tenant rental/property management platform built on Laravel 13 + Filament 4.

## 1. Database schema (implemented)

Row-level multi-tenancy via `organization_id` on operational tables. Super Admin has no tenant restriction.

### Platform
- `users` — identity, `role`, `status`, `current_organization_id`
- `plans` — SaaS tiers, `features`, `limits`
- `organizations` — landlord company or individual owner, branding, plan
- `organization_user` — membership + delegated `permissions` + optional `property_ids`
- `platform_settings`, `audit_logs`, `support_tickets`, `impersonation_logs`

### Portfolio & discovery
- `amenities`, `properties`, `property_units`, `listings`
- `calendar_blocks`, `favorites`, `saved_searches`, `viewings`

### Leasing
- `lease_templates`, `applications`, `application_documents`
- `leases`, `lease_tenants`, `lease_signatures`, `notices`, `referrals`

### Finance
- `invoices`, `invoice_items`, `payments`
- `security_deposits`, `deposit_deductions`
- `expenses`, `payouts`, `ledger_entries`

### Operations
- `vendors`, `maintenance_requests`, `maintenance_comments`
- `conversations`, `conversation_participants`, `messages`, `announcements`
- `inspections`, `documents` (polymorphic), `reviews`, `tasks`

Money is stored in **minor units** (cents/poisha).

## 2. Role-permission matrix

| Feature | Super Admin | Org Admin | Owner | Assistant | Tenant | Vendor |
|---|---|---|---|---|---|---|
| Platform orgs / plans / settings | Yes | — | — | — | — | — |
| Impersonate / audit / tickets | Yes | Own org tickets | Create tickets | — | Create tickets | — |
| Org portfolio | All | Org | Org | Assigned properties | — | Assigned jobs |
| Listings publish | Moderate | Yes | Yes | If `manage_listings` | Browse public | — |
| Applications | View | Yes | Yes | If `process_applications` | Submit own | — |
| Sign leases | — | Yes | Yes | If `sign_leases` | Sign own | — |
| View P&L / reports | Platform | Yes | Yes | If `view_finances` / `view_reports` | Own invoices | Own payouts |
| Collect rent | — | Yes | Yes | If `collect_rent` | Pay own | — |
| Maintenance | View | Yes | Yes | Day-to-day; approve if `approve_maintenance` | Submit / rate | Update assigned |
| Documents vault | Platform | Yes | Yes | If `manage_documents` | Own docs | Job evidence |
| AI Hub Studio `/ai-hub` | Yes | — | — | — | — | — |

Assistant toggles live in `config/rentos.php` → `assistant_permissions` and `App\Enums\AssistantPermission`.

## 3. API endpoints (`/api`)

| Method | Path | Auth | Purpose |
|---|---|---|---|
| POST | `/auth/login` | Public | Sanctum token |
| POST | `/auth/logout` | Sanctum | Revoke token |
| GET | `/me` | Sanctum | Current user |
| GET | `/listings` | Public | Search published listings |
| GET | `/listings/{listing}` | Public | Listing detail |
| POST | `/listings/{listing}/favorite` | Sanctum | Save favorite |
| GET | `/applications` | Sanctum | My applications |
| POST | `/listings/{listing}/applications` | Sanctum | Apply |
| GET | `/leases` | Sanctum | My leases |
| GET | `/invoices` | Sanctum | My invoices |
| POST | `/invoices/{invoice}/pay` | Sanctum | Record payment |
| GET | `/maintenance` | Sanctum | My tickets |
| POST | `/maintenance` | Sanctum | Create ticket |

## 4. Screen inventory

### Public site
- Home (`/`)
- Listing search (`/listings`)
- Listing detail (`/listings/{listing}`)

### Super Admin (`/admin`)
- Dashboard + platform stats
- Organizations, Users, Plans
- Listing moderation
- Support tickets
- AI Hub (package nav → `/ai-hub`)
- Filament Master CLI/studio (visual studio disabled on Filament 4; `master:*` commands available)

### Owner / staff (`/owner/{org}`)
- Dashboard stats
- Properties, Listings, Applications, Leases
- Invoices, Expenses
- Maintenance, Vendors, Tasks
- Announcements
- AI assistant

### Tenant (`/tenant`)
- Dashboard
- Leases + PDF
- Invoices + pay + PDF
- Maintenance create/track

## 5. Phased roadmap

**Phase 1 (this repo — MVP)**  
Multi-tenancy, roles, properties, listings, applications, leases, invoicing, payments ledger, maintenance, announcements, public search, Sanctum API, AI copy/screening/chat, Unicode lease/invoice PDFs.

**Phase 2**  
Gateway webhooks (Stripe, bKash, Nagad, SSLCommerz), auto-pay, payout scheduling, e-sign canvas, background-check vendors, iCal sync.

**Phase 3**  
Vendor marketplace, IoT locks, QuickBooks/Xero, map/polygon search, commute filters, mobile offline inspections.

**Phase 4**  
Predictive maintenance, fraud models, white-label domains, 1099/tax packs.

## 6. Architecture

```
Browser / Mobile
    ├── Public Blade + Tailwind (listings)
    ├── Filament Admin  /admin
    ├── Filament Owner  /owner/{organization}
    └── Filament Tenant /tenant
                │
         Laravel 13 HTTP + Sanctum API
                │
     Domain services
       BillingService, LeaseService
       ListingAiService / ScreeningAiService / SupportChatService  → laravel-ai-hub
       LeasePdfService / InvoicePdfService                         → laravel-unicode-pdf
       HasUniversalSlug (org/property/listing/vendor/plan)         → laravel-universal-slug
                │
     MySQL or SQLite + Redis queues (database driver by default)
     Jobs: monthly rent invoices, late fees, AI log prune
     Storage: local / S3-compatible for IDs, photos, PDFs
```

Organization scoping: Filament tenancy on the owner panel; `BelongsToOrganization` fills `organization_id` on create; tenant resources filter by `auth()->id()`.
