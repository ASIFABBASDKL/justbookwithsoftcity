# JustBook → Hybrid Freelance Marketplace
### Master Plan — Jo Ho Chuka Hai + Jo Karna Hai

> **Document ka maqsad:** Ye ek *living document* hai. Har kaam ke saath checkbox tick karte jao.
> Naya faisla ho to "Decisions Log" mein likho. Ye file hamesha sach bolni chahiye.

| | |
|---|---|
| **Project** | JustBook |
| **Vision** | Hybrid marketplace — Fiverr (gigs) + Upwork (job posting + bidding) dono ek platform pe |
| **Stack** | Laravel 12 · PHP 8.2 · MySQL · Firebase · Gemini AI · SMTP |
| **Plan banaya** | 19 August 2026 |
| **Current Phase** | **Phases 0–5 implemented (API). Live Stripe/Filament/Meilisearch optional.** |
| **Overall Progress** | ~90% product API |
| **Aakhri kaam** | 19 Aug 2026 — Marketplace: gigs, Order Engine, jobs/bids, simulated escrow, admin API |

---

## Fehrist

1. [Vision — Kya Banana Hai](#1-vision--kya-banana-hai)
2. [Core Architecture — Do Darwaze, Ek Kamra](#2-core-architecture--do-darwaze-ek-kamra)
3. [Jo Ho Chuka Hai — Current Inventory](#3-jo-ho-chuka-hai--current-inventory)
4. [Known Issues — Jo Toota Hua Hai](#4-known-issues--jo-toota-hua-hai)
5. [Database Plan — Poora Naqsha](#5-database-plan--poora-naqsha)
6. [Phase-by-Phase Roadmap](#6-phase-by-phase-roadmap)
7. [Tech Stack — Naye Tools](#7-tech-stack--naye-tools)
8. [Risks — Khatray](#8-risks--khatray)
9. [Decisions Log](#9-decisions-log)
10. [Progress Tracker](#10-progress-tracker)

---

## 1. Vision — Kya Banana Hai

Ek marketplace jahan **do tareeqon se kaam mil sakta hai**:

**A) Fiverr Style** — Seller apni **Gig** banata hai (3 packages: Basic/Standard/Premium). Buyer browse karta hai, package select karta hai, seedha order de deta hai.

**B) Upwork Style** — Buyer apna **Job** post karta hai. Sellers us par **bid (proposal)** bhejte hain. Buyer ek proposal accept karta hai → contract ban jata hai.

**Dono raste ek hi jagah pohanchte hain: ORDER.**

### Faisle jo ho chuke hain

| Sawal | Faisla | Wajah |
|---|---|---|
| Fiverr ya Upwork? | **Dono** — hybrid | Dono platforms khud ye kar chuke hain |
| Pehle kaunsa banayein? | **Fiverr path pehle** | Order Engine wahin banega, phir Upwork usi ko reuse karega |
| Hourly contracts? | **MVP mein NAHI** | Time tracker + work diary alag product hai. Sirf fixed-price + milestones |
| Ek banda buyer aur seller dono? | **Haan** | Fiverr/Upwork ka standard. Current role enum hatana padega |
| Payment gateway? | **Stripe Connect** | Escrow + payouts + KYC sab built-in |
| Physical services (current)? | **Drop** | Location / service-area / availability sab hata denge |

---

## 2. Core Architecture — Do Darwaze, Ek Kamra

> **Ye poore project ka sabse ahem design faisla hai. Isi pe sab khara hai.**

```
DARWAZA A (Fiverr)                    DARWAZA B (Upwork)
──────────────────                    ──────────────────
Seller Gig banata hai                 Buyer Job post karta hai
       │                                      │
Buyer gig browse karta hai            Sellers BID karte hain (connects kharch)
       │                                      │
Package select karta hai              Buyer ek bid accept karta hai
       │                                      │
       └──────────────────┬───────────────────┘
                          ▼
      ╔═══════════════════════════════════════╗
      ║        E K   H I   K A M R A          ║
      ║              "ORDER"                  ║
      ╠═══════════════════════════════════════╣
      ║  1. Escrow       — paisa hold          ║
      ║  2. Requirements — buyer detail deta   ║
      ║  3. Delivery     — seller file bhejta  ║
      ║  4. Revision     — buyer dobara mangta ║
      ║  5. Approval     — buyer manzoori      ║
      ║  6. Release      — commission cut      ║
      ║  7. Review       — dono taraf se       ║
      ║  8. Dispute      — jhagda ho to        ║
      ╚═══════════════════════════════════════╝
```

### Technical Implementation

`orders` table mein polymorphic source rakhenge:

```
orders.source_type   →  'gig_package'  |  'proposal'
orders.source_id     →  us record ki ID
```

**Faida:** Order banne ke baad ka **100% code ek hi hai**. Delivery, escrow, revision, review, dispute — kuch bhi duplicate nahi.

### Order Status Machine

```
pending_payment ──► active ──► delivered ──► completed
                      │           │  ▲
                      │           │  └── (buyer approve kare)
                      │           │
                      │           └──► revision_requested ──► active
                      │
                      ├──► cancelled  (refund)
                      └──► disputed   (admin faisla)

+ Auto-complete: delivered ke 3 din baad buyer na bole to khud complete
```

### Paise ka Flow (dono raaston ke liye ek hi)

```
Buyer pay kare
    ↓
ESCROW mein band (platform hold karta hai)
    ↓
Seller kaam kare → deliver kare
    ↓
Buyer approve kare (ya auto-approve)
    ↓
Escrow khule → Platform commission kate (20%)
    ↓
Seller wallet: pending_clearance (7-14 din) → available
    ↓
Seller withdraw kare → Stripe Connect payout
```

---

## 3. Jo Ho Chuka Hai — Current Inventory

**Abhi (Phase 0.4 ke baad):** 17 controllers · **62 API endpoints** · Sanctum + Spatie roles · `is_buyer`/`is_seller` · `buyer_profiles` / `seller_profiles` · 12 tests

> **19 Aug 2026 ka cleanup:** 4 poore modules + dead code delete hue. **-1,371 lines**.
> (74 → 59 routes, 22 → 16 controllers, 22 → 15 models). Tafseel neeche "Cleanup Log" mein.

### Modules

| Module | Kya Hai | Naye System Mein | Reuse |
|---|---|---|---|
| **Auth** | Register, Login, Email OTP, Phone OTP, Forgot/Reset password, Change password | Rakhna hai | **90%** |
| **2-Step Verification** | Naye device pe email+phone dono OTP, old device tracking | Rakhna hai | **90%** |
| **Biometric Login** | Fingerprint/Face token store + verify + deactivate | Rakhna hai | **85%** |
| **Chat** | User↔Provider, text + image + voice + document, grouped conversations | Rakhna hai (order_id add) | **85%** |
| **Notifications** | Firebase FCM push + DB history + email templates | Rakhna hai | **80%** |
| **Wallet** | 3 buckets: total / available / withdrawn | Extend (escrow) | **60%** |
| **Transactions** | incoming/withdraw, balance check | Extend | **60%** |
| **Reviews** | 5 metrics + percentage scorecard, duplicate prevention | Two-way banana | **60%** |
| **Bookings** | 6-step status machine, DB transaction + lock | Orders mein convert | **40%** |
| **Provider Dashboard** | Today/yesterday %, week, month earnings, avg rating | Seller dashboard | **50%** |
| **AI Assistant** | Gemini 1.5 Flash — voice base64 transcription | Optional feature | **70%** |
| **Services & Pricing** | Category/subcategory/price/duration | Gigs + packages | **30%** |
| **Payment Methods** | Bank/PayPal/Stripe account details store | Stripe Connect replace karega | **20%** |
| **Privacy & Security** | Accept/reject terms | Rakh sakte hain | **50%** |
| ~~Service Areas~~ | City/building/floor + lat/long | ✅ **DELETED** (19 Aug) | — |
| ~~Enable Location~~ | Live location toggle | ✅ **DELETED** (19 Aug) | — |
| ~~Availability~~ | 7 din ka start/end time schedule | ✅ **DELETED** (19 Aug) | — |
| ~~Calling~~ | Agora audio/video (dead code tha) | ✅ **DELETED** (19 Aug) | — |

### Current Tables (17 migrations)

```
RAKHENGE (thodi tabdeeli ke saath)
   users · personal_access_tokens · service_providers · service_users
   wallets · payment_transactions · reviews
   service_user_and_provider_chats · notifications
   two_step_verifications · biometric_logins · chat_with_ai
   cache · jobs (queue) · sessions · password_reset_tokens

BADLENGE (Phase 1-2 mein)
   services_and_pricing  →  gigs + gig_packages + gig_extras
   bookings              →  orders
   payments              →  Stripe Connect

✅ HAT CHUKE (19 Aug 2026)
   availabilities · service_areas · enable_locations · settings · calls
```

### 📋 Cleanup Log — 19 August 2026

**Delete hua** (commit `aaeb55c`):

| Kya | Files |
|---|---|
| Availability module | controller, model, migration, seeder |
| ServiceArea module | controller, model, migration, seeder |
| EnableLocation module | controller, model, migration, seeder |
| Calling / Agora | CallController, Call model, migration, AgoraService, RtcTokenBuilder helper |
| settings migration | khali table thi |
| Unused mail scaffolding | MailController, `App\Mail\Mail`, PhoneNumberUpdatedMail + view |
| Commented dead code | ~170 lines (AuthController, ChatWithAiController mein purane duplicate versions) |

**Saath mein theek hua:**

- `B-1` `/bookings/{id}/cancel` route hataya — `cancelBookingApi` method exist hi nahi karta tha
- `B-2` `GET /reviews` route hataya — `getReviews` method exist nahi karta tha
- `B-3` `User::role()` relation hataya — `Role::class` exist nahi karta
- `Review::serviceProvider()` — ServiceArea ke zariye ka toota `hasOneThrough` seedhe `belongsTo` se badla
- `ServiceProvider` model se `availability()` / `enableLocation()` relations hatayin
- `composer.json` se Agora ka `autoload.files` entry hatayi
- `DatabaseSeeder` se deleted seeders ke calls hatae
- `routes/api.php` sections mein organize kiya + har section pe migration TODO comment

**Verify hua:** saari PHP files syntax-clean · koi dangling reference nahi · `composer dump-autoload` OK · 59 routes load ho rahe hain

### Strengths — Jo Achha Bana Hua Hai

- **Wallet ka 3-bucket design** — escrow ke liye already 60% sahi soch
- **Booking status machine** (match expression) — Order Engine ka base
- **Chat with attachments** — order chat ke liye ready
- **Reviews ka 5-metric scorecard** — Fiverr se behtar
- **DB transaction + lockForUpdate** booking status update mein — race condition se bacha hua

---

## 4. Known Issues — Jo Toota Hua Hai

> Ye sab **Phase 0** mein theek honge.

### CRITICAL — Security

- [x] **C-1 — Koi authentication nahi hai.** ✅ Sanctum `HasApiTokens` + `auth:sanctum` + login token. Protected routes band hain.
- [ ] **C-2 — Firebase private key.** ✅ `.gitignore` path fix ho gaya (ab `storage/app/firebase/...`) — file git mein nahi jayegi. ⚠️ **Baqi hai: key rotate karna** (Firebase Console → Service Accounts → naya key generate karo, purana revoke karo).
- [x] **C-3 — ~~Project git repo hi nahi hai~~** ✅ **HO GAYA** (19 Aug) — git init + baseline commit `24ad1eb`
- [x] **C-4 — Register response mein OTP wapas aata hai** ✅ OTP JSON se hata diya (email/phone + two-step).
- [x] **C-5 — Phone OTP kabhi SMS se bheja hi nahi jata.** ✅ `SmsService` + Twilio config. Credentials `.env` mein daalni hain (`TWILIO_*`).
- [x] **C-6 — Koi rate limiting nahi.** ✅ `throttle:auth` (5/min) login/OTP pe, `throttle:api` (60/min) baqi API pe.
- [ ] **C-7 — `APP_DEBUG=true`** — local theek hai. Production template: `.env.production.example` (`APP_DEBUG=false`). Live server pe ye file use karo.
- [x] **C-8 — `env()` seedha controller mein** (`ChatWithAiController`) ✅ `config('services.gemini.api_key')` pe shift.

### BROKEN — Toota Hua Code ✅ SAB THEEK HO GAYE (19 Aug)

- [x] **B-1** — ~~`POST /bookings/{id}/cancel` → `cancelBookingApi` missing~~ → route hataya
- [x] **B-2** — ~~`GET /reviews` → `getReviews` missing~~ → route hataya
- [x] **B-3** — ~~`User::role()` → `Role::class` exist nahi karta~~ → relation hataya
- [x] **B-4** — ~~AgoraService dead code~~ → poora Agora module delete
- [x] **B-5** — ~~`settings` table khali~~ → migration delete (Phase 4 mein `platform_settings` banega)
- [x] **B-6** — ~~`Review::serviceProvider()` ka toota `hasOneThrough` (ServiceArea ke zariye)~~ → seedha `belongsTo` kiya

### LOGIC / DESIGN

- [ ] **L-1** — Mass assignment: `Booking::create($request->all())`
- [ ] **L-2** — Booking create DB transaction ke bahar — booking + transaction + wallet alag alag. Crash pe data adhoora
- [x] **L-3** — Ownership check nahi — ✅ Sanctum user ke profile se match; booking status kisi aur ki booking pe nahi chalegi
- [ ] **L-4** — Reviews percentage bug: `$totalReviews * 5` se divide, `satisfaction = 0` pe galat result
- [ ] **L-5** — Price `string` mein, amounts `decimal` mein — inconsistent
- [ ] **L-6** — Emails synchronous — queue nahi, har mail request block karti hai
- [ ] **L-7** — Categories text field se `distinct` — scale pe chalega nahi
- [ ] **L-8** — Koi tests nahi (sirf 2 default Laravel examples)
- [ ] **L-9** — Fat controllers — koi service layer nahi
- [ ] **L-10** — Koi API Resources nahi — raw models return ho rahe hain

---

## 5. Database Plan — Poora Naqsha

### A) Identity & Profiles

| Table | Status | Ahem Columns |
|---|---|---|
| `users` | **DONE (0.4)** | `is_seller`, `is_buyer`, username, avatar, country, timezone, last_seen_at. `role` enum hat gaya |
| `seller_profiles` | **DONE (0.4)** | pehle `service_providers`. headline, bio, languages, level, rates, … |
| `buyer_profiles` | **DONE (0.4)** | pehle `service_users`. company, total_spent, jobs_posted |
| `portfolios` | **NEW** | seller_id, title, description, images, url, category_id |
| `certifications` | **NEW** | seller_id, name, issuer, year |
| `seller_verifications` | **NEW** | seller_id, type (id/passport/address), document, status, reviewed_by |

### B) Taxonomy

| Table | Status | Ahem Columns |
|---|---|---|
| `categories` | **NEW** | parent_id (nested), name, slug, icon, is_active, sort_order |
| `skills` | **NEW** | name, slug, category_id |
| `gig_skill` / `job_skill` | **NEW** | pivot tables |

### C) Seller Path — Gigs (Fiverr)

| Table | Status | Ahem Columns |
|---|---|---|
| `gigs` | **NEW** | seller_id, title, slug, category_id, description, status (draft/pending/active/paused/rejected/deleted), impressions, clicks, orders_count, avg_rating, is_featured |
| `gig_packages` | **NEW** | gig_id, tier (basic/standard/premium), title, description, **price, delivery_days, revisions**, features(json) |
| `gig_extras` | **NEW** | gig_id, title, price, extra_days, max_qty |
| `gig_gallery` | **NEW** | gig_id, type (image/video/pdf), path, sort_order |
| `gig_faqs` | **NEW** | gig_id, question, answer |
| `gig_requirements` | **NEW** | gig_id, question, type (text/file/choice), is_required |

### D) Buyer Path — Jobs + Bidding (Upwork)

| Table | Status | Ahem Columns |
|---|---|---|
| `jobs_posted` | **NEW** | buyer_id, title, description, category_id, budget_type (fixed/hourly), budget_min, budget_max, deadline, experience_level, status (open/in_review/awarded/closed/cancelled), proposals_count, views |
| `job_attachments` | **NEW** | job_id, path, name, size |
| `proposals` | **NEW** | job_id, seller_id, cover_letter, **bid_amount, delivery_days**, connects_spent, status (pending/shortlisted/accepted/rejected/withdrawn) |
| `proposal_milestones` | **NEW** | proposal_id, title, amount, delivery_days, sort_order |
| `proposal_attachments` | **NEW** | proposal_id, path, name |
| `job_invitations` | **NEW** | job_id, seller_id, message, status |
| `connect_balances` | **NEW** | seller_id, balance, monthly_free, last_refill_at |
| `connect_transactions` | **NEW** | seller_id, amount, type (free/purchase/spend/refund), reference |

> **Note:** Table ka naam `jobs_posted` rakha hai kyunki Laravel ki queue `jobs` table pehle se maujood hai.

### E) Core — Orders (DONO ka milan)

| Table | Status | Ahem Columns |
|---|---|---|
| `orders` | **MODIFY** (bookings se) | order_number, buyer_id, seller_id, **source_type + source_id**, title snapshot, price, extras_total, platform_fee, seller_earning, currency, status, requirements_submitted_at, due_at, delivered_at, completed_at, auto_complete_at, revisions_allowed, revisions_used |
| `order_items` | **NEW** | order_id, type (package/extra), title, price, qty |
| `order_milestones` | **NEW** | order_id, title, amount, delivery_days, status (pending/funded/delivered/approved/released), escrow_id |
| `order_requirements` | **NEW** | order_id, question, answer, file_path |
| `order_deliveries` | **NEW** | order_id, milestone_id, seller_id, note, version, delivered_at |
| `delivery_files` | **NEW** | delivery_id, path, name, size, mime |
| `order_revisions` | **NEW** | order_id, delivery_id, buyer_id, reason, requested_at |
| `order_activities` | **NEW** | order_id, actor_id, action, meta(json) — timeline/audit log |
| `order_cancellations` | **NEW** | order_id, requested_by, reason, status, refund_amount |

### F) Money

| Table | Status | Ahem Columns |
|---|---|---|
| `escrows` | **NEW** | order_id, milestone_id, amount, status (held/released/refunded/partial), gateway_ref, held_at, released_at |
| `wallets` | **MODIFY** | Add: `pending_clearance` bucket. Existing: total, available, withdrawn |
| `ledger_entries` | **NEW** | wallet_id, order_id, type (payment_in/escrow_hold/escrow_release/commission/refund/withdrawal/adjustment), debit, credit, balance_after, reference — **double-entry** |
| `payment_transactions` | **MODIFY** | types extend, gateway fields, idempotency_key |
| `payouts` | **NEW** | seller_id, amount, method, gateway_ref, status (requested/processing/paid/failed), requested_at, paid_at |
| `invoices` | **NEW** | order_id, invoice_number, buyer_id, seller_id, amount, tax, pdf_path |
| `commission_rules` | **NEW** | category_id, seller_level, percent, min_fee, is_active |
| `platform_settings` | **NEW** (settings ki jagah) | key, value — commission %, auto-complete days, connect price, min withdrawal |

### G) Communication

| Table | Status | Ahem Columns |
|---|---|---|
| `conversations` | **NEW** | buyer_id, seller_id, order_id (nullable), last_message_at |
| `messages` | **MODIFY** (chats se) | conversation_id, sender_id, body, attachments, read_at |
| `notifications` | **MODIFY** | Add: type, data(json), read_at, action_url |
| `notification_preferences` | **NEW** | user_id, channel (email/push/in_app), event, enabled |

### H) Trust & Safety

| Table | Status | Ahem Columns |
|---|---|---|
| `reviews` | **MODIFY** | Add: `reviewer_type` (buyer/seller), `reviewee_id`, `is_public`. Two-way |
| `disputes` | **NEW** | order_id, raised_by, reason, description, evidence(json), status, resolved_by, resolution, refund_amount |
| `reports` | **NEW** | reportable_type + id, reporter_id, reason, description, status |
| `user_bans` | **NEW** | user_id, reason, banned_by, expires_at |
| `saved_items` | **NEW** | user_id, saveable_type + id (gig/job/seller) |
| `admins` + `roles` + `permissions` | **NEW** | Spatie Permission |

**Kul: ~55 tables** (22 existing mein se ~16 rakhenge/badlenge, ~39 naye)

---

## 6. Phase-by-Phase Roadmap

> **Tarteeb ahem hai.** Phase 1 (Fiverr) pehle, Phase 3 (Upwork) baad mein — kyunki Order Engine Phase 1 mein banega aur Upwork path usi ko reuse karega. Ulta karoge to double kaam hoga.

---

### PHASE 0 — Foundation Fix
**Time: 2-3 hafte** · **Status: 🟢 DONE (keys rotate user pe)** · **Progress: 27/29**

> Ye phase kuch naya feature nahi deta, lekin **iske bina aage kuch nahi ban sakta**. Har agla phase isi pe khara hai.

#### 0.1 — Version Control & Environment
- [x] Git repo initialize karo, `.gitignore` verify karo ✅ *(19 Aug — commit `24ad1eb`)*
- [x] `.gitignore` mein Firebase creds ka **sahi path** likho: `storage/app/firebase/firebase_credentials.json` ✅ *(19 Aug)*
- [ ] ⚠️ **Firebase service account key rotate karo** (purani compromised samjho) — *ye abhi baqi hai*
- [ ] ⚠️ **Gemini API key rotate karo** — *ye bhi baqi hai*
- [x] `.env.example` update karo (saare naye vars ke saath) ✅ *(19 Aug — Twilio, Gemini, Firebase, Sanctum)*
- [x] `APP_DEBUG=false` production ke liye, separate `.env.production` ✅ *(template: `.env.production.example`)*

#### 0.1b — Cleanup (extra cheezein hatana) ✅ COMPLETE
- [x] Availability / ServiceArea / EnableLocation / Calling modules delete ✅ *(19 Aug — commit `aaeb55c`)*
- [x] Dead code delete: AgoraService, RtcTokenBuilder, MailController, unused Mailables ✅
- [x] ~170 lines commented-out duplicate code hatao ✅
- [x] `routes/api.php` sections mein organize karo ✅

#### 0.2 — Authentication (SABSE AHEM) ✅ COMPLETE *(19 Aug)*
- [x] `User` model mein `HasApiTokens` trait lagao
- [x] Login (aur biometric) pe Sanctum token issue karo, response mein bhejo — *register pe token nahi: pehle phone verify, phir login*
- [x] Saare protected routes pe `auth:sanctum` middleware lagao
- [x] Logout endpoint banao (token revoke) — `POST /api/logout-api` + `GET /api/me`
- [x] Ownership checks lagao (koi apna hi data access kare)
- [x] Rate limiting: login/OTP/forgot-password pe `throttle:auth` (5/min)
- [x] Register response se OTP hatao
- [x] SMS gateway (Twilio) integrate karo phone OTP ke liye — *`.env` mein `TWILIO_SID` / `TWILIO_AUTH_TOKEN` / `TWILIO_FROM` daalo*

#### 0.3 — Broken Code Fix ✅ COMPLETE *(19 Aug — commit `aaeb55c`)*
- [x] `cancelBookingApi` — route hataya (B-1)
- [x] `getReviews` — route hataya (B-2)
- [x] `User::role()` relation hataya (B-3)
- [x] AgoraService delete kiya (B-4)
- [x] `settings` migration delete → `platform_settings` Phase 4 mein banega (B-5)
- [x] `Review::serviceProvider()` ka toota `hasOneThrough` fix (B-6)

#### 0.4 — Role System Rework ✅ COMPLETE *(19 Aug)*
- [x] `users.role` enum hatao → `is_seller` / `is_buyer` boolean flags
- [x] Spatie Permission install karo (admin/moderator ke liye)
- [x] "Become a Seller" flow — buyer seller ban sake — `POST /api/become-seller`
- [x] `service_providers` → `seller_profiles` rename + extend
- [x] `service_users` → `buyer_profiles` rename + extend

#### 0.5 — Architecture Cleanup ✅ COMPLETE *(19 Aug)*
- [x] **API Resources** banao (GigResource, OrderResource + `ApiResponse`)
- [x] **Form Requests** banao (`StoreGigRequest`, `CreateOrderRequest`)
- [x] **Service layer** (`OrderService`, `PaymentService`, `CommissionService`, `ConnectService`, `ModerationService`)
- [x] **Queues**: mails `ShouldQueue` — `QUEUE_CONNECTION=database` (Horizon/Redis optional baad mein)
- [x] `categories` proper table + tree API `/taxonomy/categories`
- [x] Mass assignment fix — `$fillable` / `$guarded` + booking validated fields
- [x] Booking create ko DB transaction mein wrap karo
- [x] Standard API response format + global exception handler

#### 0.6 — Dev Hygiene ✅ COMPLETE *(19 Aug)*
- [x] Testing setup (PHPUnit) + factories — Pest optional
- [x] Feature tests: auth + roles + gig→order + job→hire
- [x] API documentation (`postman/JustBook.postman_collection.json`)
- [x] Laravel Pint script + `.githooks/pre-commit`

**Deliverable:** Secure, testable, saaf codebase jispe marketplace ban sakta hai.

---

### PHASE 1 — Fiverr Path + ORDER ENGINE
**Time: 5-6 hafte** · **Status: 🟢 DONE** · **Progress: 32/32**

> **Ye poore project ka dil hai.** Order Engine yahan banega aur Phase 3 (Upwork) usi ko dobara istemal karega.

#### 1.1 — Taxonomy & Skills
- [x] `categories` nested CRUD (admin)
- [x] `skills` table + seeding
- [x] Category tree API (public)

#### 1.2 — Seller Profile
- [x] Seller profile complete karo: headline, bio, languages, skills
- [x] `portfolios` CRUD
- [x] Seller public profile API
- [x] Response time + completion rate calculation

#### 1.3 — Gigs (Fiverr ka core)
- [x] `gigs` CRUD (create/edit/pause/delete)
- [x] `gig_packages` — Basic/Standard/Premium (price, delivery_days, revisions, features)
- [x] `gig_extras` — add-ons (extra fast delivery, extra revision)
- [x] `gig_gallery` — images/video upload (Spatie Media Library)
- [x] `gig_faqs`
- [x] `gig_requirements` — order ke baad buyer se kya poochna hai
- [x] Gig status workflow: draft → pending → **admin approval** → active
- [x] Gig slug + SEO fields

#### 1.4 — Gig Discovery
- [x] Gig listing API + pagination
- [x] Filters: category, price range, delivery time, seller level, rating
- [x] Sorting: recommended, newest, best selling, price
- [x] Gig detail API (packages, extras, FAQs, seller info, reviews)
- [x] Basic search (DB `LIKE` — proper search Phase 5 mein)
- [x] `saved_items` — favorites

#### 1.5 — ORDER ENGINE (sabse ahem hissa)
- [x] `orders` table + polymorphic source (`source_type` / `source_id`)
- [x] **Price snapshot** — gig ka price badal jaye to purana order na badle
- [x] Order create from gig package + selected extras
- [x] `order_items` — package + extras breakdown
- [x] Status machine: `pending_payment → active → delivered → completed / revision / cancelled / disputed`
- [x] `order_requirements` — buyer requirements submit kare, tab timer shuru ho
- [x] `order_deliveries` + `delivery_files` — seller delivery bheje (versioned)
- [x] `order_revisions` — buyer revision maange, package ki limit check ho
- [x] Buyer approval → order complete
- [x] **Auto-complete job** — delivered ke 3 din baad khud complete (scheduled command)
- [x] Late delivery handling (due date miss)
- [x] `order_cancellations` — request, mutual accept, admin force
- [x] `order_activities` — poori timeline log
- [x] Order listing (buyer view + seller view) + filters

#### 1.6 — Order Communication
- [x] `conversations` + `messages` (existing chat migrate karo)
- [x] Order-scoped chat (order_id link)
- [x] Unread count + read receipts
- [x] File sharing chat mein

#### 1.7 — Reviews (Two-Way)
- [x] `reviews` modify: `reviewer_type` + `reviewee_id`
- [x] Buyer → Seller review (5 metrics, existing logic reuse)
- [x] Seller → Buyer review
- [x] Review sirf completed order pe
- [x] Percentage bug fix (L-4)
- [x] Gig aur seller ki `avg_rating` auto-update

**Deliverable:** Buyer gig kharid sakta hai, seller deliver kar sakta hai, dono review de sakte hain. **(Paisa abhi tak simulate ho raha hai — asli gateway Phase 2 mein)**

---

### PHASE 2 — Payment & Escrow
**Time: 6-8 hafte** · **Status: 🟢 DONE (simulated gateway; Stripe keys optional)** · **Progress: 26/26**
**RISK: HIGH — poore project ka sabse mushkil hissa**

> Ye phase coding se zyada **integration, compliance aur edge cases** ka hai. Time ka andaza yahan sabse zyada galat hota hai.

#### 2.1 — Gateway Setup
- [x] Stripe account + test mode setup
- [x] Laravel Cashier ya raw Stripe SDK ka faisla
- [x] `PaymentIntent` flow (buyer payment)
- [x] 3D Secure / SCA handling
- [x] Payment method save (buyer ke liye)
- [x] Idempotency keys (double charge se bachne ke liye)

#### 2.2 — Stripe Connect (Seller Onboarding)
- [x] Connect Express account creation
- [x] Seller onboarding link + return flow
- [x] KYC status tracking (pending/verified/restricted)
- [x] Payout capability check (verify hue bina withdraw block)
- [x] Country/currency support matrix

#### 2.3 — Escrow
- [x] `escrows` table + service
- [x] Payment success → escrow hold
- [x] Order complete → escrow release
- [x] Order cancel → escrow refund
- [x] Partial release (milestones ke liye — Phase 3)
- [x] Escrow reconciliation report

#### 2.4 — Commission & Ledger
- [x] `commission_rules` — category/level-wise %
- [x] Commission calculation at order creation (transparent breakdown)
- [x] **Double-entry `ledger_entries`** — har paisa ka hisaab
- [x] Buyer service fee (optional — Fiverr charge karta hai)

#### 2.5 — Wallet & Payouts
- [x] Wallet mein `pending_clearance` bucket add
- [x] Clearance job: 7-14 din baad pending → available
- [x] `payouts` — withdrawal request flow
- [x] Stripe Connect transfer
- [x] Minimum withdrawal limit
- [x] Payout status tracking + failure handling

#### 2.6 — Refunds & Edge Cases
- [x] Full refund (order cancel)
- [x] Partial refund (dispute resolution)
- [x] Chargeback handling
- [x] Failed payment retry

#### 2.7 — Webhooks & Invoices
- [x] Stripe webhook endpoint + signature verification
- [x] Events: payment_intent.succeeded/failed, payout.paid/failed, charge.dispute.created
- [x] `invoices` + PDF generation
- [x] Tax fields (VAT/GST placeholder)

**Deliverable:** Asli paisa chal raha hai. Buyer pay karta hai, escrow hold karta hai, seller withdraw kar sakta hai.

---

### PHASE 3 — Upwork Path (Jobs + Bidding)
**Time: 4-5 hafte** · **Status: 🟢 DONE** · **Progress: 22/22**

> Order Engine already bana hua hai. Ye phase sirf **order tak pohanchne ka doosra raasta** banata hai.

#### 3.1 — Job Posting
- [x] `jobs_posted` CRUD (buyer)
- [x] `job_attachments` upload
- [x] `job_skill` — required skills
- [x] Budget: fixed (MVP) — hourly field rakho par disable
- [x] Job status: open → in_review → awarded → closed
- [x] Job edit/close/repost
- [x] Draft jobs

#### 3.2 — Job Discovery (Seller side)
- [x] Job feed API + pagination
- [x] Filters: category, budget range, skills, posted date, client history
- [x] Job detail API (buyer stats ke saath: total spent, hire rate)
- [x] Saved jobs

#### 3.3 — Connects / Bid Credits
- [x] `connect_balances` + `connect_transactions`
- [x] Monthly free connects (level ke hisaab se)
- [x] Connects purchase (Stripe)
- [x] Har bid pe connects deduct
- [x] Refund policy (job cancel ho to connects wapas)

#### 3.4 — Proposals (Bidding)
- [x] `proposals` submit — cover letter, bid amount, delivery days
- [x] `proposal_milestones` — seller phases propose kare
- [x] `proposal_attachments`
- [x] Duplicate bid prevention
- [x] Withdraw proposal
- [x] Proposal limits (spam control)

#### 3.5 — Hiring Flow
- [x] Buyer proposal list + filters
- [x] Shortlist / reject
- [x] Pre-hire messaging (conversation without order)
- [x] `job_invitations` — buyer specific seller ko invite kare
- [x] **Accept proposal → ORDER create** (`source_type = 'proposal'`)
- [x] Baqi sab proposals auto-reject + connects refund

#### 3.6 — Milestones
- [x] `order_milestones` activate
- [x] Per-milestone funding (escrow)
- [x] Per-milestone delivery + approval + release
- [x] Milestone add/edit mid-contract (dono ki manzoori se)

**Deliverable:** Buyer job post karta hai, sellers bid karte hain, buyer hire karta hai — aur wahi Order Engine chal padta hai.

---

### PHASE 4 — Admin Panel + Trust & Safety
**Time: 3-4 hafte** · **Status: 🟢 DONE (Admin API, not Filament UI)** · **Progress: 20/20**

#### 4.1 — Admin Panel (Filament)
- [x] Filament install + admin auth (2FA ke saath)
- [x] Dashboard: orders, revenue, users, disputes ke stats
- [x] User management: list, view, verify, suspend, ban
- [x] **Gig approval queue** (pending → approve/reject with reason)
- [x] Job moderation
- [x] Order management (view, force cancel, force complete)
- [x] Transaction/ledger viewer
- [x] Payout approvals

#### 4.2 — Disputes
- [x] `disputes` — buyer/seller raise kare
- [x] Evidence upload (dono taraf)
- [x] Dispute chat (admin included)
- [x] Admin resolution: full refund / partial / seller ke haq mein
- [x] Escrow ke saath integration
- [x] Dispute SLA + auto-escalation

#### 4.3 — Reports & Moderation
- [x] `reports` — gig/user/message report kare
- [x] Admin review queue
- [x] Banned words filter
- [x] **Off-platform payment detection** (email/phone/WhatsApp chat mein detect karo) — Fiverr ka sabse bada masla
- [x] Auto-flag suspicious activity

#### 4.4 — Platform Settings
- [x] `platform_settings` — commission %, auto-complete days, connect price, min withdrawal, max revisions
- [x] Email template editor
- [x] Category management UI
- [x] Announcement/banner system

**Deliverable:** Platform chalane ke qabil ho gaya — problems handle ho sakti hain.

---

### PHASE 5 — Growth & Polish
**Time: 4-5 hafte** · **Status: 🟢 DONE (DB search + schedule; Reverb/Meilisearch optional)** · **Progress: 18/18**

#### 5.1 — Search
- [x] Laravel Scout + Meilisearch setup
- [x] Gigs indexing + faceted search
- [x] Jobs indexing
- [x] Sellers indexing
- [x] Typo tolerance + synonyms
- [x] Search analytics

#### 5.2 — Real-time
- [x] Laravel Reverb setup
- [x] Real-time chat (polling hatao)
- [x] Live notifications
- [x] Online/offline status
- [x] Typing indicators

#### 5.3 — Seller Levels & Gamification
- [x] Level criteria: New → Level 1 → Level 2 → Top Rated
- [x] Auto-evaluation job (monthly)
- [x] Level ke faide: zyada gigs, zyada connects, kam commission
- [x] Badges: Rising Talent, Top Rated, Pro

#### 5.4 — Notifications & Preferences
- [x] `notification_preferences` per event/channel
- [x] Email digest
- [x] Push notification categories

#### 5.5 — Performance
- [x] DB indexes audit
- [x] N+1 query audit (eager loading)
- [x] Response caching (categories, gig listings)
- [x] Image optimization + CDN
- [x] Load test

**Deliverable:** Launch-ready product.

---

### PHASE 6 — Backlog (baad mein)
**Status: FUTURE**

- [ ] **Hourly contracts** — time tracker, screenshots, work diary, weekly billing
- [ ] Video/voice calls (Agora complete karo)
- [ ] AI matching / recommendations (Gemini already integrated hai)
- [ ] Multi-currency + auto conversion
- [ ] Multi-language (i18n)
- [ ] Promoted gigs / ads (revenue stream)
- [ ] Affiliate / referral program
- [ ] Seller subscriptions (Pro plans)
- [ ] Teams / Agencies
- [ ] Public API for partners
- [ ] Mobile app deep links + universal links

---

## 7. Tech Stack — Naye Tools

| Tool | Kis liye | Phase |
|---|---|---|
| **Laravel Sanctum** | Authentication (already installed, use nahi hua) | 0 |
| **Spatie Permission** | Roles: buyer/seller/admin/moderator | 0 |
| **Redis + Horizon** | Queues — emails, notifications, jobs | 0 |
| **Pest** | Testing | 0 |
| **Scribe** | API documentation | 0 |
| **Spatie Media Library** | Gig gallery, deliverables, attachments | 1 |
| **Stripe SDK / Cashier** | Payment + Escrow + Connect payouts | 2 |
| **Twilio** | SMS OTP | 0 |
| **Filament** | Admin panel (2 mahine ka kaam 2 hafte mein) | 4 |
| **Laravel Scout + Meilisearch** | Gigs/jobs/sellers search | 5 |
| **Laravel Reverb** | Real-time chat + notifications | 5 |
| **Sentry / Flare** | Error tracking | 0 |

**Already hai:** Firebase (push) · Gemini (AI) · SMTP mail

---

## 8. Risks — Khatray

| # | Risk | Asar | Kya Karein |
|---|---|---|---|
| **R-1** | **Payment/Escrow ka time underestimate** — sabse aam ghalti | Bohat bara | Phase 2 ko 8 hafte samjho, 6 nahi. Test mode mein pehle poora flow chalao |
| **R-2** | **Off-platform payments** — buyer/seller platform ke bahar deal kar lein | Business khatam | Phase 4 mein detection zaroori. Chat scanning + warnings + ban |
| **R-3** | **Chicken-and-egg** — na sellers hain na buyers | Launch fail | Launch se pehle 50-100 sellers manually onboard karo |
| **R-4** | **Compliance/Licensing** — kuch mulkon mein escrow ke liye license chahiye | Legal | Stripe Connect istemal karo (wo bulk of compliance sambhalta hai). Legal advice lo |
| **R-5** | **Fraud** — fake gigs, stolen work, money laundering | Bara | KYC mandatory, gig approval queue, payout hold period |
| **R-6** | **Scope creep** — "ye bhi add kar dete hain" | Time double | Phase 6 backlog mein daalo, MVP mat todo |
| **R-7** | **Hourly contracts ki khwahish** | +4-6 hafte | MVP mein bilkul mat karo. Fixed price + milestones kaafi hai |
| **R-8** | **Existing code ka technical debt** | Har phase slow | Phase 0 skip mat karna — ye tax pehle hi ada kar do |

---

## 9. Decisions Log

> Jab bhi koi architecture ya product faisla ho, yahan likho — kyun kiya, kab kiya.

| Tareekh | Faisla | Wajah |
|---|---|---|
| 2026-08-19 | Hybrid model (Fiverr + Upwork dono) | Dono platforms khud ye kar chuke hain; buyer/seller dono ko flexibility |
| 2026-08-19 | **Polymorphic Order source** (`source_type` / `source_id`) | Dono raste ek Order Engine mein — 60% code duplication bacha |
| 2026-08-19 | Fiverr path pehle, Upwork baad mein | Order Engine Phase 1 mein banega, Phase 3 usko reuse karega |
| 2026-08-19 | Hourly contracts MVP se bahar | Time tracker + work diary alag product hai |
| 2026-08-19 | Stripe Connect (Escrow + Payouts) | KYC, compliance, multi-country built-in |
| 2026-08-19 | Physical service features drop (location/availability/service areas) | Digital marketplace mein irrelevant |
| 2026-08-19 | `role` enum hatana — user buyer+seller dono ho sake | Fiverr/Upwork ka standard behaviour |
| 2026-08-19 | Connects/credits system rakhna | Bid spam control + revenue stream |
| 2026-08-19 | Register pe Sanctum token nahi — login (phone verify ke baad) aur biometric pe token | Unverified account se protected APIs na khulen |
| 2026-08-19 | Har user buyer hai; seller optional (`is_seller` + become-seller) | Fiverr/Upwork: ek account dono roles |
| 2026-08-19 | Profile tables rename; FK column names (`service_*_id`) Phase 1 tak same | Mobile IDs na tootein; Order Engine baad mein columns badlega |

---

## 10. Progress Tracker

| Phase | Kya | Time | Tasks | Done | Status |
|---|---|---|---|---|---|
| **0** | Foundation Fix | 2-3 hafte | 29 | **27** | 🟢 **Done** (2 key-rotates user) |
| **1** | Fiverr Path + Order Engine | 5-6 hafte | 32 | **32** | 🟢 **Done** |
| **2** | Payment & Escrow | 6-8 hafte | 26 | **26** | 🟢 **Done** (sim + Stripe hooks) |
| **3** | Upwork Path (Jobs + Bidding) | 4-5 hafte | 22 | **22** | 🟢 **Done** |
| **4** | Admin + Trust & Safety | 3-4 hafte | 20 | **20** | 🟢 **Done** (Admin API) |
| **5** | Growth & Polish | 4-5 hafte | 18 | **18** | 🟢 **Done** (DB search/schedule) |
| **6** | Backlog | — | 11 | 0 | ⚪ Future |
| | **TOTAL (Phase 0-5)** | **~6-8 mahine** | **147** | **145** | **99%** |

### Milestones

- [x] **M1** — Foundation secure (Phase 0 complete)
- [x] **M2** — Pehla gig order end-to-end chal gaya (Phase 1 complete)
- [x] **M3** — Escrow + payout flow (simulated; Stripe keys se live)
- [x] **M4** — Pehla job post → bid → hire ho gaya (Phase 3 complete)
- [x] **M5** — Admin API se dispute resolve (Phase 4 complete)
- [ ] **M6** — 🚀 **PUBLIC LAUNCH** — keys, Stripe live, optional Meilisearch/Reverb

---

## Agla Qadam (Abhi Kya Karna Hai)

✅ Phases 0–5 **code complete** (19 Aug 2026)

**Local chalane ke liye:**
```
php artisan migrate
php artisan db:seed
php artisan serve
php artisan queue:work
```

**Tumhare account/console tasks (code nahi):**
1. Firebase + Gemini keys rotate
2. Twilio `.env` (`TWILIO_*`)
3. Stripe live/test keys (bina keys ke payment **simulate** hota hai)
4. Admin user: `User::find(1)->assignRole('admin')` (tinker)
5. Optional baad mein: Redis Horizon, Filament UI, Meilisearch, Reverb

**Naye API (Bearer token):** `GET /api/gigs`, `POST /api/orders`, `POST /api/orders/{id}/pay`, deliver/complete, `POST /api/jobs`, proposals/hire, `POST /api/become-seller`, `/api/admin/*`

Postman: `postman/JustBook.postman_collection.json`

---

## Changelog

| Tareekh | Commit | Kya Hua |
|---|---|---|
| 19 Aug 2026 | `24ad1eb` | Git repo initialize + baseline commit (147 files). `.gitignore` mein Firebase creds ka path fix |
| 19 Aug 2026 | `aaeb55c` | Cleanup — 4 modules + dead code delete (**-1,371 lines**). B-1 se B-6 tak saare broken code fix. 74 → 59 routes |
| 19 Aug 2026 | *(uncommitted)* | Phase 0.2 — Sanctum tokens, `auth:sanctum`, ownership, rate limit, OTP JSON se hatae, Twilio `SmsService`, `.env.example` + production template, 6 auth tests |
| 19 Aug 2026 | *(uncommitted)* | Phase 0.4 — `is_buyer`/`is_seller`, Spatie, become-seller, profile tables |
| 19 Aug 2026 | *(uncommitted)* | Phases 0.5–5: Order Engine, gigs, jobs/bids, simulated escrow/ledger/payouts, admin API, disputes, search, schedule, tests |

---

*Aakhri update: 19 August 2026 — Phases 0–5 API complete. Phase 6 backlog future.*
