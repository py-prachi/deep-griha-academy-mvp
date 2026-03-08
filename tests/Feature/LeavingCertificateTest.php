<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admission;
use App\Models\LeavingCertificate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class LeavingCertificateTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $sessionId;
    protected $classId;
    protected $sectionId;
    protected Admission $admission;

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

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);
        $this->admin->assignRole('admin');

        $this->admission = $this->makeAdmission([
            'status'       => Admission::STATUS_CONFIRMED,
            'student_name' => 'Rahul Patil',
            'mother_name'  => 'Sunita Patil',
            'date_of_birth'=> '2016-06-15',
            'confirmed_date'=> now()->toDateString(),
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function seedRolesAndPermissions(): void
    {
        $admin   = Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
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
            'status'         => Admission::STATUS_CONFIRMED,
            'student_name'   => 'Test Child',
            'contact_mobile' => '9999999999',
            'father_phone'   => '9999999999',
            'mother_phone'   => '8888888888',
            'academic_year'  => '2025-2026',
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'session_id'     => $this->sessionId,
            'inquiry_date'   => now()->toDateString(),
            'gender'         => 'male',
            'religion'       => 'Hinduism',
            'nationality'    => 'Indian',
            'city'           => 'Daund',
            'zip'            => '413801',
        ], $overrides));
    }

    private function makeLc(array $overrides = []): LeavingCertificate
    {
        return LeavingCertificate::create(array_merge([
            'admission_id'    => $this->admission->id,
            'lc_number'       => LeavingCertificate::generateLcNumber(),
            'issue_date'      => now()->toDateString(),
            'date_of_leaving' => now()->toDateString(),
            'conduct'         => 'Good',
            'fees_cleared'    => true,
            'fees_due'        => 0,
            'pupil_name'      => 'Rahul Patil',
        ], $overrides));
    }

    // ── Index ──────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_lc_index()
    {
        $this->actingAs($this->admin)
            ->get(route('lc.index'))
            ->assertOk()
            ->assertViewIs('lc.index');
    }

    /** @test */
    public function lc_index_shows_existing_certificates()
    {
        $this->makeLc(['lc_number' => 'LC001', 'pupil_name' => 'Rahul Patil']);

        $this->actingAs($this->admin)
            ->get(route('lc.index'))
            ->assertSee('LC001')
            ->assertSee('Rahul Patil');
    }

    /** @test */
    public function lc_index_can_search_by_lc_number()
    {
        $this->makeLc(['lc_number' => 'LC042']);

        $this->actingAs($this->admin)
            ->get(route('lc.index', ['search' => 'LC042']))
            ->assertSee('LC042');
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_lc_create_form()
    {
        $this->actingAs($this->admin)
            ->get(route('lc.create'))
            ->assertOk()
            ->assertViewIs('lc.create');
    }

    /** @test */
    public function lc_create_form_shows_next_lc_number()
    {
        $this->actingAs($this->admin)
            ->get(route('lc.create'))
            ->assertSee('LC001');
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_issue_a_leaving_certificate()
    {
        $this->actingAs($this->admin)
            ->post(route('lc.store'), [
                'admission_id'       => $this->admission->id,
                'issue_date'         => now()->toDateString(),
                'date_of_leaving'    => now()->toDateString(),
                'pupil_name'         => 'Rahul Patil',
                'standard_studying'  => 'Class 3 - A',
                'reason_for_leaving' => 'Transfer',
                'conduct'            => 'Good',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leaving_certificates', [
            'admission_id' => $this->admission->id,
            'lc_number'    => 'LC001',
            'conduct'      => 'Good',
        ]);
    }

    /** @test */
    public function lc_number_auto_increments()
    {
        $this->makeLc(['lc_number' => 'LC001']);
        $this->assertEquals('LC002', LeavingCertificate::generateLcNumber());
    }

    /** @test */
    public function lc_number_pads_to_three_digits()
    {
        $this->makeLc(['lc_number' => 'LC009']);
        $this->assertEquals('LC010', LeavingCertificate::generateLcNumber());
    }

    /** @test */
    public function first_lc_number_is_lc001()
    {
        $this->assertEquals('LC001', LeavingCertificate::generateLcNumber());
    }

    /** @test */
    public function cannot_issue_lc_to_admission_that_already_has_one()
    {
        $this->makeLc(['lc_number' => 'LC001']);

        $this->actingAs($this->admin)
            ->post(route('lc.store'), [
                'admission_id'    => $this->admission->id,
                'issue_date'      => now()->toDateString(),
                'date_of_leaving' => now()->toDateString(),
                'conduct'         => 'Good',
            ])
            ->assertSessionHasErrors('admission_id');
    }

    /** @test */
    public function store_requires_admission_id()
    {
        $this->actingAs($this->admin)
            ->post(route('lc.store'), [
                'issue_date'      => now()->toDateString(),
                'date_of_leaving' => now()->toDateString(),
                'conduct'         => 'Good',
            ])
            ->assertSessionHasErrors('admission_id');
    }

    /** @test */
    public function store_requires_issue_date()
    {
        $this->actingAs($this->admin)
            ->post(route('lc.store'), [
                'admission_id'    => $this->admission->id,
                'date_of_leaving' => now()->toDateString(),
                'conduct'         => 'Good',
            ])
            ->assertSessionHasErrors('issue_date');
    }

    /** @test */
    public function store_requires_date_of_leaving()
    {
        $this->actingAs($this->admin)
            ->post(route('lc.store'), [
                'admission_id' => $this->admission->id,
                'issue_date'   => now()->toDateString(),
                'conduct'      => 'Good',
            ])
            ->assertSessionHasErrors('date_of_leaving');
    }

    /** @test */
    public function store_requires_conduct()
    {
        $this->actingAs($this->admin)
            ->post(route('lc.store'), [
                'admission_id'    => $this->admission->id,
                'issue_date'      => now()->toDateString(),
                'date_of_leaving' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('conduct');
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_lc_show_page()
    {
        $lc = $this->makeLc(['lc_number' => 'LC001', 'pupil_name' => 'Rahul Patil']);

        $this->actingAs($this->admin)
            ->get(route('lc.show', $lc->id))
            ->assertOk()
            ->assertSee('LC001')
            ->assertSee('Rahul Patil');
    }

    /** @test */
    public function show_page_displays_fee_warning_when_fees_due()
    {
        $lc = $this->makeLc([
            'lc_number'    => 'LC001',
            'fees_cleared' => false,
            'fees_due'     => 1500.00,
        ]);

        $this->actingAs($this->admin)
            ->get(route('lc.show', $lc->id))
            ->assertSee('1,500.00');
    }

    /** @test */
    public function lc_issued_records_fees_due_as_zero_when_no_payments()
    {
        $this->actingAs($this->admin)
            ->post(route('lc.store'), [
                'admission_id'    => $this->admission->id,
                'issue_date'      => now()->toDateString(),
                'date_of_leaving' => now()->toDateString(),
                'conduct'         => 'Good',
            ]);

        $this->assertDatabaseHas('leaving_certificates', [
            'admission_id' => $this->admission->id,
            'fees_due'     => 0,
        ]);
    }

    // ── PDF ────────────────────────────────────────────────────────────────────

    /** @test */
    public function pdf_route_returns_pdf_for_existing_lc()
    {
        $lc = $this->makeLc(['lc_number' => 'LC001']);

        $response = $this->actingAs($this->admin)
            ->get(route('lc.pdf', $lc->id));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function pdf_returns_404_for_nonexistent_lc()
    {
        $this->actingAs($this->admin)
            ->get(route('lc.pdf', 99999))
            ->assertNotFound();
    }

    // ── Student Info AJAX ──────────────────────────────────────────────────────

    /** @test */
    public function student_info_endpoint_returns_admission_data()
    {
        $this->actingAs($this->admin)
            ->getJson(route('lc.student-info', ['admission_id' => $this->admission->id]))
            ->assertOk()
            ->assertJsonPath('admission.student_name', 'Rahul Patil')
            ->assertJsonPath('fee_check.has_due', false)
            ->assertJsonPath('has_lc', false);
    }

    /** @test */
    public function student_info_endpoint_flags_existing_lc()
    {
        $this->makeLc(['lc_number' => 'LC001']);

        $this->actingAs($this->admin)
            ->getJson(route('lc.student-info', ['admission_id' => $this->admission->id]))
            ->assertOk()
            ->assertJsonPath('has_lc', true)
            ->assertJsonPath('lc_number', 'LC001');
    }

    /** @test */
    public function student_info_returns_422_for_missing_admission_id()
    {
        $this->actingAs($this->admin)
            ->getJson(route('lc.student-info'))
            ->assertStatus(422);
    }

    /** @test */
    public function student_info_returns_422_for_invalid_admission_id()
    {
        $this->actingAs($this->admin)
            ->getJson(route('lc.student-info', ['admission_id' => 99999]))
            ->assertStatus(422);
    }

    // ── Auth ───────────────────────────────────────────────────────────────────

    /** @test */
    public function unauthenticated_user_cannot_view_lc_index()
    {
        $this->get(route('lc.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function unauthenticated_user_cannot_issue_lc()
    {
        $this->post(route('lc.store'), [])
            ->assertRedirect(route('login'));
    }
}
