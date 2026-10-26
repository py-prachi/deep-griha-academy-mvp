<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admission;
use App\Models\Promotion;
use App\Models\StudentExit;
use App\Models\LeavingCertificate;
use App\Models\FeePayment;
use App\Models\FeeSettlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ExitTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
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

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);
        $this->admin->assignRole('admin');

        $this->teacher = User::factory()->create([
            'role'     => 'teacher',
            'password' => Hash::make('password'),
        ]);
        $this->teacher->assignRole('teacher');
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

    // A fully confirmed admission with a linked student user + promotion, built
    // directly (not through the /admissions/{id}/confirm HTTP flow) so exit tests
    // stay fast and focused on exit behavior rather than re-exercising confirmation.
    private function makeConfirmedAdmission(array $overrides = []): Admission
    {
        $student = User::factory()->create([
            'role'         => 'student',
            'fee_category' => 'general',
        ]);
        $student->assignRole('student');

        $admission = Admission::create(array_merge([
            'status'           => Admission::STATUS_CONFIRMED,
            'student_name'     => $student->first_name . ' ' . $student->last_name,
            'student_user_id'  => $student->id,
            'class_id'         => $this->classId,
            'session_id'       => $this->sessionId,
            'academic_year'    => '2025-2026',
            'inquiry_date'     => now()->toDateString(),
            'confirmed_date'   => now()->toDateString(),
            'contact_mobile'   => '9999999999',
            'father_phone'     => '9999999999',
            'mother_phone'     => '8888888888',
            'gender'           => 'male',
            'religion'         => 'Hinduism',
            'nationality'      => 'Indian',
            'city'             => 'Daund',
            'zip'              => '413801',
        ], $overrides));

        $student->update(['admission_id' => $admission->id]);

        Promotion::create([
            'student_id'     => $student->id,
            'session_id'     => $this->sessionId,
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'id_card_number' => 'TEST-' . $student->id,
        ]);

        return $admission->fresh();
    }

    // Marks an admission exited directly (bypassing the HTTP endpoint) for tests
    // that need an already-exited fixture rather than exercising store() itself.
    private function exitAdmission(Admission $admission, ?string $exitType = StudentExit::TYPE_GENUINE): StudentExit
    {
        $exit = StudentExit::create([
            'admission_id' => $admission->id,
            'exit_type'    => $exitType,
            'exit_date'    => now()->toDateString(),
        ]);

        $admission->status    = Admission::STATUS_EXITED;
        $admission->exit_date = now()->toDateString();
        $admission->save();

        User::where('id', $admission->student_user_id)->update(['student_status' => 'exited']);

        return $exit;
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 1 — EXIT CATEGORIZATION (Genuine / Correction)
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_record_exit_as_genuine()
    {
        $admission = $this->makeConfirmedAdmission();

        $response = $this->actingAs($this->admin)->post(route('exits.store'), [
            'admission_id' => $admission->id,
            'exit_date'    => now()->toDateString(),
            'is_genuine'   => '1',
        ]);

        $exit = StudentExit::where('admission_id', $admission->id)->first();
        $this->assertNotNull($exit);
        $response->assertRedirect(route('exits.show', $exit->id));
        $this->assertEquals(StudentExit::TYPE_GENUINE, $exit->exit_type);
        $this->assertDatabaseHas('admissions', [
            'id'     => $admission->id,
            'status' => Admission::STATUS_EXITED,
        ]);
    }

    /** @test */
    public function admin_can_record_exit_as_correction()
    {
        $admission = $this->makeConfirmedAdmission();

        // is_genuine intentionally omitted — mirrors an unchecked checkbox, which
        // browsers simply never submit.
        $response = $this->actingAs($this->admin)->post(route('exits.store'), [
            'admission_id' => $admission->id,
            'exit_date'    => now()->toDateString(),
        ]);

        $exit = StudentExit::where('admission_id', $admission->id)->first();
        $this->assertNotNull($exit);
        $this->assertEquals(StudentExit::TYPE_CORRECTION, $exit->exit_type);
    }

    /** @test */
    public function cannot_exit_an_admission_that_is_not_confirmed()
    {
        $admission = Admission::create([
            'status'         => Admission::STATUS_PENDING,
            'student_name'   => 'Pending Student',
            'inquiry_date'   => now()->toDateString(),
            'class_id'       => $this->classId,
            'session_id'     => $this->sessionId,
            'academic_year'  => '2025-2026',
            'contact_mobile' => '9999999999',
            'father_phone'   => '9999999999',
            'mother_phone'   => '8888888888',
            'gender'         => 'male',
            'religion'       => 'Hinduism',
            'nationality'    => 'Indian',
            'city'           => 'Daund',
            'zip'            => '413801',
        ]);

        $response = $this->actingAs($this->admin)->post(route('exits.store'), [
            'admission_id' => $admission->id,
            'exit_date'    => now()->toDateString(),
            'is_genuine'   => '1',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function cannot_exit_the_same_admission_twice()
    {
        // The duplicate-exit guard in store() is a defensive check (backed by a DB
        // unique constraint on admission_id) rather than something reachable via two
        // sequential real HTTP calls — the first exit always flips the admission's
        // status away from 'confirmed', so a genuine second POST 404s on the status
        // lookup before ever reaching this guard. Test the guard directly instead,
        // by constructing the state it protects against: an exit record already
        // present while status is still 'confirmed'.
        $admission = $this->makeConfirmedAdmission();
        StudentExit::create([
            'admission_id' => $admission->id,
            'exit_type'    => StudentExit::TYPE_GENUINE,
            'exit_date'    => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('exits.store'), [
            'admission_id' => $admission->id,
            'exit_date'    => now()->toDateString(),
            'is_genuine'   => '1',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, StudentExit::where('admission_id', $admission->id)->count());
    }

    /** @test */
    public function genuine_tab_only_shows_genuine_exits()
    {
        $genuine    = $this->makeConfirmedAdmission(['student_name' => 'Genuine Student']);
        $correction = $this->makeConfirmedAdmission(['student_name' => 'Correction Student']);
        $this->exitAdmission($genuine, StudentExit::TYPE_GENUINE);
        $this->exitAdmission($correction, StudentExit::TYPE_CORRECTION);

        $response = $this->actingAs($this->admin)->get(route('exits.index', ['tab' => 'genuine']));

        $response->assertStatus(200);
        $response->assertSee('Genuine Student');
        $response->assertDontSee('Correction Student');
    }

    /** @test */
    public function uncategorized_tab_shows_legacy_null_type_exits()
    {
        $admission = $this->makeConfirmedAdmission(['student_name' => 'Legacy Student']);
        $this->exitAdmission($admission, null);

        $response = $this->actingAs($this->admin)->get(route('exits.index', ['tab' => 'uncategorized']));

        $response->assertStatus(200);
        $response->assertSee('Legacy Student');
    }

    /** @test */
    public function admin_can_bulk_categorize_uncategorized_exits()
    {
        $admissionA = $this->makeConfirmedAdmission();
        $admissionB = $this->makeConfirmedAdmission();
        $exitA = $this->exitAdmission($admissionA, null);
        $exitB = $this->exitAdmission($admissionB, null);

        $response = $this->actingAs($this->admin)->post(route('exits.categorizeBulk'), [
            'exit_ids' => [$exitA->id, $exitB->id],
            'genuine'  => [$exitA->id], // only A stays checked => genuine; B unchecked => correction
        ]);

        $response->assertRedirect(route('exits.index', ['tab' => 'uncategorized']));
        $this->assertEquals(StudentExit::TYPE_GENUINE, $exitA->fresh()->exit_type);
        $this->assertEquals(StudentExit::TYPE_CORRECTION, $exitB->fresh()->exit_type);
    }

    /** @test */
    public function admin_can_recategorize_a_single_exit()
    {
        $admission = $this->makeConfirmedAdmission();
        $exit = $this->exitAdmission($admission, StudentExit::TYPE_GENUINE);

        $response = $this->actingAs($this->admin)->post(route('exits.categorize', $exit->id), [
            // is_genuine omitted = unchecked = correction
        ]);

        $response->assertRedirect(route('exits.show', $exit->id));
        $this->assertEquals(StudentExit::TYPE_CORRECTION, $exit->fresh()->exit_type);
    }

    /** @test */
    public function teacher_cannot_access_exits()
    {
        $response = $this->actingAs($this->teacher)->get(route('exits.index'));
        $response->assertStatus(403);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 2 — VIEW-ONLY ACCESS FOR EXITED STUDENTS
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_can_view_profile_of_exited_student()
    {
        $admission = $this->makeConfirmedAdmission();
        $this->exitAdmission($admission);

        $response = $this->actingAs($this->admin)
            ->get(route('student.profile.show', $admission->student_user_id));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_fee_ledger_of_exited_student_before_lc()
    {
        $admission = $this->makeConfirmedAdmission();
        $this->exitAdmission($admission);

        $response = $this->actingAs($this->admin)
            ->get(route('fees.ledger', $admission->student_user_id));

        $response->assertStatus(200);
        $response->assertSee('Record Payment');
        $response->assertSee('Dues Still Collectible');
    }

    /** @test */
    public function admin_cannot_edit_exited_admission()
    {
        $admission = $this->makeConfirmedAdmission();
        $this->exitAdmission($admission);

        $editResponse = $this->actingAs($this->admin)->get(route('admissions.edit', $admission->id));
        $editResponse->assertRedirect(route('admissions.show', $admission->id));

        $updateResponse = $this->actingAs($this->admin)->put(route('admissions.update', $admission->id), [
            'general_id' => '99999999999',
        ]);
        $updateResponse->assertRedirect(route('admissions.show', $admission->id));
        $updateResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('admissions', [
            'id'         => $admission->id,
            'general_id' => '99999999999',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SECTION 3 — FEES COLLECTIBLE UNTIL LC IS ISSUED
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function payment_can_be_recorded_for_exited_student_without_lc()
    {
        $admission = $this->makeConfirmedAdmission();
        $this->exitAdmission($admission);

        $response = $this->actingAs($this->admin)->post(route('fees.store', $admission->student_user_id), [
            'payment_date'     => now()->toDateString(),
            'amount_paid'      => 1000,
            'payment_mode'     => 'cash',
            'payment_category' => 'fee',
        ]);

        $this->assertDatabaseHas('fee_payments', [
            'student_user_id' => $admission->student_user_id,
            'amount_paid'     => 1000,
        ]);
        $payment = FeePayment::where('student_user_id', $admission->student_user_id)->first();
        $response->assertRedirect(route('fees.challan', $payment->id));
    }

    /** @test */
    public function payment_blocked_once_lc_issued_for_exited_student()
    {
        $admission = $this->makeConfirmedAdmission();
        $this->exitAdmission($admission);
        LeavingCertificate::create([
            'admission_id'    => $admission->id,
            'lc_number'       => 'TEST-LC-001',
            'issue_date'      => now()->toDateString(),
            'date_of_leaving' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('fees.store', $admission->student_user_id), [
            'payment_date'     => now()->toDateString(),
            'amount_paid'      => 1000,
            'payment_mode'     => 'cash',
            'payment_category' => 'fee',
        ]);

        $response->assertRedirect(route('fees.ledger', $admission->student_user_id));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('fee_payments', [
            'student_user_id' => $admission->student_user_id,
        ]);
    }

    /** @test */
    public function ledger_hides_record_payment_and_shows_locked_badge_after_lc()
    {
        $admission = $this->makeConfirmedAdmission();
        $this->exitAdmission($admission);
        LeavingCertificate::create([
            'admission_id'    => $admission->id,
            'lc_number'       => 'TEST-LC-002',
            'issue_date'      => now()->toDateString(),
            'date_of_leaving' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('fees.ledger', $admission->student_user_id));

        $response->assertStatus(200);
        $response->assertSee('LC Issued');
        $response->assertDontSee('Record Payment');
    }

    /** @test */
    public function rollover_payment_blocked_once_lc_issued()
    {
        $admission = $this->makeConfirmedAdmission();
        $this->exitAdmission($admission);
        LeavingCertificate::create([
            'admission_id'    => $admission->id,
            'lc_number'       => 'TEST-LC-003',
            'issue_date'      => now()->toDateString(),
            'date_of_leaving' => now()->toDateString(),
        ]);
        $settlement = FeeSettlement::create([
            'student_user_id'    => $admission->student_user_id,
            'session_id'         => $this->sessionId,
            'outstanding_amount' => 500,
            'settlement_type'    => 'carried_forward',
            'remark'             => 'test rollover',
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('fees.rollover.store', [$admission->student_user_id, $settlement->id]),
            [
                'payment_date' => now()->toDateString(),
                'amount_paid'  => 500,
                'payment_mode' => 'cash',
            ]
        );

        $response->assertRedirect(route('fees.ledger', $admission->student_user_id));
        $response->assertSessionHas('error');
    }
}
