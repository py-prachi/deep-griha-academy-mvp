# Deep Griha Academy — Dev Plan & Progress Tracker

> Reference document for ongoing development.
> Update status as features are completed.
> Use this as context when starting a new chat window.

---

## Project Stack
- Laravel 8.x, PHP 7.x (no nullsafe `?->`, no named args, no match)
- Bootstrap 5, Blade templates
- Repository pattern throughout
- Docker locally, Railway staging, Railway Pro (planned prod)
- Branch: `feature/promotions-hardening` (off develop, not merged yet)

---

## School Context
- **Deep Griha Academy**, Pune — NGO school, Classes Nursery to Class 8
- **Academic year**: Two terms (Term 1, Term 2)
- **Grading scale** (Classes 1–8): A1(91-100), A2(81-90), B1(71-80), B2(61-70), C1(51-60), C2(41-50), D(33-40), E(<33)
- **Pre-primary scale**: E=Excellent(70-100%), S=Satisfactory(50-70%), I=Improvement needed(30-50%), D=Still developing(0-30%)

---

## Mark Distribution Per Class Group (Both Terms Same)

| Class Group | Internal Assessment | Written | Total |
|---|---|---|---|
| Class 1 & 2 | Oral(15)+Activity(10)+Test(15)+HW(10) = 50 | Oral(5)+Activity(5)+Writing(40) = 50 | 100 |
| Class 3 & 4 | Oral(15)+Activity(15)+Test(15)+HW(15) = 60 | Oral(5)+Activity(5)+Writing(30) = 40 | 100 |
| Class 5 & 6 | Oral(15)+Activity(10)+Test(15)+HW(10) = 50 | Oral(5)+Activity(5)+Writing(40) = 50 | 100 |
| Class 7 & 8 | Oral(5)+Activity(10)+Test(15)+HW(10) = 40 | Oral(5)+Activity(5)+Writing(50) = 60 | 100 |

---

## Subjects (Classes 1–8 Report Card)
English, Marathi, Hindi, Mathematics, General Science & EVS, Social Science,
Physical Education, Library, Computer, Agriculture, Vocational Studies, Vocal, Tabla, Dance

---

## Pre-Primary Report Card (Nursery / LKG / UKG)
- Skill-based checklist: Language & Communication, Cognitive Development,
  Personal/Social/Emotional Development, Physical Development
- Each skill rated E/S/I/D for Term 1 and Term 2
- Back page: "Does well in", "Needs to improve in", "Observations & Comments" (free text per term)
- Monthly observation notes (April, June–March) — internal teacher tracking

---

## Feature Status

### ✅ COMPLETED
- Admissions flow (import via Excel, manual entry, confirm, cancel)
- Admissions report with pagination + class filter
- Student profiles and edit
- Promotions flow (hardened: validation, session context, redirect, graduated flag)
- Academic session management
- Fee structures and payments (regular + misc)
- Academic Settings page (rebuilt — removed college model cards)
- Subjects CRUD + class-subject assignments per session
- Class Teachers assignment (with subject checkboxes)
- Subject Teachers assignment (with class filter on list)
- Teachers menu (add teacher, list teachers, profile, edit)
- Teacher auto-credentials (email: firstname.lastname@dga.teacher, pw: dga@teacher2026)
- Photo fields removed from all forms (deferred to Cloudinary phase)
- Zip/Address2 added to teacher add form

### 🔧 IN PROGRESS / BROKEN — NEEDS FIX
- **Attendance index** (`/attendances`):
  - Currently shows ALL classes/sections to everyone
  - CT should see ONLY their assigned section (from class_teachers table)
  - Admin sees all classes — can mark any section
  - `AttendanceController::index()` currently returns `back()` — broken
  - `index.blade.php` has accordion but shows all sections without CT filter
- **Exam/Marks system**: Completely broken — depends on Semester model which has no data.
  `/exams/view` crashes with null->id error.
  Needs full rebuild for Deep Griha's mark structure (see below).

### 📋 PENDING — BUILD IN ORDER

