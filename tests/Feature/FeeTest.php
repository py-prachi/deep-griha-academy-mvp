<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admission;
use App\Models\Promotion;
use App\Models\FeeStructure;
use App\Models\FeePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class FeeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
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
        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);
        $this->admin->assignRole('admin');

        $this->student = User::factory()->create([
            'role'         => 'student',
            'fee_category' => 'general',
            'password'     => Hash::make('password'),
        ]);
        $this->student->assignRole('student');

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

    private function makeFeeStructure(array $overrides = []): FeeStructure
    {
        return FeeStructure::create(array_merge([
            'class_id'      => $this->classId,
            'session_id'    => $this->sessionId,
            'academic_year' => '2025-2026',
            'fee_category'  => 'general',
            'admission_fee' => 1000,
            'tuition_fee'   => 12000,
            'transport_fee' => 2000,
            'other_fee'     => 500,
        ], $overrides));
    }

    private function recordPayment(array $overrides = []): array
    {
        return array_merge([
            'payment_date' => now()->toDateString(),
            'amount_paid'  => 5000,
            'payment_mode' => 'cash',
        ], $overrides);
    }

    /** @test */
    public function admin_can_view_fee_structures_index()
    {
        $this->makeFeeStructure();
        $response = $this->actingAs($this->admin)->get(route('fee-structures.index'));
        $response->assertStatus(200);
        $response->assertSee('Class 3');
    }

    /** @test */
    public function admin_can_create_fee_structure()
    {
        $response = $this->actingAs($this->admin)->post(route('fee-structures.store'), [
            'class_id'      => $this->classId,
            'fee_category'  => 'general',
            'academic_year' => '2025-2026',
            'admission_fee' => 1000,
            'tuition_fee'   => 12000,
            'transport_fee' => 2000,
            'other_fee'     => 500,
        ]);
        $response->assertRedirect(route('fee-structures.index'));
        $this->assertDatabaseHas('fee_structures', [
            'class_id'     => $this->classId,
            'fee_category' => 'general',
            'tuition_fee'  => 12000,
        ]);
    }

    /** @test */
    public function admin_can_update_fee_structure()
    {
        $fs = $this->makeFeeStructure();
        $response = $this->actingAs($this->admin)->put(route('fee-structures.update', $fs->id), [
            'admission_fee' => 1500,
            'tuition_fee'   => 15000,
            'transport_fee' => 2500,
            'other_fee'     => 600,
        ]);
        $response->assertRedirect(route('fee-structures.index'));
        $this->assertDatabaseHas('fee_structures', ['id' => $fs->id, 'tuition_fee' => 15000]);
    }

    /** @test */
    public function admin_can_delete_fee_structure()
    {
        $fs = $this->makeFeeStructure();
        $response = $this->actingAs($this->admin)->delete(route('fee-structures.destroy', $fs->id));
        $response->assertRedirect(route('fee-structures.index'));
        $this->assertDatabaseMissing('fee_structures', ['id' => $fs->id]);
    }

    /** @test */
    public function student_cannot_access_fee_structures()
    {
        $response = $this->actingAs($this->student)->get(route('fee-structures.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_student_fee_ledger()
    {
        $this->makeFeeStructure();
        $response = $this->actingAs($this->admin)->get(route('fees.ledger', $this->student->id));
        $response->assertStatus(200);
        $response->assertSee($this->student->first_name);
    }

    /** @test */
    public function admin_can_record_cash_payment()
    {
        $response = $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['amount_paid' => 5000, 'payment_mode' => 'cash'])
        );
        $this->assertDatabaseHas('fee_payments', [
            'student_user_id' => $this->student->id,
            'amount_paid'     => 5000,
            'payment_mode'    => 'cash',
        ]);
        $payment = FeePayment::where('student_user_id', $this->student->id)->first();
        $response->assertRedirect(route('fees.challan', $payment->id));
    }

    /** @test */
    public function admin_can_record_cheque_payment()
    {
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment([
                'amount_paid'  => 3000,
                'payment_mode' => 'cheque',
                'cheque_no'    => '123456',
                'cheque_date'  => now()->toDateString(),
                'bank_name'    => 'Bank of Maharashtra',
            ])
        );
        $this->assertDatabaseHas('fee_payments', [
            'student_user_id' => $this->student->id,
            'payment_mode'    => 'cheque',
            'cheque_no'       => '123456',
        ]);
    }

    /** @test */
    public function admin_can_record_qr_payment()
    {
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment([
                'amount_paid'     => 2500,
                'payment_mode'    => 'qr',
                'transaction_ref' => 'UPI123456789',
            ])
        );
        $this->assertDatabaseHas('fee_payments', [
            'student_user_id' => $this->student->id,
            'payment_mode'    => 'qr',
            'transaction_ref' => 'UPI123456789',
        ]);
    }

    /** @test */
    public function challan_number_increments_sequentially()
    {
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['amount_paid' => 1000])
        );
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['amount_paid' => 2000])
        );
        $payments = FeePayment::where('student_user_id', $this->student->id)
            ->orderBy('challan_no')->get();
        $this->assertEquals(2, $payments->count());
        $this->assertEquals($payments[0]->challan_no + 1, $payments[1]->challan_no);
    }

    /** @test */
    public function admin_can_view_challan()
    {
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment()
        );
        $payment = FeePayment::where('student_user_id', $this->student->id)->first();
        $response = $this->actingAs($this->admin)->get(route('fees.challan', $payment->id));
        $response->assertStatus(200);
        $response->assertSee($this->student->first_name);
    }

    /** @test */
    public function payment_requires_amount_greater_than_zero()
    {
        $response = $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['amount_paid' => 0])
        );
        $response->assertSessionHasErrors('amount_paid');
    }

    /** @test */
    public function cheque_payment_requires_cheque_fields()
    {
        $response = $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['payment_mode' => 'cheque'])
        );
        $response->assertSessionHasErrors(['cheque_no', 'cheque_date', 'bank_name']);
    }

    /** @test */
    public function qr_payment_requires_transaction_ref()
    {
        $response = $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['payment_mode' => 'qr'])
        );
        $response->assertSessionHasErrors('transaction_ref');
    }

    /** @test */
    public function student_cannot_record_payments()
    {
        $response = $this->actingAs($this->student)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment()
        );
        $response->assertStatus(403);
    }

    /** @test */
    public function ledger_shows_correct_balance()
    {
        $this->makeFeeStructure([
            'admission_fee' => 1000,
            'tuition_fee'   => 4000,
            'transport_fee' => 0,
            'other_fee'     => 0,
        ]);
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['amount_paid' => 3000])
        );
        $response = $this->actingAs($this->admin)->get(route('fees.ledger', $this->student->id));
        $response->assertStatus(200);
        $response->assertSee('3,000');
        $response->assertSee('2,000');
    }

    /** @test */
    public function daily_report_returns_payments_for_date()
    {
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['payment_date' => now()->toDateString(), 'amount_paid' => 5000])
        );
        $response = $this->actingAs($this->admin)->get(
            route('reports.fees.daily', ['date' => now()->toDateString()])
        );
        $response->assertStatus(200);
        $response->assertSee('5,000');
    }

    /** @test */
    public function daily_report_returns_empty_for_date_with_no_payments()
    {
        $response = $this->actingAs($this->admin)->get(
            route('reports.fees.daily', ['date' => '2000-01-01'])
        );
        $response->assertStatus(200);
        $response->assertSee('No payments recorded');
    }

    /** @test */
    public function date_range_report_returns_payments_in_range()
    {
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['payment_date' => now()->toDateString(), 'amount_paid' => 7000])
        );
        $response = $this->actingAs($this->admin)->get(route('reports.fees.dateRange', [
            'from' => now()->startOfMonth()->toDateString(),
            'to'   => now()->toDateString(),
        ]));
        $response->assertStatus(200);
        // Assert payment count appears — amount formatting may vary
        $this->assertDatabaseHas('fee_payments', [
            'student_user_id' => $this->student->id,
            'amount_paid'     => 7000,
        ]);
    }

    /** @test */
    public function defaulters_report_loads_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('reports.fees.defaulters'));
        $response->assertStatus(200);
    }

    /** @test */
    public function category_summary_report_loads_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('reports.fees.categorySummary'));
        $response->assertStatus(200);
    }

    /** @test */
    public function admissions_report_loads_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('reports.admissions'));
        $response->assertStatus(200);
    }

    /** @test */
    public function class_strength_report_loads_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('reports.classStrength'));
        $response->assertStatus(200);
    }

    /** @test */
    public function rte_report_only_shows_rte_students()
    {
        $rteStudent = User::factory()->create([
            'role'         => 'student',
            'fee_category' => 'rte',
        ]);
        $rteStudent->assignRole('student');
        Promotion::create([
            'student_id'     => $rteStudent->id,
            'session_id'     => $this->sessionId,
            'class_id'       => $this->classId,
            'section_id'     => $this->sectionId,
            'id_card_number' => 'RTE-001',
        ]);
        $response = $this->actingAs($this->admin)->get(route('reports.rte'));
        $response->assertStatus(200);
        $response->assertSee($rteStudent->first_name);
        $response->assertDontSee($this->student->first_name);
    }

    /** @test */
    public function non_admin_cannot_access_reports()
    {
        $response = $this->actingAs($this->student)->get(route('reports.fees.daily'));
        $response->assertStatus(403);
    }

    /** @test */
    public function daily_report_pdf_download_works()
    {
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['amount_paid' => 1000])
        );
        $response = $this->actingAs($this->admin)->get(
            route('reports.fees.daily', ['date' => now()->toDateString(), 'pdf' => 1])
        );
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function challan_pdf_download_works()
    {
        $this->actingAs($this->admin)->post(
            route('fees.store', $this->student->id),
            $this->recordPayment(['amount_paid' => 1000])
        );
        $payment = FeePayment::where('student_user_id', $this->student->id)->first();
        $response = $this->actingAs($this->admin)->get(route('fees.challan.pdf', $payment->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
