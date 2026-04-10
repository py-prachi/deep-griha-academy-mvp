<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
    protected $student;
    protected $sessionId;
    protected $classId;
    protected $sectionId;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seedRolesAndPermissions();
        $this->seedSchoolStructure();
        DB::table('academic_settings')->insert([
            'attendance_type' => 'section',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->admin = User::factory()->create(['role' => 'admin', 'password' => Hash::make('password')]);
        $this->admin->assignRole('admin');

        $this->teacher = User::factory()->create(['role' => 'teacher', 'password' => Hash::make('password')]);
        $this->teacher->assignRole('teacher');
        $this->teacher->givePermissionTo(['view attendances', 'take attendances', 'view users']);

        $this->student = User::factory()->create(['role' => 'student', 'password' => Hash::make('password')]);
        $this->student->assignRole('student');
        $this->student->givePermissionTo(['view attendances']);

        Promotion::create([
            'student_id'     => $this->student->id,
            'session_id'     => $this->sessionId,
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'id_card_number' => 'TEST-001',
        ]);
    }

    private function seedRolesAndPermissions(): void
    {
        foreach (['take attendances', 'view attendances', 'view users', 'view academic settings'] as $p) {
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

    // ── 1. ADMIN CAN VIEW ATTENDANCE INDEX ───────────────────────────────

    /** @test */
    public function admin_can_view_attendance_index()
    {
        $response = $this->actingAs($this->admin)->get('/attendances');
        $response->assertStatus(200);
        $response->assertViewIs('attendances.index');
    }

    // ── 2. CT IS REDIRECTED TO THEIR SECTION ─────────────────────────────

    /** @test */
    public function class_teacher_is_redirected_to_their_section_on_attendance_index()
    {
        // Assign teacher as CT for class/section
        DB::table('class_teachers')->insert([
            'teacher_id' => $this->teacher->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'session_id' => $this->sessionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->teacher)->get('/attendances');

        $response->assertRedirect();
        $response->assertRedirectContains('class_id=' . $this->classId);
        $response->assertRedirectContains('section_id=' . $this->sectionId);
    }

    // ── 3. TEACHER NOT ASSIGNED AS CT SEES WARNING ───────────────────────

    /** @test */
    public function teacher_not_assigned_as_ct_sees_not_assigned_message()
    {
        // Teacher has no class_teacher record
        $response = $this->actingAs($this->teacher)->get('/attendances');
        $response->assertStatus(200);
        $response->assertViewHas('not_assigned', true);
    }

    // ── 4. TAKE ATTENDANCE PAGE LOADS ────────────────────────────────────

    /** @test */
    public function admin_can_open_take_attendance_for_any_section()
    {
        $response = $this->actingAs($this->admin)->get(
            '/attendances/take?class_id=' . $this->classId . '&section_id=' . $this->sectionId
        );
        $response->assertStatus(200);
        $response->assertViewIs('attendances.take');
    }

    // ── 5. ATTENDANCE IS SAVED ────────────────────────────────────────────

    /** @test */
    public function admin_can_save_attendance()
    {
        $response = $this->actingAs($this->admin)->post('/attendances', [
            'student_ids'    => [$this->student->id],
            'status'         => [$this->student->id => 'on'],
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'session_id'     => $this->sessionId,
            'course_id'      => 0,
            'attendance_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
        ]);
    }

    // ── 6. TEACHER CAN SAVE ATTENDANCE FOR THEIR SECTION ─────────────────

    /** @test */
    public function ct_can_save_attendance_for_their_section()
    {
        DB::table('class_teachers')->insert([
            'teacher_id' => $this->teacher->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'session_id' => $this->sessionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->teacher)->post('/attendances', [
            'student_ids'    => [$this->student->id],
            'status'         => [$this->student->id => 'on'],
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'session_id'     => $this->sessionId,
            'course_id'      => 0,
            'attendance_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', ['student_id' => $this->student->id]);
    }

    // ── 7. STUDENT CANNOT POST ATTENDANCE ────────────────────────────────

    /** @test */
    public function student_cannot_post_attendance()
    {
        // Students have 'view attendances' so can GET the page,
        // but the store action checks for role teacher/admin
        $response = $this->actingAs($this->student)->post('/attendances', [
            'student_ids'    => [$this->student->id],
            'status'         => [$this->student->id => 'on'],
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'session_id'     => $this->sessionId,
            'course_id'      => 0,
        ]);
        $response->assertStatus(403);
    }

    // ── 8. UNAUTHENTICATED USER IS REDIRECTED TO LOGIN ───────────────────

    /** @test */
    public function unauthenticated_user_cannot_access_attendance()
    {
        $this->get('/attendances')->assertRedirect('/login');
    }

    // ── 9. STUDENT CAN VIEW OWN ATTENDANCE ───────────────────────────────

    /** @test */
    public function student_can_view_own_attendance()
    {
        $response = $this->actingAs($this->student)
            ->get('/students/view/attendance/' . $this->student->id);
        $response->assertStatus(200);
    }

    // ── 10. STUDENT CANNOT VIEW ANOTHER STUDENT'S ATTENDANCE ─────────────

    /** @test */
    public function student_cannot_view_another_students_attendance()
    {
        $other = User::factory()->create(['role' => 'student']);
        $other->assignRole('student');
        $other->givePermissionTo(['view attendances']);

        $response = $this->actingAs($this->student)
            ->get('/students/view/attendance/' . $other->id);
        $response->assertStatus(404);
    }
}
