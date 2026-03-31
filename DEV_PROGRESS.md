# DGA Development Progress

## Phase 1 — COMPLETE ✅
- Database migrations (6 files)
- Seeders (roles, permissions, users, school structure)
- Database renamed to dga_school

## Phase 2 — COMPLETE ✅
- Eloquent Models (7 files)
- AdmissionInterface, AdmissionRepository, AdmissionServiceProvider
- AdmissionController (11 routes)
- Blade Views (index, create, show, edit, cancelled)
- Left menu updated with Admissions section
- Full admission flow working:
  - New Inquiry → Pending → Confirmed → Cancelled
  - Document checklist
  - On confirm: student user created + promotion record created
  - Student appears in existing student list after confirmation

## Phase 2 — Post-Implementation Fixes — COMPLETE ✅
- Attendance view null-safety fixes
- Student list loading fix (no filter case)
- Student profile null-safety + admission parent data fallback
- Student edit page fixes (birthday, blood type, mother phone)
- Admission form improvements (religion dropdown, father/mother phone split, city/zip, blood type)
- Cancelled admissions view enhanced with phone column
- Migration: add_blood_type_to_admissions_table
- Migration: add_contact_fields_to_admissions_table

## Phase 2.5 — Feature Tests — COMPLETE ✅
- 31 automated feature tests in tests/Feature/AdmissionTest.php
- All 31 passing ✅
- Run: docker exec app php artisan test tests/Feature/AdmissionTest.php --testdox

## Phase 3 — Fees — COMPLETE ✅

### Key Decisions
- Fee collection integrated into admission confirm flow (first payment at confirm)
- Same challan for all fee types (academic + misc)
- Partial payments allowed anytime
- Payment modes: cash, QR/UPI, cheque
- RTE → first payment section hidden at confirm
- COC → challan marked as Internal Transfer
- General ID: optional at confirm, manually entered (SARAL/ZP portal ID)
- Nursery/LKG/UKG: auto DGA admission no, no general ID
- Class 1+: general ID field shown, optional for now
- Reports: PDF downloadable, date range filters

### Done ✅
- DomPDF installed (v2.2.0 for PHP 7.x)
- FeeStructure model, Interface, Repository, ServiceProvider
- FeePayment model, Interface, Repository, ServiceProvider
- 18 routes (fee structures, payments, reports)
- FeeStructureController (CRUD)
- FeePaymentController (ledger, create, store, challan, challanPdf)
- FeeReportController (daily, dateRange, defaulters, categorySummary, admissions, classStrength, rte)
- All views: fee-structures, fees, reports (screen + PDF)
- Left menu: Fees + Reports sections
- Admission confirm flow: optional first payment + challan redirect
- FeeTest.php: 27/27 passing ✅
- Run: docker exec app php artisan test tests/Feature/FeeTest.php --testdox

### Pending Angela review
- Challan format/layout — may need tweaks
- RTE confirm flow — no challan, record doc number only (to confirm)
- COC confirm flow — Internal Transfer challan (to confirm)

## Phase 3.5 — Misc Payments — COMPLETE ✅

### Key Decisions
- Same fee_payments + fee_line_items tables, split by payment_category (fee / misc)
- Fee payments → reduce student fee balance
- Misc payments → challan issued, fee balance untouched
- Mixing fee + misc in one transaction blocked (validation error)
- Amount Paid auto-calculated from line items (no manual entry error)
- Two separate "Other" options: Other (Fee) and Other (Misc) for future flexibility

### Fee Line Items
- admission_fee, tuition_fee, transport_charges, transfer_certificate, bonafide_certificate, other_fee

### Misc Line Items
- uniform, notebooks, stationery, sports, other_misc

### Done ✅
- Migration: 2026_03_25_000001_add_payment_category_to_fee_payments
  - Added payment_category enum (fee/misc) to fee_payments table
  - Updated fee_line_items description enum with new values
