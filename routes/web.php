<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ExamRuleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\GradeRuleController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\GradingSystemController;
use App\Http\Controllers\SchoolSessionController;
use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\AssignedTeacherController;
use App\Http\Controllers\Auth\UpdatePasswordController;
use App\Http\Controllers\LeavingCertificateController;
use App\Http\Controllers\SessionSetupController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\MarksController;
use App\Http\Controllers\PrePrimaryController;
use App\Http\Controllers\TimetableController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/health', function () { return response('OK', 200); });

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::prefix('school')->name('school.')->group(function () {
        Route::post('session/create', [SchoolSessionController::class, 'store'])->name('session.store');
        Route::post('session/browse', [SchoolSessionController::class, 'browse'])->name('session.browse');
        Route::post('session/clone-classes', [SessionSetupController::class, 'cloneClasses'])->name('session.clone-classes');

        Route::post('semester/create', [SemesterController::class, 'store'])->name('semester.create');
        Route::post('final-marks-submission-status/update', [AcademicSettingController::class, 'updateFinalMarksSubmissionStatus'])->name('final.marks.submission.status.update');

        Route::post('attendance/type/update', [AcademicSettingController::class, 'updateAttendanceType'])->name('attendance.type.update');

        // Class
        Route::post('class/create', [SchoolClassController::class, 'store'])->name('class.create');
        Route::post('class/update', [SchoolClassController::class, 'update'])->name('class.update');

        // Sections
        Route::post('section/create', [SectionController::class, 'store'])->name('section.create');
        Route::post('section/update', [SectionController::class, 'update'])->name('section.update');

        // Courses
        Route::post('course/create', [CourseController::class, 'store'])->name('course.create');
        Route::post('course/update', [CourseController::class, 'update'])->name('course.update');

        // Teacher
        Route::post('teacher/create', [UserController::class, 'storeTeacher'])->name('teacher.create');
        Route::post('teacher/update', [UserController::class, 'updateTeacher'])->name('teacher.update');
        Route::post('teacher/assign', [AssignedTeacherController::class, 'store'])->name('teacher.assign');

        // Student
        Route::post('student/create', [UserController::class, 'storeStudent'])->name('student.create');
        Route::post('student/update', [UserController::class, 'updateStudent'])->name('student.update');
    });


    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Attendance
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendances/view', [AttendanceController::class, 'show'])->name('attendance.list.show');
    Route::get('/attendances/take', [AttendanceController::class, 'create'])->name('attendance.create.show');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');

    // Classes and sections
    Route::get('/classes', [SchoolClassController::class, 'index']);
    Route::get('/class/edit/{id}', [SchoolClassController::class, 'edit'])->name('class.edit');
    Route::get('/sections', [SectionController::class, 'getByClassId'])->name('get.sections.courses.by.classId');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->name('section.edit');

    // Teachers
    Route::get('/teachers/add', function () {
        return view('teachers.add');
    })->name('teacher.create.show');
    Route::get('/teachers/edit/{id}', [UserController::class, 'editTeacher'])->name('teacher.edit.show');
    Route::get('/teachers/view/list', [UserController::class, 'getTeacherList'])->name('teacher.list.show');
    Route::get('/teachers/view/profile/{id}', [UserController::class, 'showTeacherProfile'])->name('teacher.profile.show');

    //Students
    Route::get('/students/add', [UserController::class, 'createStudent'])->name('student.create.show');
    Route::get('/students/edit/{id}', [UserController::class, 'editStudent'])->name('student.edit.show');
    Route::get('/students/view/list', [UserController::class, 'getStudentList'])->name('student.list.show');
    Route::get('/students/view/profile/{id}', [UserController::class, 'showStudentProfile'])->name('student.profile.show');
    Route::get('/students/view/attendance/{id}', [AttendanceController::class, 'showStudentAttendance'])->name('student.attendance.show');
    Route::post('/admin/reset-password/{user_id}', [UserController::class, 'resetPassword'])->name('admin.resetPassword');

    // Marks
    Route::get('/marks/create', [MarkController::class, 'create'])->name('course.mark.create');
    Route::post('/marks/store', [MarkController::class, 'store'])->name('course.mark.store');
    Route::get('/marks/results', [MarkController::class, 'index'])->name('course.mark.list.show');
    // Route::get('/marks/view', function () {
    //     return view('marks.view');
    // });
    Route::get('/marks/view', [MarkController::class, 'showCourseMark'])->name('course.mark.show');
    Route::get('/marks/final/submit', [MarkController::class, 'showFinalMark'])->name('course.final.mark.submit.show');
    Route::post('/marks/final/submit', [MarkController::class, 'storeFinalMark'])->name('course.final.mark.submit.store');

    // Exams
    // New marks system routes
    Route::get('/marks2', [MarksController::class, 'index'])->name('marks.index');
    Route::get('/marks2/entry', [MarksController::class, 'entry'])->name('marks.entry');
    Route::post('/marks2/store', [MarksController::class, 'store'])->name('marks.store');
    Route::get('/marks2/review', [MarksController::class, 'review'])->name('marks.review');
    Route::post('/marks2/publish', [MarksController::class, 'publishTerm'])->name('marks.publishTerm');
    Route::get('/marks2/observations', [MarksController::class, 'observationsForm'])->name('marks.observations');
    Route::post('/marks2/observations', [MarksController::class, 'saveObservation'])->name('marks.saveObservation');
    Route::get('/marks2/print/{student_id}', [MarksController::class, 'printReportCard'])->name('marks.printReportCard');
    Route::get('/marks2/print-class', [MarksController::class, 'printClass'])->name('marks.printClass');
    Route::get('/my-marks', [MarksController::class, 'reportCard'])->name('marks.reportCard');

    // Pre-Primary (Nursery/LKG/UKG) skill assessment
    Route::get('/preprimary/entry', [PrePrimaryController::class, 'entry'])->name('preprimary.entry');
    Route::post('/preprimary/entry', [PrePrimaryController::class, 'store'])->name('preprimary.store');
    Route::get('/preprimary/narratives', [PrePrimaryController::class, 'narratives'])->name('preprimary.narratives');
    Route::post('/preprimary/narratives', [PrePrimaryController::class, 'saveNarratives'])->name('preprimary.saveNarratives');
    Route::get('/preprimary/print/{student_id}', [PrePrimaryController::class, 'printReportCard'])->name('preprimary.printReportCard');
    Route::get('/preprimary/print-class', [PrePrimaryController::class, 'printClass'])->name('preprimary.printClass');

    // Old exam route — redirect to avoid 500 crash until rebuilt
    Route::get('/exams/view', function() {
        return redirect('/marks2')->with('info', 'The exam system has been rebuilt. Please use the new Marks section.');
    })->name('exam.list.show');
    // Route::get('/exams/view/history', function () {
    //     return view('exams.history');
    // });
    Route::post('/exams/create', [ExamController::class, 'store'])->name('exam.create');
    // Route::post('/exams/delete', [ExamController::class, 'delete'])->name('exam.delete');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exam.create.show');
    Route::get('/exams/add-rule', [ExamRuleController::class, 'create'])->name('exam.rule.create');
    Route::post('/exams/add-rule', [ExamRuleController::class, 'store'])->name('exam.rule.store');
    Route::get('/exams/edit-rule', [ExamRuleController::class, 'edit'])->name('exam.rule.edit');
    Route::post('/exams/edit-rule', [ExamRuleController::class, 'update'])->name('exam.rule.update');
    Route::get('/exams/view-rule', [ExamRuleController::class, 'index'])->name('exam.rule.show');
    Route::get('/exams/grade/create', [GradingSystemController::class, 'create'])->name('exam.grade.system.create');
    Route::post('/exams/grade/create', [GradingSystemController::class, 'store'])->name('exam.grade.system.store');
    Route::get('/exams/grade/view', [GradingSystemController::class, 'index'])->name('exam.grade.system.index');
    Route::get('/exams/grade/add-rule', [GradeRuleController::class, 'create'])->name('exam.grade.system.rule.create');
    Route::post('/exams/grade/add-rule', [GradeRuleController::class, 'store'])->name('exam.grade.system.rule.store');
    Route::get('/exams/grade/view-rules', [GradeRuleController::class, 'index'])->name('exam.grade.system.rule.show');
    Route::post('/exams/grade/delete-rule', [GradeRuleController::class, 'destroy'])->name('exam.grade.system.rule.delete');

    // Promotions
    Route::get('/promotions/index', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/class/{class_id}', [PromotionController::class, 'classView'])->name('promotions.class');
    Route::get('/promotions/promote', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/promote', [PromotionController::class, 'store'])->name('promotions.store');

    // Academic settings
    Route::get('/academics/settings', [AcademicSettingController::class, 'index']);

    // Subjects
    Route::get('/academics/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/academics/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/academics/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/academics/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::post('/academics/subjects/class-assignments', [SubjectController::class, 'saveClassSubjects'])->name('subjects.saveClassSubjects');
    Route::post('/academics/subjects/bulk-assign-1to8', [SubjectController::class, 'bulkAssignClasses1to8'])->name('subjects.bulkAssign1to8');
    Route::post('/promotions/reassign-roll-numbers', [App\Http\Controllers\PromotionController::class, 'reassignRollNumbers'])->name('promotions.reassignRollNumbers');

    // Teacher assignments (new clean flow)
    Route::get('/academics/teacher-assignments', [SubjectController::class, 'teacherAssignments'])->name('academics.teacher-assignments');
    Route::get('/academics/class-subjects-json', [SubjectController::class, 'classSubjectsJson'])->name('academics.classSubjectsJson');
    Route::post('/academics/class-teacher', [SubjectController::class, 'saveClassTeacher'])->name('academics.saveClassTeacher');
    Route::delete('/academics/class-teacher/{classTeacher}', [SubjectController::class, 'removeClassTeacher'])->name('academics.removeClassTeacher');
    Route::post('/academics/subject-teacher', [SubjectController::class, 'saveSubjectTeacher'])->name('academics.saveSubjectTeacher');
    Route::delete('/academics/subject-teacher/{subjectTeacher}', [SubjectController::class, 'removeSubjectTeacher'])->name('academics.removeSubjectTeacher');

    // Calendar events
    Route::get('calendar-event', [EventController::class, 'index'])->name('events.show');
    Route::post('calendar-crud-ajax', [EventController::class, 'calendarEvents'])->name('events.crud');

    // Routines
    Route::get('/routine/create', [RoutineController::class, 'create'])->name('section.routine.create');
    Route::get('/routine/view', [RoutineController::class, 'show'])->name('section.routine.show');
    Route::post('/routine/store', [RoutineController::class, 'store'])->name('section.routine.store');

    // Syllabus
    Route::get('/syllabus/create', [SyllabusController::class, 'create'])->name('class.syllabus.create');
    Route::post('/syllabus/create', [SyllabusController::class, 'store'])->name('syllabus.store');
    Route::get('/syllabus/index', [SyllabusController::class, 'index'])->name('course.syllabus.index');

    // Notices
    Route::get('/notice/create', [NoticeController::class, 'create'])->name('notice.create');
    Route::post('/notice/create', [NoticeController::class, 'store'])->name('notice.store');

    // Courses
    Route::get('courses/teacher/index', [AssignedTeacherController::class, 'getTeacherCourses'])->name('course.teacher.list.show');
    Route::get('courses/student/index/{student_id}', [CourseController::class, 'getStudentCourses'])->name('course.student.list.show');
    Route::get('course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');

    // Assignment
    Route::get('courses/assignments/index', [AssignmentController::class, 'getCourseAssignments'])->name('assignment.list.show');
    Route::get('courses/assignments/create', [AssignmentController::class, 'create'])->name('assignment.create');
    Route::post('courses/assignments/create', [AssignmentController::class, 'store'])->name('assignment.store');

    // Update password
    Route::get('password/edit', [UpdatePasswordController::class, 'edit'])->name('password.edit');
    Route::post('password/edit', [UpdatePasswordController::class, 'update'])->name('password.update');

    Route::post(
    'attendance/update/{attendance}',
    [AttendanceController::class, 'update']
)->name('attendance.update');

});

// ── ADMISSIONS ────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/admissions',                           [App\Http\Controllers\AdmissionController::class, 'index'])->name('admissions.index');
    Route::get('/admissions/create',                    [App\Http\Controllers\AdmissionController::class, 'create'])->name('admissions.create');
    Route::post('/admissions',                          [App\Http\Controllers\AdmissionController::class, 'store'])->name('admissions.store');
    Route::get('/admissions/{id}',                      [App\Http\Controllers\AdmissionController::class, 'show'])->name('admissions.show');
    Route::get('/admissions/{id}/edit',                 [App\Http\Controllers\AdmissionController::class, 'edit'])->name('admissions.edit');
    Route::put('/admissions/{id}',                      [App\Http\Controllers\AdmissionController::class, 'update'])->name('admissions.update');
    Route::post('/admissions/{id}/mark-pending',        [App\Http\Controllers\AdmissionController::class, 'markPending'])->name('admissions.markPending');
    Route::post('/admissions/{id}/confirm',             [App\Http\Controllers\AdmissionController::class, 'confirm'])->name('admissions.confirm');
    Route::post('/admissions/{id}/cancel',              [App\Http\Controllers\AdmissionController::class, 'cancel'])->name('admissions.cancel');
    Route::post('/admissions/{id}/document/{doc_id}',   [App\Http\Controllers\AdmissionController::class, 'updateDocument'])->name('admissions.updateDocument');
    Route::get('/admissions-cancelled',                 [App\Http\Controllers\AdmissionController::class, 'cancelled'])->name('admissions.cancelled');

    // ── BULK IMPORT ───────────────────────────────────────────────────────
    Route::get('/import/students',          [App\Http\Controllers\StudentImportController::class, 'showForm'])->name('import.students');
    Route::get('/import/students/template', [App\Http\Controllers\StudentImportController::class, 'downloadTemplate'])->name('import.students.template');
    Route::post('/import/students/preview', [App\Http\Controllers\StudentImportController::class, 'preview'])->name('import.students.preview');
    Route::post('/import/students/commit',  [App\Http\Controllers\StudentImportController::class, 'commit'])->name('import.students.commit');

    // ── FEE STRUCTURE ─────────────────────────────────────────────────────
    Route::get('/fee-structures',                        [App\Http\Controllers\FeeStructureController::class, 'index'])->name('fee-structures.index');
    Route::get('/fee-structures/create',                 [App\Http\Controllers\FeeStructureController::class, 'create'])->name('fee-structures.create');
    Route::post('/fee-structures',                       [App\Http\Controllers\FeeStructureController::class, 'store'])->name('fee-structures.store');
    Route::get('/fee-structures/{id}/edit',              [App\Http\Controllers\FeeStructureController::class, 'edit'])->name('fee-structures.edit');
    Route::put('/fee-structures/{id}',                   [App\Http\Controllers\FeeStructureController::class, 'update'])->name('fee-structures.update');
    Route::delete('/fee-structures/{id}',                [App\Http\Controllers\FeeStructureController::class, 'destroy'])->name('fee-structures.destroy');

    // ── FEE PAYMENTS ──────────────────────────────────────────────────────
    Route::get('/fees/collect',                          [App\Http\Controllers\FeePaymentController::class, 'collectFee'])->name('fees.collect');
    Route::get('/fees/student/{student_id}',             [App\Http\Controllers\FeePaymentController::class, 'ledger'])->name('fees.ledger');
    Route::get('/fees/student/{student_id}/pay',         [App\Http\Controllers\FeePaymentController::class, 'create'])->name('fees.create');
    Route::post('/fees/student/{student_id}/pay',        [App\Http\Controllers\FeePaymentController::class, 'store'])->name('fees.store');
    Route::get('/fees/challan/{payment_id}',             [App\Http\Controllers\FeePaymentController::class, 'challan'])->name('fees.challan');
    Route::get('/fees/challan/{payment_id}/pdf',         [App\Http\Controllers\FeePaymentController::class, 'challanPdf'])->name('fees.challan.pdf');

    // ── FEE REPORTS ───────────────────────────────────────────────────────
    Route::get('/reports/fees/daily',                    [App\Http\Controllers\FeeReportController::class, 'daily'])->name('reports.fees.daily');
    Route::get('/reports/fees/date-range',               [App\Http\Controllers\FeeReportController::class, 'dateRange'])->name('reports.fees.dateRange');
    Route::get('/reports/fees/defaulters',               [App\Http\Controllers\FeeReportController::class, 'defaulters'])->name('reports.fees.defaulters');
    Route::get('/reports/fees/category-summary',         [App\Http\Controllers\FeeReportController::class, 'categorySummary'])->name('reports.fees.categorySummary');
    Route::get('/reports/admissions',                    [App\Http\Controllers\FeeReportController::class, 'admissions'])->name('reports.admissions');
    Route::get('/reports/class-strength',                [App\Http\Controllers\FeeReportController::class, 'classStrength'])->name('reports.classStrength');
    Route::get('/reports/rte',                           [App\Http\Controllers\FeeReportController::class, 'rte'])->name('reports.rte');
    Route::get('/reports/misc-sales',                    [App\Http\Controllers\FeeReportController::class, 'miscSales'])->name('reports.miscSales');

    // ── LEAVING CERTIFICATES ──────────────────────────────────────────────
    Route::prefix('lc')->name('lc.')->group(function () {
        Route::get('/',             [LeavingCertificateController::class, 'index'])->name('index');
        Route::get('/create',       [LeavingCertificateController::class, 'create'])->name('create');
        Route::post('/',            [LeavingCertificateController::class, 'store'])->name('store');
        Route::get('/student-info', [LeavingCertificateController::class, 'studentInfo'])->name('student-info');
        Route::get('/{id}',         [LeavingCertificateController::class, 'show'])->name('show');
        Route::get('/{id}/pdf',     [LeavingCertificateController::class, 'pdf'])->name('pdf');
    });

    // Timetable
    Route::get('/timetable/edit',           [TimetableController::class, 'edit'])->name('timetable.edit');
    Route::post('/timetable/save',          [TimetableController::class, 'save'])->name('timetable.save');
    Route::get('/timetable/view',           [TimetableController::class, 'show'])->name('timetable.show');
    Route::get('/timetable/teacher',        [TimetableController::class, 'teacherView'])->name('timetable.teacher');
    Route::get('/timetable/student',        [TimetableController::class, 'studentView'])->name('timetable.student');
    Route::post('/timetable/period/update',       [TimetableController::class, 'periodUpdate'])->name('timetable.period.update');
    Route::post('/timetable/period/delete',       [TimetableController::class, 'periodDelete'])->name('timetable.period.delete');
    Route::post('/timetable/period/add',          [TimetableController::class, 'periodAdd'])->name('timetable.period.add');
    Route::post('/timetable/period/copy-defaults',[TimetableController::class, 'periodCopyDefaults'])->name('timetable.period.copy-defaults');
    Route::post('/timetable/period/reset-day',    [TimetableController::class, 'periodResetDay'])->name('timetable.period.reset-day');

    // ── STUDENT EXIT ──────────────────────────────────────────────
    Route::get('/exits',              [App\Http\Controllers\StudentExitController::class, 'index'])->name('exits.index');
    Route::get('/exits/create',       [App\Http\Controllers\StudentExitController::class, 'create'])->name('exits.create');
    Route::post('/exits',             [App\Http\Controllers\StudentExitController::class, 'store'])->name('exits.store');
    Route::get('/exits/student-info', [App\Http\Controllers\StudentExitController::class, 'studentInfo'])->name('exits.studentInfo');
    Route::get('/exits/{id}',         [App\Http\Controllers\StudentExitController::class, 'show'])->name('exits.show');
});
