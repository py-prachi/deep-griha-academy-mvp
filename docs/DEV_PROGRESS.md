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

### Manual test checklist for Saru (resume tomorrow)
- Flow 2: Confirm without payment → admission show page ✅ success message
- Flow 3: Cheque payment → challan with cheque details
- Flow 4: QR/UPI payment → challan with transaction ref
- Flow 5: RTE confirm → payment section hidden, no challan
- Flow 6: Discount confirm → discounted amount + challan
- Fee Structures: create/edit/delete
- Ledger: correct due/paid/balance shown
- Back to Profile link on ledger works
- All 7 report pages load
- All 7 report PDF downloads work
- Nav menu: Fees + Reports links all work

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
- feature/phase3-fees → MERGED to develop ✅
- feature/phase4-lc → current branch (Phase 4 complete, 25/25 tests passing, pending Saru manual testing)
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

## Manual Testing Session — In Progress (feature/manual-testing-fixes)

### Completed
- TC-01 Admin Login ✅
- TC-02 Teacher Login ✅ (fixed: LC restricted to admin only, menu guard added)
- TC-03 New Admission Inquiry ✅
- TC-04 Full Admission Flow — steps 1-4 ✅, step 5 pass with note
  - Added Fee Ledger + Issue LC buttons to admission show page ✅

### Current Bug Being Fixed
- LC create page preview panel shows — for Student/Admission No/Class
- Fee warning not appearing after student selected from dropdown
- AJAX returns correct data (verified via tinker)
- JS auto-trigger added for pre-selected admission_id
- general_id fix applied to preview and PDF register no field
- Root cause not yet found — JS may not be firing on select change

### Angela Clarification Items (separate doc being maintained)
1. Misc/ad-hoc payments (books, uniform) — how to handle
2. Register No. of Pupil on LC — use general_id ?? dga_admission_no
3. Student IDs — when does pre-primary student get general_id

### Next Steps
- Fix LC preview panel and fee warning
- Resume TC-04 step 5 onwards
- TC-05 Cancel admission
- Then Phase 3 fee flows (TC-07 onwards)
EOF
git add DEV_PROGRESS.md
git commit -m "docs: update testing progress for handoff to new chat"
git push origin feature/manual-testing-fixes
