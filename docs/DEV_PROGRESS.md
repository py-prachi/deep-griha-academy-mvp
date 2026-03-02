# DGA Development Progress

## Phase 1 — COMPLETE ✅
- Database migrations (6 files)
- Seeders (roles, permissions, users, school structure)
- Database renamed to dga_school

## Phase 2 — IN PROGRESS 🔄
- Eloquent Models created:
  - Admission.php
  - AdmissionDocument.php
  - FeeStructure.php
  - FeePayment.php
  - FeeLineItem.php
  - LeavingCertificate.php
  - User.php (updated with DGA relationships)

## Next Steps (Phase 2 continued)
- AdmissionController
- Routes for admissions
- Blade views:
  - Admission list page
  - New admission form (all 30 fields)
  - Admission detail/profile page
  - Status change flow

## Phase 3 — NOT STARTED
- Fee Management
- Challan PDF generation

## Phase 4 — NOT STARTED
- Leaving Certificate
- Reports

## Key Decisions (see FDD v1.1 for full details)
- Challan numbers: global running sequence
- LC numbers: global running sequence (LC001...)
- DGA admission no: DGA/26-27/001 (resets per year, pre-primary only)
- Fee categories: general, rte, coc, discount
- COC: full fee shown as DGA Internal Transfer, parent balance = 0
- LC issuance: warning only if fees due, never blocked
- Soft delete for cancelled admissions
- Existing Add Student flow untouched — runs parallel to new Admissions module

## Branch Strategy
- main → stable only
- develop → integration branch
- feature/phase2-admissions → current branch

## Tech Stack
- Laravel 8.x + Blade + Bootstrap 5
- MySQL (database: dga_school)
- Docker (containers: app, db, nginx)
- Spatie Permissions for roles

## Login Credentials
- Admin: admin@deepgriha.com / dga@admin2026
- Teacher: [name]@deepgriha.com / dga@teacher2026

## Repo
https://github.com/py-prachi/deep-griha-academy-mvp