- FeeLineItem model: feeLabels(), miscLabels(), descriptionLabels(), isMiscDescription()
- FeePayment model: CATEGORY_FEE/CATEGORY_MISC constants, totalFeesPaidByStudent(), totalMiscPaidByStudent(), totalPaidByStudent() now fee-only
- FeePaymentRepository: getFeePaymentsByStudent(), getMiscByDateRange(), store() auto-sets category, all balance queries filter payment_category = fee, getDefaulters() and getCategoryWiseSummary() fixed
- FeePaymentController: calculateBalance() helper (fee-only), mixed payment validation
- FeePaymentInterface: getFeePaymentsByStudent(), getMiscByDateRange() added
- fees/create.blade.php: Fee/Misc toggle, two separate line item sections, auto-sum into Amount Paid
- FeeReportController: miscSales() method added
- routes/web.php: /reports/misc-sales route added
- reports/misc-sales.blade.php: screen view with summary + detail table, date range filter, PDF button
- reports/misc-sales-pdf.blade.php: PDF view
- Left menu: Misc Sales added under Reports

## Phase 4 — Leaving Certificate — COMPLETE ✅

### Key Decisions
- LC numbers: global running sequence (LC001, LC002...) — never reset
- Linked to admissions table (no separate students table in this project)
- Only confirmed admissions can get an LC
- Fees outstanding = WARNING only, never blocks issuance
- fees_due snapshotted at time of issue
- One LC per admission — duplicate issuance blocked
- PDF matches Angela's real LC format exactly

### Done ✅
- Migration: create_leaving_certificates_table (linked to admissions)
- LeavingCertificate model + generateLcNumber()
- LeavingCertificateInterface + Repository + ServiceProvider
- LeavingCertificateController (index, create, store, show, pdf, studentInfo AJAX)
- Views: index, create, show, pdf (PHP 7 compatible — no ?-> operators)
- Left menu: Leaving Certificates under Exit Formalities group
- PDF: matches real DGA LC format (Society header, all fields, NB text, signatures)
- LeavingCertificateTest.php: 25/25 passing ✅
- Run: docker exec app php artisan test tests/Feature/LeavingCertificateTest.php --testdox

## Key Decisions
- Challan numbers: global running sequence (0001, 0002...)
- DGA admission no: DGA/26-27/001 (resets per year, pre-primary only)
- General ID: manually entered, optional for now
- Fee categories: general, rte, coc, discount
- COC: full fee shown as DGA Internal Transfer, parent balance = 0
- LC issuance: warning only if fees due, never blocked
- Soft delete for cancelled admissions

## Branch Strategy
- main → stable only (never commit directly)
- develop → integration branch
- Workflow: feature/phaseN → develop → main

## Tech Stack
- Laravel 8.x + Blade + Bootstrap 5
- MySQL (database: dga_school)
- Docker (containers: app, db, nginx)
- Spatie Permissions for roles
- Repository pattern
- PHPUnit 9.5 for feature tests
- DomPDF v2.2.0

## Tech Stack — Important PHP 7 Constraints
- PHP 7.x inside Docker — NO nullsafe operator (?->) anywhere in controllers or views
- Use ternary instead: `$x ? $x->method() : '—'`  not  `$x?->method()`
- No named arguments, no match expressions, no union types
- Always check PHP version before writing new code: `docker exec app php -v`
- Laravel 8.x + Blade + Bootstrap 5, MySQL (dga_school), Docker (app/db/nginx)
- PHP 7.x — NO ?->, no named args, no match expressions
- Repository pattern, Spatie permissions, DomPDF v2.2.0, PHPUnit 9.5
- No Student model — students ARE confirmed admissions
- No AdmissionFactory — tests use Admission::create() directly

