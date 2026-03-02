# DGA Development Progress

## Phase 1 — COMPLETE ✅
- Database migrations (6 files)
- Seeders (roles, permissions, users, school structure)
- Database renamed to dga_school

## Phase 2 — COMPLETE ✅
- Eloquent Models:
  - Admission.php
  - AdmissionDocument.php
  - FeeStructure.php
  - FeePayment.php
  - FeeLineItem.php
  - LeavingCertificate.php
  - User.php (updated with DGA relationships)
- AdmissionInterface.php
- AdmissionRepository.php
- AdmissionServiceProvider.php
- AdmissionController.php (11 routes)
- Routes registered and verified

## Next Steps — Phase 2 UI (Blade Views)
Build these 4 views in order:
1. resources/views/admissions/index.blade.php — list all admissions with filters
2. resources/views/admissions/create.blade.php — new admission form (all 30 fields)
3. resources/views/admissions/show.blade.php — detail page + status change + doc checklist
4. resources/views/admissions/edit.blade.php — edit admission details
5. resources/views/admissions/cancelled.blade.php — archived cancelled admissions

Also need:
- Add Admissions link to admin sidebar navigation

## Phase 3 — NOT STARTED
- FeeStructureController + Interface + Repository
- FeePaymentController + Interface + Repository
- Challan PDF generation (DomPDF)
- Fee views

## Phase 4 — NOT STARTED
- LeavingCertificateController + Interface + Repository
- LC PDF generation
- 8 Reports

## Key Decisions (see FDD v1.1 for full details)
- Challan numbers: global running sequence (0001, 0002...)
- LC numbers: global running sequence (LC001, LC002...)
- DGA admission no: DGA/26-27/001 (resets per year, pre-primary only)
- Fee categories: general, rte, coc, discount
- COC: full fee shown as DGA Internal Transfer, parent balance = 0
- LC issuance: warning only if fees due, never blocked
- Soft delete for cancelled admissions
- Existing Add Student flow untouched

## Branch Strategy
- main → stable only
- develop → integration branch
- feature/phase2-admissions → current branch (ready to merge)

## Tech Stack
- Laravel 8.x + Blade + Bootstrap 5
- MySQL (database: dga_school)
- Docker (containers: app, db, nginx)
- Spatie Permissions for roles
- Repository pattern (Interface → Repository → ServiceProvider)

## Login Credentials
- Admin: admin@deepgriha.com / dga@admin2026
- Teacher: [name]@deepgriha.com / dga@teacher2026
- Student: auto-generated on admission confirmation

## Repo
https://github.com/py-prachi/deep-griha-academy-mvp
