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
- feature/phase3-fees → current branch (ready to merge when Saru testing done)

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
