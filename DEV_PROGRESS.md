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

### Branch: feature/phase3-fees

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
- Migration: add_fee_category_to_fee_structures_table
- FeeStructure model, Interface, Repository, ServiceProvider
- FeePayment model, Interface, Repository, ServiceProvider
- 18 routes (fee structures, payments, reports)
- FeeStructureController (CRUD)
- FeePaymentController (ledger, create, store, challan, challanPdf)
- FeeReportController (daily, dateRange, defaulters, categorySummary, admissions, classStrength, rte)
- All views: fee-structures, fees, reports (screen + PDF)
- Left menu: Fees + Reports sections
- Admission confirm flow: optional first payment + challan redirect
- Confirm modal: static (no accidental dismiss), payment fields
- Already-confirmed guard in AdmissionRepository
- generateStudentEmail: handles duplicate emails
- Challan PDF: 3 copies each on separate page
- FeeTest.php: 27/27 passing ✅
- Run: docker exec app php artisan test tests/Feature/FeeTest.php --testdox

### Pending Angela review
- Challan format/layout — may need tweaks
- RTE confirm flow — no challan, record doc number only (to confirm)
- COC confirm flow — Internal Transfer challan (to confirm)

## Phase 4 — Leaving Certificate — COMPLETE ✅

### Branch: feature/phase4-lc

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
- Left menu: Leaving Certificates link
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

## Branch Strategy (updated)
- main → stable only (never commit directly)
- develop → integration branch
- feature/manual-testing-fixes → MERGED to develop ✅
- feature/manual-testing-tc15 → current branch (TC-15 reports in progress)
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

## Manual Testing Session — In Progress (feature/manual-testing-tc15)

### Completed
- TC-01 Admin Login ✅
- TC-02 Teacher Login ✅ (fixed: LC restricted to admin only, menu guard added)
- TC-03 New Admission Inquiry ✅
- TC-04 Full Admission Flow ✅
- TC-05 Cancel Admission ✅
- TC-06 Edit Admission ✅ (fixed: Edit Details on admission show page → admissions.edit)

### Saru Testing (develop branch)
- TC-07 to TC-14 fees testing in progress

### TC-15 Reports — In Progress (feature/manual-testing-tc15)
- Collection Report (date range) ✅ fixed:
  - Left menu + breadcrumb layout wrapper added
  - Class/Div fixed in view and PDF (admissions table, not promotions)
  - Renamed from Date Range to Collection Report in menu
  - Daily Collection hidden from menu (pending Angela confirmation)
- Defaulters report — BROKEN: Undefined property stdClass::$student_id
  - Need to check getDefaulters() in FeePaymentRepository.php
  - View calls route('fees.ledger', $d->student_id) but query doesn't return student_id
- Category Summary, Admissions, Class Strength, RTE — need same layout fix as Collection Report

### UI Fixes Done (merged to develop ✅)
- @stack('scripts') missing from layouts/app.blade.php — fixed
- LC create page preview panel, fee warning, auto-populate — fixed
- Migration: drop class_id FK before modifying unique index — fixed
- Students menu hidden for admin, shown for teacher only
- Edit removed from student list
- Section dropdown persists on student list filter
- Edit Details on confirmed admission → admissions.edit (not students/edit)
- Student exit flow — deferred to Phase 5 (pending Angela's exit form)

### Angela Clarification Items
1. Misc/ad-hoc payments — how to handle (books, uniform etc.)
2. Register No. on LC — general_id ?? dga_admission_no
3. Student IDs — when does pre-primary get general_id
4. Student photos — needed? mandatory? when uploaded?
5. Student exit flow — mark as exited after LC? exit form needed
6. Challan copies — 3 copies for every transaction or only first payment?
7. RTE confirm flow — no challan, just record doc number?
8. COC confirm flow — Internal Transfer label correct?
9. Reports — is Daily Collection needed or redundant with Collection Report?
10. Reports — are all 7 reports useful? any missing?
11. Defaulters — how to define a defaulter (any balance due, or past due date)?

### Next Steps
- Fix Defaulters report (student_id undefined on stdClass)
- Fix layout on remaining reports (Category Summary, Admissions, Class Strength, RTE)
- TC-15 complete once all reports load + PDFs download correctly
- TC-16 to TC-22 LC tests
- TC-23 Left Menu Navigation
- TC-24 Session and Logout
- TC-25 Angela Challan Review

## Phase 5 — Pending (post-testing)

### Student Exit Flow
- When a student leaves mid-session (LC issued), admission stays as 'confirmed' — not handled yet
- Need new status 'exited' on admissions table
- Exit date + reason to be captured (Angela's exit form pending)
- Exited students to be removed from active student list
- New archived/exited view needed
- Waiting for Angela's exit form before implementation

### Student Photos
- Current admission form has no photo upload
- Old student profile page has placeholder only
- Decision pending Angela confirmation:
  - Is it needed?
  - Mandatory or optional?
  - At inquiry or after confirmation?
- Storage plan: Cloudinary free tier (25GB free, survives redeploys, suitable for NGO scale)

### Miscellaneous / Ad-hoc Payments
- Books, uniform, excursion etc. not currently handled
- Pending Angela clarification on flow and challan format

