<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['create users', 'view users', 'view academic settings'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        Role::findByName('admin', 'web')->syncPermissions(Permission::all());

        // Left menu requires at least one school session to exist
        DB::table('school_sessions')->insert([
            'session_name' => '2025-2026', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->admin = User::factory()->create(['role' => 'admin', 'password' => Hash::make('password')]);
        $this->admin->assignRole('admin');
    }

    // ── 1. ADD TEACHER FORM LOADS ─────────────────────────────────────────

    /** @test */
    public function admin_can_view_add_teacher_form()
    {
        $response = $this->actingAs($this->admin)->get('/teachers/add');
        $response->assertStatus(200);
        $response->assertViewIs('teachers.add');
    }

    // ── 2. TEACHER IS CREATED WITH AUTO-CREDENTIALS ───────────────────────

    /** @test */
    public function admin_can_create_teacher_with_auto_generated_credentials()
    {
        $response = $this->actingAs($this->admin)->post('/school/teacher/create', [
            'first_name'  => 'Meera',
            'last_name'   => 'Desai',
            'gender'      => 'Female',
            'phone'       => '9876543210',
            'nationality' => 'Indian',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // Teacher user exists in DB
        $this->assertDatabaseHas('users', [
            'first_name' => 'Meera',
            'last_name'  => 'Desai',
            'role'       => 'teacher',
        ]);

        // Auto-generated email format
        $teacher = User::where('first_name', 'Meera')->where('role', 'teacher')->first();
        $this->assertNotNull($teacher);
        $this->assertStringContainsString('@deepgriha.com', $teacher->email);
        $this->assertEquals('meera.desai@deepgriha.com', $teacher->email);
    }

    /** @test */
    public function duplicate_teacher_name_gets_unique_email()
    {
        // Create first teacher
        $this->actingAs($this->admin)->post('/school/teacher/create', [
            'first_name' => 'Meera', 'last_name' => 'Desai',
            'gender' => 'Female', 'phone' => '9876543210',
        ]);

        // Create second teacher with same name
        $this->actingAs($this->admin)->post('/school/teacher/create', [
            'first_name' => 'Meera', 'last_name' => 'Desai',
            'gender' => 'Female', 'phone' => '9876543211',
        ]);

        $teachers = User::where('first_name', 'Meera')->where('role', 'teacher')->get();
        $this->assertEquals(2, $teachers->count());

        // Both should have unique emails
        $emails = $teachers->pluck('email')->toArray();
        $this->assertEquals(2, count(array_unique($emails)));
    }

    // ── 3. VALIDATION ─────────────────────────────────────────────────────

    /** @test */
    public function teacher_creation_requires_first_name()
    {
        $response = $this->actingAs($this->admin)->post('/school/teacher/create', [
            'last_name' => 'Desai',
            'gender'    => 'Female',
            'phone'     => '9876543210',
        ]);
        $response->assertSessionHasErrors('first_name');
    }

    /** @test */
    public function teacher_creation_requires_phone()
    {
        $response = $this->actingAs($this->admin)->post('/school/teacher/create', [
            'first_name' => 'Meera',
            'last_name'  => 'Desai',
            'gender'     => 'Female',
        ]);
        $response->assertSessionHasErrors('phone');
    }

    // ── 4. SUCCESS MESSAGE SHOWS CREDENTIALS ─────────────────────────────

    /** @test */
    public function success_message_contains_generated_email_and_password()
    {
        $response = $this->actingAs($this->admin)->post('/school/teacher/create', [
            'first_name' => 'Priya', 'last_name' => 'Sharma',
            'gender' => 'Female', 'phone' => '9876500000',
        ]);

        $status = session('status');
        $this->assertStringContainsString('priya.sharma@deepgriha.com', $status);
        $this->assertStringContainsString('dga@teacher2026', $status);
    }

    // ── 5. TEACHER LIST IS ACCESSIBLE TO ADMIN ───────────────────────────

    /** @test */
    public function admin_can_view_teacher_list()
    {
        $response = $this->actingAs($this->admin)->get('/teachers/view/list');
        $response->assertStatus(200);
    }

    // ── 6. NON-ADMIN CANNOT CREATE TEACHER ───────────────────────────────

    /** @test */
    public function teacher_role_cannot_create_another_teacher()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $teacher->assignRole('teacher');

        $response = $this->actingAs($teacher)->post('/school/teacher/create', [
            'first_name' => 'New', 'last_name' => 'Teacher',
            'gender' => 'Male', 'phone' => '9999999999',
        ]);

        $response->assertStatus(403);
    }
}
