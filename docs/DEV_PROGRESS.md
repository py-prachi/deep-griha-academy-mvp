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

## Known Issues (fix later)
- Attendance error for new students — needs course assignment first (existing system behaviour, not our bug)

## Next Steps — Phase 3
- FeeStructureInterface + Repository + Controller
- FeePaymentInterface + Repository + Controller  
- Challan PDF (DomPDF)
- Fee views: structure setup, student ledger, record payment
- Install DomPDF: composer require barryvdh/laravel-dompdf

## Phase 4 — NOT STARTED
- LeavingCertificateController
- LC PDF generation
- 8 Reports

## Key Decisions
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
- feature/phase2-admissions → merge to develop when ready
- feature/phase3-fees → next branch to create

## Tech Stack
- Laravel 8.x + Blade + Bootstrap 5
- MySQL (database: dga_school)
- Docker (containers: app, db, nginx)
- Spatie Permissions for roles
- Repository pattern (Interface → Repository → ServiceProvider)

## Login Credentials
- Admin: admin@deepgriha.com / dga@admin2026
- Teacher: [name]@deepgriha.com / dga@teacher2026
- Student: [auto-generated]@deepgriha.com / dga@student2026

## Repo
https://github.com/py-prachi/deep-griha-academy-mvp
