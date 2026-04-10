<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subject;
use App\Models\Promotion;
use App\Models\StudentTermMark;
use App\Http\Controllers\MarksController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class MarksTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
    protected $student;
    protected $sessionId;
    protected $classId;
    protected $sectionId;
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seedRolesAndPermissions();
        $this->seedSchoolStructure();

        $this->admin = User::factory()->create(['role' => 'admin', 'password' => Hash::make('password')]);
        $this->admin->assignRole('admin');

        $this->teacher = User::factory()->create(['role' => 'teacher', 'password' => Hash::make('password')]);
        $this->teacher->assignRole('teacher');
        $this->teacher->givePermissionTo(['view users', 'save marks', 'view exams']);

        $this->student = User::factory()->create(['role' => 'student', 'password' => Hash::make('password')]);
        $this->student->assignRole('student');

        Promotion::create([
            'student_id'     => $this->student->id,
            'session_id'     => $this->sessionId,
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'id_card_number' => 'TEST-001',
        ]);

        $this->subject = Subject::create([
            'name'      => 'Mathematics',
            'code'      => 'MATH',
            'sort_order' => 1,
            'is_active' => true,
            'mark_type' => 'marks',
        ]);

        // Assign teacher to this subject + class + section
        DB::table('subject_teachers')->insert([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'session_id' => $this->sessionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedRolesAndPermissions(): void
    {
        foreach (['view users', 'save marks', 'view exams', 'view academic settings'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::findByName('admin', 'web')->syncPermissions(Permission::all());
    }

    private function seedSchoolStructure(): void
    {
        $this->sessionId = DB::table('school_sessions')->insertGetId([
            'session_name' => '2025-2026', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->classId = DB::table('school_classes')->insertGetId([
            'class_name' => 'Class 3', 'session_id' => $this->sessionId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->sectionId = DB::table('sections')->insertGetId([
            'section_name' => 'A', 'room_no' => '101',
            'class_id' => $this->classId, 'session_id' => $this->sessionId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    // ── 1. CLASS GROUP DETECTION ──────────────────────────────────────────

    /** @test */
    public function class_group_is_correctly_derived_from_class_name()
    {
        $this->assertEquals('1-2', MarksController::getClassGroup('Class 1'));
        $this->assertEquals('1-2', MarksController::getClassGroup('Class 2'));
        $this->assertEquals('3-4', MarksController::getClassGroup('Class 3'));
        $this->assertEquals('3-4', MarksController::getClassGroup('Class 4'));
        $this->assertEquals('5-6', MarksController::getClassGroup('Class 5'));
        $this->assertEquals('5-6', MarksController::getClassGroup('Std 6'));
        $this->assertEquals('7-8', MarksController::getClassGroup('Class 7'));
        $this->assertEquals('7-8', MarksController::getClassGroup('Class 8'));
    }

    // ── 2. GRADE CALCULATION ──────────────────────────────────────────────

    /** @test */
    public function grade_is_correctly_calculated_from_percentage()
    {
        $this->assertEquals('A1', MarksController::calcGrade(95));
        $this->assertEquals('A1', MarksController::calcGrade(91));
        $this->assertEquals('A2', MarksController::calcGrade(85));
        $this->assertEquals('B1', MarksController::calcGrade(75));
        $this->assertEquals('B2', MarksController::calcGrade(65));
        $this->assertEquals('C1', MarksController::calcGrade(55));
        $this->assertEquals('C2', MarksController::calcGrade(45));
        $this->assertEquals('D',  MarksController::calcGrade(35));
        $this->assertEquals('E',  MarksController::calcGrade(20));
        $this->assertEquals('E',  MarksController::calcGrade(0));
    }

    // ── 3. MARKS INDEX PAGE ───────────────────────────────────────────────

    /** @test */
    public function admin_can_view_marks_index()
    {
        $response = $this->actingAs($this->admin)->get('/marks2');
        $response->assertStatus(200);
        $response->assertViewIs('marks.index');
    }

    /** @test */
    public function assigned_teacher_can_view_marks_index()
    {
        $response = $this->actingAs($this->teacher)->get('/marks2');
        $response->assertStatus(200);
        $response->assertViewIs('marks.index');
        // Teacher should only see their assigned subjects
        $response->assertViewHas('subjects');
    }

    /** @test */
    public function student_cannot_access_marks_entry()
    {
        $response = $this->actingAs($this->student)->get('/marks2');
        $response->assertStatus(403);
    }

    // ── 4. MARKS ENTRY FORM ───────────────────────────────────────────────

    /** @test */
    public function admin_can_open_marks_entry_for_any_subject()
    {
        $response = $this->actingAs($this->admin)->get('/marks2/entry?' . http_build_query([
            'subject_id' => $this->subject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
        ]));
        $response->assertStatus(200);
        $response->assertViewIs('marks.entry');
    }

    /** @test */
    public function assigned_teacher_can_open_marks_entry_for_their_subject()
    {
        $response = $this->actingAs($this->teacher)->get('/marks2/entry?' . http_build_query([
            'subject_id' => $this->subject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
        ]));
        $response->assertStatus(200);
        $response->assertViewIs('marks.entry');
    }

    /** @test */
    public function teacher_cannot_open_marks_entry_for_unassigned_subject()
    {
        $otherSubject = Subject::create([
            'name' => 'Science', 'code' => 'SCI',
            'sort_order' => 2, 'is_active' => true, 'mark_type' => 'marks',
        ]);

        $response = $this->actingAs($this->teacher)->get('/marks2/entry?' . http_build_query([
            'subject_id' => $otherSubject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
        ]));
        $response->assertStatus(403);
    }

    // ── 5. SAVING MARKS ───────────────────────────────────────────────────

    /** @test */
    public function teacher_can_save_marks_for_their_subject()
    {
        $response = $this->actingAs($this->teacher)->post('/marks2/store', [
            'subject_id' => $this->subject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
            'marks'      => [
                $this->student->id => [
                    'oral_internal'     => 12,
                    'activity_internal' => 13,
                    'test'              => 14,
                    'hw'                => 14,
                    'oral_written'      => 4,
                    'activity_written'  => 4,
                    'writing'           => 28,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_term_marks', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'term'       => 1,
        ]);
    }

    /** @test */
    public function marks_are_correctly_totalled_and_graded_on_save()
    {
        // Class 3 config: internal=60, written=40, total=100
        // oral_i=15, act_i=15, test=15, hw=15 → internal=60
        // oral_w=5, act_w=5, writing=30 → written=40
        // grand=100 → 100% → A1
        $this->actingAs($this->teacher)->post('/marks2/store', [
            'subject_id' => $this->subject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
            'marks'      => [
                $this->student->id => [
                    'oral_internal'     => 15,
                    'activity_internal' => 15,
                    'test'              => 15,
                    'hw'                => 15,
                    'oral_written'      => 5,
                    'activity_written'  => 5,
                    'writing'           => 30,
                ],
            ],
        ]);

        $mark = StudentTermMark::where('student_id', $this->student->id)
            ->where('subject_id', $this->subject->id)
            ->first();

        $this->assertEquals(60, $mark->internal_total);
        $this->assertEquals(40, $mark->written_total);
        $this->assertEquals(100, $mark->grand_total);
        $this->assertEquals('A1', $mark->grade);
    }

    /** @test */
    public function marks_can_be_updated_after_initial_save()
    {
        // Save once
        $this->actingAs($this->teacher)->post('/marks2/store', [
            'subject_id' => $this->subject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
            'marks'      => [$this->student->id => [
                'oral_internal' => 10, 'activity_internal' => 10,
                'test' => 10, 'hw' => 10,
                'oral_written' => 3, 'activity_written' => 3, 'writing' => 20,
            ]],
        ]);

        // Save again with updated marks
        $this->actingAs($this->teacher)->post('/marks2/store', [
            'subject_id' => $this->subject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
            'marks'      => [$this->student->id => [
                'oral_internal' => 15, 'activity_internal' => 15,
                'test' => 15, 'hw' => 15,
                'oral_written' => 5, 'activity_written' => 5, 'writing' => 30,
            ]],
        ]);

        // Should still be 1 row, not 2
        $this->assertEquals(1, StudentTermMark::where([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'term'       => 1,
        ])->count());

        $mark = StudentTermMark::where('student_id', $this->student->id)->first();
        $this->assertEquals(100, $mark->grand_total);
    }

    /** @test */
    public function absent_components_are_stored_and_score_zero()
    {
        // Mark student absent for 'test' and 'writing' components only
        $this->actingAs($this->teacher)->post('/marks2/store', [
            'subject_id' => $this->subject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
            'marks'      => [
                $this->student->id => [
                    'oral_internal'     => 10,
                    'activity_internal' => 8,
                    'absent'            => ['test' => '1', 'hw' => '1'],
                    'oral_written'      => 4,
                    'activity_written'  => 4,
                    'writing'           => 30,
                ],
            ],
        ]);

        $mark = StudentTermMark::where('student_id', $this->student->id)->first();
        $this->assertNotNull($mark);
        // test and hw are absent → 0; others entered normally
        $this->assertEquals(0, $mark->test);
        $this->assertEquals(0, $mark->hw);
        $this->assertEquals(10, $mark->oral_internal);
        // absent_components stores which ones were marked AB
        $this->assertContains('test', $mark->absent_components);
        $this->assertContains('hw', $mark->absent_components);
        $this->assertNotContains('oral_internal', $mark->absent_components);
        // Grand total: 10+8+0+0 + 4+4+30 = 56
        $this->assertEquals(56, $mark->grand_total);
    }

    // ── 6. GRADE-ONLY SUBJECT ─────────────────────────────────────────────

    /** @test */
    public function grade_only_subject_saves_grade_without_marks()
    {
        $gradeSubject = Subject::create([
            'name' => 'Physical Education', 'code' => 'PE',
            'sort_order' => 5, 'is_active' => true, 'mark_type' => 'grade_only',
        ]);
        DB::table('subject_teachers')->insert([
            'teacher_id' => $this->teacher->id, 'subject_id' => $gradeSubject->id,
            'class_id' => $this->classId, 'section_id' => $this->sectionId,
            'session_id' => $this->sessionId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->teacher)->post('/marks2/store', [
            'subject_id' => $gradeSubject->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'term'       => 1,
            'marks'      => [$this->student->id => ['grade' => 'A1']],
        ]);

        $mark = StudentTermMark::where([
            'student_id' => $this->student->id,
            'subject_id' => $gradeSubject->id,
        ])->first();

        $this->assertEquals('A1', $mark->grade);
        $this->assertNull($mark->grand_total);
    }

    // ── 7. CT REVIEW DASHBOARD ────────────────────────────────────────────

    /** @test */
    public function ct_can_view_marks_review_dashboard()
    {
        DB::table('class_teachers')->insert([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->classId,
            'section_id' => $this->sectionId, 'session_id' => $this->sessionId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('class_subjects')->insert([
            'subject_id' => $this->subject->id, 'class_id' => $this->classId,
            'session_id' => $this->sessionId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->teacher)->get('/marks2/review');
        $response->assertStatus(200);
        $response->assertViewIs('marks.review');
    }

    /** @test */
    public function admin_can_view_review_dashboard_with_class_picker()
    {
        $response = $this->actingAs($this->admin)->get('/marks2/review');
        $response->assertStatus(200);
        $response->assertViewHas('pick', true);
    }

    /** @test */
    public function review_shows_correct_completion_status()
    {
        DB::table('class_teachers')->insert([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->classId,
            'section_id' => $this->sectionId, 'session_id' => $this->sessionId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('class_subjects')->insert([
            'subject_id' => $this->subject->id, 'class_id' => $this->classId,
            'session_id' => $this->sessionId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Save a fully complete mark row for the student (all components filled)
        StudentTermMark::create([
            'student_id'         => $this->student->id,
            'subject_id'         => $this->subject->id,
            'class_id'           => $this->classId,
            'section_id'         => $this->sectionId,
            'session_id'         => $this->sessionId,
            'term'               => 1,
            'grade'              => 'A2',
            'grand_total'        => 85,
            'oral_internal'      => 10,
            'activity_internal'  => 10,
            'test'               => 20,
            'hw'                 => 10,
            'oral_written'       => 10,
            'activity_written'   => 10,
            'writing'            => 15,
            'internal_total'     => 50,
            'written_total'      => 35,
        ]);

        $response = $this->actingAs($this->teacher)->get('/marks2/review');
        $response->assertStatus(200);

        $grid = $response->viewData('statusGrid');
        $this->assertEquals('complete', $grid[$this->subject->id][1]['status']);
        $this->assertEquals('not_started', $grid[$this->subject->id][2]['status']);
    }
}
