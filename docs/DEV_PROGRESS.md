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
All bugs introduced by Phase 2 into existing flows fixed:

### Attendance fixes
- `attendance.blade.php`: replaced HTML comments with Blade comments `{{-- --}}`
  to prevent compiled execution of broken ternary
- Null-safe context column (section / course / dash fallback)
- Show warning when student has no promotion record
- `AttendanceController`: derive class/section from Promotion record
  not attendance history (fixes new admission-confirmed students)

### Student List fix
- `UserRepository::getAllStudents()`: removed dangerous silent fallback
  that returned wrong students when class/section IDs were 0
- `UserController::getStudentList()`: use `getAllStudentsBySession()`
  when no filter selected — View Students now loads correctly

### Student Profile fixes
- Null-safe all `parent_info` / `academic_info` / `promotion_info` accesses
- Admission-flow students: parent data sourced from `admissions` table
- Show `admission_no` (general_id / dga_admission_no) instead of board_reg_no
- Birthday formatted as `d M Y` (no timestamp)
- Address overflow fixed with `table-layout:fixed` + `colspan`
- `UserRepository::findStudent()`: eager-load `admission` relationship

### Student Edit fixes
- Birthday pre-populated correctly using Carbon::parse
- Blood type and religion show `-- Select --` when null
- Mother phone now saves via `updateOrCreate` in `StudentParentInfoRepository`
  (admission-flow students had no parent_info row — update() was silently failing)
- Parent fields pre-filled from admission record for admission-flow students

### Admission Form improvements
- Religion changed from free text to dropdown
- `contact_mobile` replaced with explicit `father_phone` + `mother_phone` fields
- `city` and `zip` fields added (were hardcoded as Daund/413801 in confirm())
- Blood type field added to create / edit / show / index / cancelled views
- Cancelled admissions view: added father's phone column
- Admissions list: Mobile → Father's Phone, Village → City

### Migrations added
- `add_blood_type_to_admissions_table`
- `add_contact_fields_to_admissions_table` (father_phone, mother_phone, city, zip)

## Phase 2.5 — Feature Tests — COMPLETE ✅
- 31 automated feature tests in `tests/Feature/AdmissionTest.php`
- All 31 passing ✅
- Coverage:
  - Admission access control (admin / teacher / student / guest)
  - Create inquiry with validation (student name, father phone, blood type)
  - Admission status flow (pending → cancel → confirm)
  - Confirm creates student user with correct field mapping
  - Student profile (admission parent data, admission no, no crash without promotion)
  - Student list (role access, loads without filter)
  - Attendance (role access, IDs from promotion not history,
    no crash without records, warning when no promotion)
- Fixed admissions migration gender enum to `['Male', 'Female']`
  for SQLite test compatibility

### How to run tests
```bash
# Run Phase 2 tests only
docker exec app php artisan test tests/Feature/AdmissionTest.php --testdox

# Run all tests
docker exec app php artisan test --testdox
```

## Pending — Saru Regression Testing (Teacher + Student roles)

### Teacher role — `anita.sharma@deepgriha.com` / `dga@teacher2026`
| # | What to test | Expected |
|---|---|---|
| 1 | Dashboard loads | Shows counts, notices |
| 2 | My Courses visible | Shows assigned class/section/subject |
| 3 | Take attendance from My Courses | Can mark present/absent |
| 4 | View attendance from My Courses | Shows marked attendance |
| 5 | View student list | Shows all students |
| 6 | Click student profile | Profile loads correctly |
| 7 | Create exam | Dropdown shows only assigned class |
| 8 | View exams | Shows created exams |
| 9 | Admissions page | Should show 403 error |
| 10 | Add student page | Should show 403 error |

### Student role — `test.student.b.2@deepgriha.com` / `dga@student2026`
| # | What to test | Expected |
|---|---|---|
| 1 | Dashboard loads | Shows student dashboard |
| 2 | My attendance | Shows own attendance page |
| 3 | My courses | Shows enrolled courses |
| 4 | Admissions page | Should show 403 error |
| 5 | Another student's attendance URL | Should show 404 error |
| 6 | Student list URL | Should show 403 error |

## Known Issues (pre-existing — fix in separate branch later)
- View exams does not show created exam after creation
- Exam dropdown not filtered by teacher's assigned classes
- Semester assignment not visible on UI for courses

## Next Steps — Phase 3 (Fees)

### Branch
```bash
git checkout develop
git pull
git checkout -b feature/phase3-fees
```

### Install DomPDF
```bash
docker exec app composer require barryvdh/laravel-dompdf
```

### What to build
- `FeeStructureInterface` + `FeeStructureRepository` + `FeeStructureServiceProvider`
- `FeePaymentInterface` + `FeePaymentRepository` + `FeePaymentServiceProvider`
- `FeeStructureController` — setup fee structures per class/category
- `FeePaymentController` — record payments, generate challans
- Challan PDF via DomPDF
- Fee views:
  - Fee structure setup
  - Student fee ledger (what's due, what's paid, balance)
  - Record payment
  - Challan view/print

### Key decisions already made
- Challan numbers: global running sequence (0001, 0002...)
- Fee categories: general, rte, coc, discount
- COC: full fee shown as DGA Internal Transfer, parent balance = 0
- LC issuance: warning only if fees due, never blocked

## Phase 4 — NOT STARTED
- LeavingCertificateController
- LC PDF generation
- LC numbers: global running sequence (LC001, LC002...)
- 8 Reports

## Key Decisions
- DGA admission no: DGA/26-27/001 (resets per year, pre-primary only)
- General ID: manually entered by admin at confirmation (non pre-primary)
- Soft delete for cancelled admissions
- Existing Add Student flow untouched
- Show all students by default on student list (filter available)
- Religion options: Hinduism, Islam, Christianity, Buddhism, Judaism, Other

## Branch Strategy
- `main` → stable only
- `develop` → integration branch
- `fix/phase2-student-attendance` → merged to develop ✅
- `test/phase2-feature-tests` → merged to develop ✅
- `feature/phase3-fees` → next branch

## Tech Stack
- Laravel 8.x + Blade + Bootstrap 5
- MySQL (database: dga_school)
- Docker (containers: app, db, nginx)
- Spatie Permissions for roles
- Repository pattern (Interface → Repository → ServiceProvider)
- PHPUnit 9.5 for feature tests

## Login Credentials
- Admin: admin@deepgriha.com / dga@admin2026
- Teacher: [name]@deepgriha.com / dga@teacher2026
- Student: [auto-generated]@deepgriha.com / dga@student2026

## Repo
https://github.com/py-prachi/deep-griha-academy-mvp