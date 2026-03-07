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

## Phase 3 — Fees — IN PROGRESS 🚧

### Branch: feature/phase3-fees

### Decisions made
- Fee collection integrated into admission confirm flow (first payment at confirm)
- Same challan for all fee types (academic + misc)
- Partial payments allowed anytime
- Payment modes: cash, QR/UPI, cheque
- RTE → no challan, record document number only (Angela to confirm)
- COC → challan marked as Internal Transfer, ₹0 from parent (Angela to confirm)
- General ID: optional at confirm, manually entered (SARAL/ZP portal ID)
- Nursery/LKG/UKG: auto DGA admission no, no general ID
- Class 1+: general ID field shown, optional for now
- Reports: PDF downloadable, date range filters

### Done so far ✅
- DomPDF installed (v2.2.0 for PHP 7.x)
- Migration: add_fee_category_to_fee_structures_table (fee_category, session_id columns)
- FeeStructure model updated ($fillable, CATEGORIES, CATEGORY_LABELS constants)
- FeeStructureInterface created
- FeeStructureRepository created
- FeeStructureServiceProvider created and registered
- FeePaymentInterface created
- FeePaymentRepository created (with DB transaction for line items)
- FeePaymentServiceProvider created and registered
- Routes added (18 routes — fee structures, fee payments, reports)
- FeeStructureController created (index, create, store, edit, update, destroy)
- FeePaymentController created (ledger, create, store, challan, challanPdf)
- FeeReportController created (daily, dateRange, defaulters, categorySummary, admissions, classStrength, rte)
- Views created:
  - resources/views/fee-structures/index.blade.php
  - resources/views/fee-structures/create.blade.php
  - resources/views/fee-structures/edit.blade.php
  - resources/views/fees/ledger.blade.php
  - resources/views/fees/create.blade.php
  - resources/views/fees/challan.blade.php
  - resources/views/fees/challan-pdf.blade.php

### TODO — remaining Phase 3 tasks
1. Report views (screen + PDF variants):
   - resources/views/reports/fees/daily.blade.php + daily-pdf.blade.php
   - resources/views/reports/fees/date-range.blade.php + date-range-pdf.blade.php
   - resources/views/reports/fees/defaulters.blade.php + defaulters-pdf.blade.php
   - resources/views/reports/fees/category-summary.blade.php + category-summary-pdf.blade.php
   - resources/views/reports/admissions.blade.php + admissions-pdf.blade.php
   - resources/views/reports/class-strength.blade.php + class-strength-pdf.blade.php
   - resources/views/reports/rte.blade.php + rte-pdf.blade.php

2. Update admission confirm flow:
   - Add fee payment fields to admissions/show.blade.php confirm section
   - Update AdmissionController::confirm() to record first payment + generate challan
   - Update AdmissionRepository::confirm() if needed

3. Navigation menu:
   - Add Fees section to left menu
   - Add Reports section to left menu

4. Add fee permissions to RolesAndPermissionsSeeder

5. Phase 3 feature tests (FeeTest.php)

6. Manual test checklist for Saru

### Next immediate step when resuming
```bash
mkdir -p resources/views/reports/fees
```
Then build report views in this order:
daily → date-range → defaulters → category-summary → admissions → class-strength → rte
Then PDF variants.
Then admission confirm flow update.
Then nav menu.
Then tests.

## Known Issues (pre-existing — fix later)
- View exams does not show created exam after creation
- Exam dropdown not filtered by teacher's assigned classes

## Phase 4 — NOT STARTED
- LeavingCertificateController
- LC PDF generation
- LC numbers: global running sequence (LC001, LC002...)

## Key Decisions
- Challan numbers: global running sequence (0001, 0002...)
- DGA admission no: DGA/26-27/001 (resets per year, pre-primary only)
- General ID: manually entered, optional for now
- Fee categories: general, rte, coc, discount
- COC: full fee shown as DGA Internal Transfer, parent balance = 0
- LC issuance: warning only if fees due, never blocked
- Soft delete for cancelled admissions

## Branch Strategy
- main → stable only
- develop → integration branch
- feature/phase3-fees → current branch 🚧

## Tech Stack
- Laravel 8.x + Blade + Bootstrap 5
- MySQL (database: dga_school)
- Docker (containers: app, db, nginx)
- Spatie Permissions for roles
- Repository pattern
- PHPUnit 9.5 for feature tests
- DomPDF v2.2.0

## Login Credentials
- Admin: admin@deepgriha.com / dga@admin2026
- Teacher: anita.sharma@deepgriha.com / dga@teacher2026
- Student: test.student.b.2@deepgriha.com / dga@student2026

## Repo
https://github.com/py-prachi/deep-griha-academy-mvp