## Project Structure — Critical Facts
- NO Student model / students table — students ARE admissions
- Admission model = student record (status: confirmed = active student)
- Only confirmed admissions appear in student lists, LC, fee ledger etc.
- No AdmissionFactory exists — tests use Admission::create() + DB::table() directly
- Test pattern: copy AdmissionTest.php setUp() pattern exactly for all new feature tests
- Views live at: resources/views/ on Mac, /var/www/resources/views/ in container
- App code lives at: app/ on Mac, /var/www/app/ in container (NOT /var/www/html/)
- Tests live at: tests/ on Mac, /var/www/tests/ in container

## Docker Workflow
- After editing any file locally: docker cp <local_path> app:<container_path>
- After editing views: also run docker exec app php artisan view:clear
- Files are NOT auto-synced — every change needs explicit docker cp
- To copy a view: docker cp resources/views/lc/foo.blade.php app:/var/www/resources/views/lc/foo.blade.php
- PHP patches in container: use docker exec app php -r (NOT python3 — not installed, NOT sed — quote escaping issues)

## Running Tests
- docker exec app php artisan test tests/Feature/AdmissionTest.php --testdox   # 31 tests
- docker exec app php artisan test tests/Feature/FeeTest.php --testdox          # 27 tests
- docker exec app php artisan test tests/Feature/LeavingCertificateTest.php --testdox  # 25 tests

## Login Credentials
- Admin: admin@deepgriha.com / dga@admin2026
- Teacher: anita.sharma@deepgriha.com / dga@teacher2026
- Student: test.student.b.2@deepgriha.com / dga@student2026

## Repo
https://github.com/py-prachi/deep-griha-academy-mvp

## Manual Testing — Status

### Completed ✅
- TC-01 Admin Login ✅
- TC-02 Teacher Login ✅ (fixed: LC restricted to admin only, menu guard added)
- TC-03 New Admission Inquiry ✅
- TC-04 Full Admission Flow ✅
- TC-05 Cancel Admission ✅
- TC-06 Edit Admission ✅
- TC-15 Reports ✅ (all 6 reports load + PDFs work)

### Saru Testing (develop branch)
- TC-07 to TC-14 fees testing — ✅

### Next
- TC-16 to TC-22 LC tests — ✅
- TC-23 Left Menu Navigation — ✅
- TC-24 Session and Logout — ✅
- TC-25 Angela Challan Review — Needs few confirmations

## UI / Layout Fixes Done (all merged to develop ✅)
- @stack('scripts') missing from layouts/app.blade.php — fixed
- LC create page preview panel, fee warning, auto-populate — fixed
- Students menu hidden for admin, shown for teacher only
- Edit removed from student list
- Section dropdown persists on student list filter
- Edit Details on confirmed admission → admissions.edit
- Left menu: reordered for logical admin flow
- Left menu: Exit Formalities collapsible group (LC inside, Phase 5 exit form to be added)
- Left menu: Fees icon bi-cash-stack, removed redundant Collect Fee link
- fee-structures index/create/edit: left menu + breadcrumb wrapper added
- fees/create (Record Payment): left menu wrapper added
- All report views: left menu + breadcrumb wrapper added (matching Collection Report layout)
- Ledger: smart back button (reads referer — defaulters / profile / fallback)
- Defaulters: getDefaulters() query fixed (student_id alias, dga_admission_no, general_id added)
- Class Strength: GROUP BY sc.id added (only_full_group_by fix)
- RTE report: View button → admissions.show (was student.profile.show)

## Angela Clarification Items
1. Misc/ad-hoc payments — DONE ✅ (separate payment_category, misc sales report)
2. Register No. on LC — CONFIRMED: general_id for class 1+, dga_admission_no for pre-primary — DONE ✅
3. Student IDs — CONFIRMED: general_id from class 1 onwards
4. Student photos — Deferred, storage decision pending (Cloudinary free tier plan ready)
5. Student exit flow — DONE ✅ (Phase 5 complete)
6. Challan copies — CONFIRMED: 3 copies on every transaction
7. RTE confirm flow — not confirmed yet
8. COC confirm flow — not confirmed yet
9. Reports — Daily Collection hidden (redundant with Collection Report) ✅
10. Reports — screenshots/PDFs sent for review
11. Defaulters — not confirmed yet (currently: balance > 0)
12. RTE doc number — CONFIRMED: same as general_id — PENDING implementation

