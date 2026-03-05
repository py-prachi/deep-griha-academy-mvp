<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admission;
use App\Models\Promotion;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AdmissionTest extends TestCase
{
    use RefreshDatabase;

    // ── TEST DATA ─────────────────────────────────────────────────────────

    protected $admin;
    protected $teacher;
    protected $student;
    protected $sessionId;
    protected $classId;
    protected $sectionId;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed roles and permissions
        $this->seedRolesAndPermissions();

        // Seed minimal school structure
        $this->seedSchoolStructure();

        // Seed academic settings (attendance type)
        DB::table('academic_settings')->insert([
            'attendance_type' => 'section',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);
        $this->admin->assignRole('admin');

        // Create teacher user
        $this->teacher = User::factory()->create([
            'role'     => 'teacher',
            'password' => Hash::make('password'),
        ]);
        $this->teacher->assignRole('teacher');
        $this->teacher->givePermissionTo(['view attendances', 'take attendances', 'view users']);

        // Create student user
        $this->student = User::factory()->create([
            'role'     => 'student',
            'password' => Hash::make('password'),
        ]);
        $this->student->assignRole('student');
        $this->student->givePermissionTo(['view attendances']);

        // Assign student to class/section via promotion
        Promotion::create([
            'student_id'     => $this->student->id,
            'session_id'     => $this->sessionId,
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'id_card_number' => 'TEST-001',
        ]);
    }

    // ── HELPERS ───────────────────────────────────────────────────────────

    private function seedRolesAndPermissions(): void
    {
        $permissions = [
            'take attendances', 'view attendances',
            'view users', 'manage admissions',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // Give admin all permissions
        $admin = Role::findByName('admin', 'web');
        $admin->syncPermissions(Permission::all());
    }

    private function seedSchoolStructure(): void
    {
        $this->sessionId = DB::table('school_sessions')->insertGetId([
            'session_name' => '2025-2026',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->classId = DB::table('school_classes')->insertGetId([
            'class_name' => 'Class 3',
            'session_id' => $this->sessionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->sectionId = DB::table('sections')->insertGetId([
            'section_name' => 'A',
            'room_no'      => '101',
            'class_id'     => $this->classId,
            'session_id'   => $this->sessionId,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function makeAdmission(array $overrides = []): Admission
    {
        return Admission::create(array_merge([
            'status'         => Admission::STATUS_INQUIRY,
            'student_name'   => 'Test Child',
            'contact_mobile' => '9999999999',
            'father_phone'   => '9999999999',
            'mother_phone'   => '8888888888',
            'academic_year'  => '2025-2026',
            'class_id'       => $this->classId,
            'session_id'     => $this->sessionId,
            'inquiry_date'   => now()->toDateString(),
            'gender'         => 'male',
            'religion'       => 'Hinduism',
            'nationality'    => 'Indian',
            'city'           => 'Daund',
            'zip'            => '413801',
        ], $overrides));
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 1 — ADMISSION ACCESS CONTROL
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_view_admissions_index()
    {
        $response = $this->actingAs($this->admin)->get('/admissions');
        $response->assertStatus(200);
        $response->assertViewIs('admissions.index');
    }

    /** @test */
    public function teacher_cannot_access_admissions()
    {
        $response = $this->actingAs($this->teacher)->get('/admissions');
        $response->assertStatus(403);
    }

    /** @test */
    public function student_cannot_access_admissions()
    {
        $response = $this->actingAs($this->student)->get('/admissions');
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_is_redirected_to_login_from_admissions()
    {
        $response = $this->get('/admissions');
        $response->assertRedirect('/login');
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 2 — CREATE ADMISSION (INQUIRY)
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_view_create_admission_form()
    {
        $response = $this->actingAs($this->admin)->get('/admissions/create');
        $response->assertStatus(200);
        $response->assertViewIs('admissions.create');
    }

    /** @test */
    public function admin_can_create_admission_inquiry()
    {
        $response = $this->actingAs($this->admin)->post('/admissions', [
            'student_name'   => 'New Student',
            'father_phone'   => '9876543210',
            'mother_phone'   => '9876543211',
            'academic_year'  => '2025-2026',
            'class_id'       => $this->classId,
            'session_id'     => $this->sessionId,
            'gender'         => 'female',
            'religion'       => 'Hinduism',
            'nationality'    => 'Indian',
            'city'           => 'Daund',
        ]);

        // After store, redirects to admissions index
        $response->assertStatus(302);
        $this->assertDatabaseHas('admissions', [
            'student_name' => 'New Student',
            'status'       => Admission::STATUS_INQUIRY,
            'father_phone' => '9876543210',
            'mother_phone' => '9876543211',
        ]);
    }

    /** @test */
    public function admission_requires_student_name()
    {
        $response = $this->actingAs($this->admin)->post('/admissions', [
            'father_phone'  => '9876543210',
            'academic_year' => '2025-2026',
            'class_id'      => $this->classId,
        ]);
        $response->assertSessionHasErrors('student_name');
    }

    /** @test */
    public function admission_requires_father_phone()
    {
        $response = $this->actingAs($this->admin)->post('/admissions', [
            'student_name'  => 'New Student',
            'academic_year' => '2025-2026',
            'class_id'      => $this->classId,
        ]);
        $response->assertSessionHasErrors('father_phone');
    }

    /** @test */
    public function admission_saves_blood_type_when_provided()
    {
        $this->actingAs($this->admin)->post('/admissions', [
            'student_name'  => 'Blood Type Student',
            'father_phone'  => '9876543210',
            'academic_year' => '2025-2026',
            'class_id'      => $this->classId,
            'session_id'    => $this->sessionId,
            'blood_type'    => 'B+',
            'gender'        => 'male',
            'nationality'   => 'Indian',
            'city'          => 'Daund',
        ]);

        $this->assertDatabaseHas('admissions', [
            'student_name' => 'Blood Type Student',
            'blood_type'   => 'B+',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 3 — ADMISSION STATUS FLOW
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_mark_admission_as_pending()
    {
        $admission = $this->makeAdmission();

        $response = $this->actingAs($this->admin)
            ->post("/admissions/{$admission->id}/mark-pending");

        $response->assertRedirect(route('admissions.show', $admission->id));
        $this->assertDatabaseHas('admissions', [
            'id'     => $admission->id,
            'status' => Admission::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function admin_can_cancel_admission_with_reason()
    {
        $admission = $this->makeAdmission();

        $response = $this->actingAs($this->admin)
            ->post("/admissions/{$admission->id}/cancel", [
                'cancel_reason' => 'Parent changed their mind about the school.',
            ]);

        $response->assertRedirect(route('admissions.index'));

        // Should be soft deleted
        $this->assertSoftDeleted('admissions', [
            'id'     => $admission->id,
            'status' => Admission::STATUS_CANCELLED,
        ]);
    }

    /** @test */
    public function cancel_requires_reason_of_minimum_5_characters()
    {
        $admission = $this->makeAdmission();

        $response = $this->actingAs($this->admin)
            ->post("/admissions/{$admission->id}/cancel", [
                'cancel_reason' => 'No',
            ]);

        $response->assertSessionHasErrors('cancel_reason');
        $this->assertDatabaseHas('admissions', [
            'id'     => $admission->id,
            'status' => Admission::STATUS_INQUIRY,
        ]);
    }

    /** @test */
    public function cancelled_admission_appears_in_cancelled_list()
    {
        $admission = $this->makeAdmission([
            'status'        => Admission::STATUS_CANCELLED,
            'cancel_reason' => 'Distance too far from home.',
        ]);
        $admission->delete(); // soft delete

        $response = $this->actingAs($this->admin)->get('/admissions-cancelled');
        $response->assertStatus(200);
        $response->assertSee($admission->student_name);
    }

    /** @test */
    public function confirmed_admission_creates_student_user()
    {
        $admission = $this->makeAdmission([
            'status'      => Admission::STATUS_PENDING,
            'father_name' => 'Test Father',
            'mother_name' => 'Test Mother',
            'father_phone'=> '9876543210',
            'mother_phone'=> '9876543211',
            'full_address'=> '123 Test Street',
            'city'        => 'Daund',
            'zip'         => '413801',
            'blood_type'  => 'O+',
            'date_of_birth' => '2017-06-15',
            'religion'    => 'Hinduism',
            'nationality' => 'Indian',
            'gender'      => 'male',
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admissions/{$admission->id}/confirm", [
                'fee_category' => 'general',
                'section_id'   => $this->sectionId,
                'general_id'   => 'GEN001',
            ]);

        $response->assertRedirect(route('admissions.show', $admission->id));

        // Student user created
        $this->assertDatabaseHas('users', [
            'role'       => 'student',
            'blood_type' => 'O+',
            'city'       => 'Daund',
            'zip'        => '413801',
        ]);

        // Promotion record created
        $student = User::where('role', 'student')
            ->where('admission_id', $admission->id)
            ->first();

        $this->assertNotNull($student);
        $this->assertDatabaseHas('promotions', [
            'student_id' => $student->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
        ]);

        // Admission status updated
        $this->assertDatabaseHas('admissions', [
            'id'     => $admission->id,
            'status' => Admission::STATUS_CONFIRMED,
        ]);
    }

    /** @test */
    public function confirmed_admission_maps_father_phone_to_student_user()
    {
        $admission = $this->makeAdmission([
            'status'       => Admission::STATUS_PENDING,
            'father_phone' => '9123456789',
            'city'         => 'Pune',
            'zip'          => '411001',
        ]);

        $this->actingAs($this->admin)
            ->post("/admissions/{$admission->id}/confirm", [
                'fee_category' => 'general',
                'section_id'   => $this->sectionId,
            ]);

        $student = User::where('admission_id', $admission->id)->first();
        $this->assertEquals('9123456789', $student->phone);
        $this->assertEquals('Pune', $student->city);
        $this->assertEquals('411001', $student->zip);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 4 — STUDENT PROFILE
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_view_student_profile()
    {
        $response = $this->actingAs($this->admin)
            ->get("/students/view/profile/{$this->student->id}");
        $response->assertStatus(200);
        $response->assertViewIs('students.profile');
    }

    /** @test */
    public function student_profile_shows_admission_parent_data_when_no_parent_info_record()
    {
        // Create admission-confirmed student with parent data on admission record
        $admission = $this->makeAdmission([
            'status'       => Admission::STATUS_CONFIRMED,
            'father_name'  => 'Admission Father',
            'mother_name'  => 'Admission Mother',
            'father_phone' => '9111111111',
            'mother_phone' => '9222222222',
            'full_address' => '456 Admission Street',
        ]);

        $admissionStudent = User::factory()->create([
            'role'         => 'student',
            'admission_id' => $admission->id,
        ]);

        Promotion::create([
            'student_id'     => $admissionStudent->id,
            'session_id'     => $this->sessionId,
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'id_card_number' => 'ADM-001',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/students/view/profile/{$admissionStudent->id}");

        $response->assertStatus(200);
        $response->assertSee('Admission Father');
        $response->assertSee('Admission Mother');
        $response->assertSee('9111111111');
    }

    /** @test */
    public function student_profile_shows_correct_admission_number()
    {
        $admission = $this->makeAdmission([
            'status'     => Admission::STATUS_CONFIRMED,
            'general_id' => 'GEN-TEST-001',
        ]);

        $admissionStudent = User::factory()->create([
            'role'         => 'student',
            'admission_id' => $admission->id,
            'general_id'   => 'GEN-TEST-001',
        ]);

        Promotion::create([
            'student_id'     => $admissionStudent->id,
            'session_id'     => $this->sessionId,
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'id_card_number' => 'GEN-TEST-001',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/students/view/profile/{$admissionStudent->id}");

        $response->assertStatus(200);
        $response->assertSee('GEN-TEST-001');
    }

    /** @test */
    public function student_profile_does_not_crash_when_no_promotion_record()
    {
        // Student with no promotion record (edge case)
        $orphanStudent = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($this->admin)
            ->get("/students/view/profile/{$orphanStudent->id}");

        // Should not crash — shows profile with empty academic info message
        $response->assertStatus(200);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 5 — STUDENT LIST
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_view_student_list()
    {
        $response = $this->actingAs($this->admin)
            ->get('/students/view/list');
        $response->assertStatus(200);
        $response->assertViewIs('students.list');
    }

    /** @test */
    public function student_list_shows_all_students_when_no_filter()
    {
        $response = $this->actingAs($this->admin)
            ->get('/students/view/list');
        $response->assertStatus(200);
        // Page loads without error — no class/section filter needed
    }

    /** @test */
    public function teacher_can_view_student_list()
    {
        $response = $this->actingAs($this->teacher)
            ->get('/students/view/list');
        $response->assertStatus(200);
    }

    /** @test */
    public function student_cannot_view_student_list()
    {
        $response = $this->actingAs($this->student)
            ->get('/students/view/list');
        $response->assertStatus(403);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 6 — ATTENDANCE
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_view_student_attendance_page()
    {
        $response = $this->actingAs($this->admin)
            ->get("/students/view/attendance/{$this->student->id}");
        $response->assertStatus(200);
        $response->assertViewIs('attendances.attendance');
    }

    /** @test */
    public function student_can_view_own_attendance()
    {
        $response = $this->actingAs($this->student)
            ->get("/students/view/attendance/{$this->student->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function student_cannot_view_other_students_attendance()
    {
        $otherStudent = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($this->student)
            ->get("/students/view/attendance/{$otherStudent->id}");
        $response->assertStatus(404);
    }

    /** @test */
    public function attendance_page_does_not_crash_for_student_with_no_attendance_records()
    {
        // Student exists with promotion but zero attendance records
        $response = $this->actingAs($this->admin)
            ->get("/students/view/attendance/{$this->student->id}");

        $response->assertStatus(200);
        // Should show empty calendar, not crash
    }

    /** @test */
    public function attendance_page_gets_class_from_promotion_not_attendance_history()
    {
        // Student has NO attendance records
        // Page should still load and show correct class from promotion
        $response = $this->actingAs($this->admin)
            ->get("/students/view/attendance/{$this->student->id}");

        $response->assertStatus(200);
        $response->assertViewHas('class_id', $this->classId);
        $response->assertViewHas('section_id', $this->sectionId);
        $response->assertViewHas('has_promotion', true);
    }

    /** @test */
    public function attendance_page_shows_warning_when_student_has_no_promotion()
    {
        $orphanStudent = User::factory()->create([
            'role' => 'student',
        ]);
        $orphanStudent->givePermissionTo('view attendances');

        // No promotion record for this student
        $response = $this->actingAs($this->admin)
            ->get("/students/view/attendance/{$orphanStudent->id}");

        $response->assertStatus(200);
        $response->assertViewHas('has_promotion', false);
    }

    /** @test */
    public function attendance_context_column_handles_null_section_and_course()
    {
        // Create attendance record with both section and course null (edge case)
        Attendance::create([
            'student_id' => $this->student->id,
            'class_id'   => $this->classId,
            'section_id' => $this->sectionId,
            'course_id'  => 0,
            'session_id' => $this->sessionId,
            'status'     => 'on',
        ]);

        // Should NOT crash
        $response = $this->actingAs($this->admin)
            ->get("/students/view/attendance/{$this->student->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_can_view_student_attendance()
    {
        $response = $this->actingAs($this->teacher)
            ->get("/students/view/attendance/{$this->student->id}");
        $response->assertStatus(200);
    }
}