#### 1. Attendance (HIGH PRIORITY — daily use)
**What to build:**
- `AttendanceController::index()`:
  - Admin → show all classes/sections with Take/View links
  - CT (teacher) → auto-redirect directly to their assigned section's Take Attendance page
- CT section comes from `class_teachers` table (session-scoped)
- Take attendance: P / A / L (Late) per student, date picker (defaults today)
- Already mostly working — just needs CT-scoping on the index + fix the index() method

**Files to touch:**
- `app/Http/Controllers/AttendanceController.php` — fix `index()`
- `resources/views/attendances/index.blade.php` — admin view only (CT bypasses this)
- Routes: verify attendance routes in `routes/web.php`

---

#### 2. Marks Entry System (MEDIUM PRIORITY)
**What to build:**
- New migrations:
  - `exam_terms` (Term 1, Term 2 per session)
  - `student_marks` (student_id, subject_id, class_id, session_id, term, component, marks)
  - Components: oral_internal, activity_internal, test, hw, oral_written, activity_written, writing
- `MarksController` (new, replaces broken ExamController):
  - Subject teacher: sees only their assigned subjects → selects class/section/term → enters marks per student per component
  - Auto-calculates internal total, written total, grand total, grade
  - CT review dashboard: grid of subjects × completion status (green/orange/red)
- Mark components configured per class group (from class_group_config table or hardcoded initially)

**Files to create:**
- `database/migrations/xxxx_create_exam_terms_table.php`
- `database/migrations/xxxx_create_student_marks_table.php`
- `app/Http/Controllers/MarksController.php`
- `resources/views/marks/entry.blade.php`
- `resources/views/marks/review.blade.php` (CT dashboard)

---

#### 3. Report Card View (MEDIUM PRIORITY)
**What to build:**
- Auto-calculated from `student_marks`
- Classes 1–8: Subject | Term 1 Grade | Term 2 Grade table + Descriptive Remarks
- Pre-primary: Skill checklist with E/S/I/D per term
- Attendance summary (from attendance table)
- PDF export (using Laravel DomPDF or Snappy)

---

#### 4. Monthly Observations — Pre-Primary Only (LOWER PRIORITY)
**What to build:**
- `monthly_observations` table (student_id, session_id, month, notes)
- CT view: student list → click student → enter note per month
- Timeline view per student across all months

---

#### 5. Exam System Cleanup (LOWER PRIORITY)
- `/exams/view` crashes — fix by guarding against null semester
- Eventually replace entirely with new Marks system above
- Disable/hide exam menu items that are broken until rebuilt

---

#### 6. Session Clone (DEFERRED)
- Clone session: copy classes, sections, subjects, fee structures to new session
- Was in progress, put on hold for attendance/marks priority

---

## Key Database Tables Reference

| Table | Purpose |
|---|---|
| `users` | Students (role=student) and teachers (role=teacher) |
| `admissions` | Admission records, links to user via student_user_id |
| `promotions` | student↔session↔class↔section mapping (source of truth) |
| `school_sessions` | Academic years |
| `school_classes` | Classes per session |
| `sections` | Sections per class |
| `subjects` | Subject master list |
| `class_subjects` | Subject assigned to class for a session |
| `class_teachers` | CT assigned to class+section for a session |
| `subject_teachers` | Subject teacher assigned to subject+class+section |
| `attendances` | Daily attendance (student_id, date, status P/A/L) |
| `fee_structures` | Fee categories per class per session |
| `fee_payments` | Payment records |
| `misc_payments` | Miscellaneous income |

---

## Important Notes for New Chat Windows
1. Never use PHP 8 syntax (nullsafe `?->`, named args, match expressions)
2. Docker workflow: after code changes run `docker cp` to sync files to container
3. The `feature/promotions-hardening` branch is the active branch — don't merge to develop yet
4. Angela (school admin) is testing on staging — don't break existing working features
5. `getSchoolCurrentSession()` trait drives ALL session-scoped queries — always use it
6. Students are confirmed admissions — `promotions` table is the student↔session link
7. Photos deferred to Cloudinary integration (future phase)
8. Railway Pro is planned production host (~₹840/month, fully managed)