## Phase 5 — Student Exit Flow — COMPLETE ✅

### Done ✅
- Migration: add_exited_status_to_admissions_table (status enum + exit_date)
- Migration: create_student_exits_table (all exit form fields)
- StudentExit model, Interface, Repository, ServiceProvider
- StudentExitController (index, create, store, show, studentInfo AJAX)
- exits/ views: index, create (live name search), show (with LC prompt banner)
- Left menu: Exit Formalities group (LC + Exit Form)
- On exit: admission marked as exited, removed from active student list
- Exited students shown in exits/index

### LC Fixes Done ✅
- Issue LC button removed from admissions/show — LC only issued from exits/show
- LC create: pre-populates student details for exited admissions
- LC create: date_of_leaving pre-fills from admission exit_date
- LC PDF: Register No — general_id for class_id >= 4 (Class 1+), dga_admission_no for pre-primary ✅ (Angela confirmed #2)
- LC show: phone field added; back button routes to exit record when student is exited
- LC create: back arrow + breadcrumb route through exits/ when accessed from exit flow

### Other Fixes Done ✅
- admissions/show: Cancel Admission + Edit hidden for exited status
- admissions/create: guardian_address autocomplete disabled (was auto-filling on city entry)
- Fee ledger: fallback to admission class/fee_category when no promotion record exists
- exits/create: live text search replaces dropdown (filters as you type, works for 200+ students)
- exits/create student preview: mobile falls back to father_phone → contact_mobile → mother_phone

### RTE Doc Number Field — PENDING
- rte_doc_no = same as general_id (Angela confirmed #12)
- Needs: migration + field on admission form (shown only when fee_category = rte)
- RTE report currently shows '—' for all students

### Student Photos — DEFERRED
- No photo upload currently
- Decision pending Angela: mandatory? at inquiry or after confirmation?
- Storage plan: Cloudinary free tier

## Deployment Strategy

### Platform Decision
- Railway.app — selected for initial staging/review deployment
- Free tier sufficient for Angela/Rahul review phase
- Production: migrate to DigitalOcean $6/month droplet (stable, no sleep)
- NGO consideration: DigitalOcean gives $200 free credit to new accounts

### Architecture (Cloud)
- App container (Laravel + PHP 7.x + Nginx) → Railway service
- MySQL database → Railway managed MySQL plugin
- Storage → local for now (photos pending Angela decision)
- Domain → optional, cheap (~$10/year via Namecheap)

### Deployment Flow
- GitHub (develop branch) → Railway auto-deploy on push
- Migrations run on deploy
- Environment variables set in Railway dashboard (not in code)
- Public URL shared with Angela/Rahul for review: e.g. dga.railway.app

### Railway Deployment — Completed 16 Mar 2026
- Production
  URL: https://deep-griha-academy-mvp-production.up.railway.app
  Branch: main
  Database: Separate production MySQL on Railway
  Status: Live, seeded with initial data

- Staging
  URL: https://deep-griha-academy-mvp-staging.up.railway.app
  Branch: develop
  Database: Separate staging MySQL on Railway
  Status: Live, seeded with test data

### Workflow
- Feature branches → merge to develop → auto deploys to staging
- Staging approved → merge to main → auto deploys to production

### Known Railway Settings
- Networking port must be set to 8080 on app service
- APP_URL and ASSET_URL must be set to the https domain
- TrustProxies set to '*' for https asset loading behind Railway proxy
- Builder: Dockerfile (not Railpack)
- Dockerfile path: Dockerfile.railway
