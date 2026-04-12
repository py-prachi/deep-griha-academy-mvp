<?php

namespace App\Repositories;

use App\Models\Admission;
use App\Models\User;
use App\Models\AdmissionDocument;
use App\Interfaces\AdmissionInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdmissionRepository implements AdmissionInterface
{
    // Get all admissions with optional filters
    public function getAll($filters = [])
    {
        $query = Admission::with(['schoolClass', 'section', 'session'])
                          ->whereNotIn('status', ['cancelled', 'exited', 'graduated']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }

        if (!empty($filters['search'])) {
            $query->where('student_name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate(25);
    }

    // Find a single admission by ID
    public function findById($id)
    {
        return Admission::with([
            'schoolClass',
            'section',
            'session',
            'documents',
            'student'
        ])->findOrFail($id);
    }

    // Create new admission (starts as inquiry)
    public function create($data)
    {
        DB::beginTransaction();
        try {
            $admission = Admission::create([
                'status'                  => Admission::STATUS_INQUIRY,
                'student_name'            => $data['student_name'],
                'date_of_birth'           => $data['date_of_birth'] ?? null,
                'gender'                  => $data['gender'] ?? null,
                'caste'                   => $data['caste'] ?? null,
                'religion'                => $data['religion'] ?? null,
                'nationality'             => $data['nationality'] ?? 'Indian',
                'place_of_birth'          => $data['place_of_birth'] ?? null,
                'language_spoken_at_home' => $data['language_spoken_at_home'] ?? null,
                'father_name'             => $data['father_name'] ?? null,
                'father_occupation'       => $data['father_occupation'] ?? null,
                'mother_name'             => $data['mother_name'] ?? null,
                'mother_occupation'       => $data['mother_occupation'] ?? null,
                'full_address'            => $data['full_address'] ?? null,
                'village'                 => $data['village'] ?? null,
                'distance_from_school'    => $data['distance_from_school'] ?? null,
                'contact_residence'       => $data['contact_residence'] ?? null,
                'contact_mobile'          => $data['contact_mobile'] ?? null,
                'contact_emergency'       => $data['contact_emergency'] ?? null,
                'father_phone'            => $data['father_phone'] ?? null,
                'mother_phone'            => $data['mother_phone'] ?? null,
                'city'                    => $data['city'] ?? null,
                'zip'                     => $data['zip'] ?? null,
                'guardian_name'           => $data['guardian_name'] ?? null,
                'guardian_occupation'     => $data['guardian_occupation'] ?? null,
                'guardian_address'        => $data['guardian_address'] ?? null,
                'sibling_name_age'        => $data['sibling_name_age'] ?? null,
                'transport_required'      => $data['transport_required'] ?? false,
                'allergies_medical'       => $data['allergies_medical'] ?? null,
                'blood_type'              => $data['blood_type'] ?? null,
                'doctor_name_phone'       => $data['doctor_name_phone'] ?? null,
                'previous_school'         => $data['previous_school'] ?? null,
                'session_id'              => $data['session_id'] ?? null,
                'class_id'                => $data['class_id'] ?? null,
                'section_id'              => $data['section_id'] ?? null,
                'academic_year'           => $data['academic_year'],
                'fee_category'            => $data['fee_category'] ?? null,
                'discounted_amount'       => $data['discounted_amount'] ?? null,
                'inquiry_date'            => now()->toDateString(),
            ]);

            // Create document checklist entries for this admission
            $this->createDocumentChecklist($admission);

            DB::commit();
            return $admission;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Update admission details
    public function update($id, $data)
    {
        $admission = Admission::findOrFail($id);
        $admission->update($data);
        return $admission;
    }

    // Update just the status
    public function updateStatus($id, $status, $reason = null)
    {
        $admission = Admission::findOrFail($id);
        $admission->status = $status;
        if ($reason) {
            $admission->cancel_reason = $reason;
        }
        $admission->save();
        return $admission;
    }

    // Confirm admission — creates student user account
    public function confirm($id, $data)
    {
        DB::beginTransaction();
        try {
            $admission = Admission::findOrFail($id);

            // Generate admission number for pre-primary
            $prePrimary = ['Nursery', 'Lower KG', 'Upper KG'];
            $className  = $admission->schoolClass ? $admission->schoolClass->class_name : '';

            if (in_array($className, $prePrimary)) {
                $admission->dga_admission_no = Admission::generateDgaAdmissionNo($admission->academic_year);
            } else {
                // General ID entered manually by admin
                $admission->general_id = $data['general_id'] ?? null;
            }

            // Set fee category
            $admission->fee_category      = $data['fee_category'];
            $admission->discounted_amount = $data['discounted_amount'] ?? null;
            $admission->section_id        = $data['section_id'] ?? $admission->section_id;

            // If already confirmed, bail out early
            if ($admission->status === 'confirmed') {
                throw new \Exception('This admission has already been confirmed.');
            }

            // Create the student user account
            $nameParts = explode(' ', $admission->student_name, 2);
            $student = User::create([
                'first_name'       => $nameParts[0],
                'last_name'        => $nameParts[1] ?? '',
                'email'            => $this->generateStudentEmail($admission->student_name, $id),
                'password'         => Hash::make('dga@student2026'),
                'gender'           => ucfirst($admission->gender ?? 'Male'),
                'nationality'      => $admission->nationality ?? 'Indian',
                'phone'            => $admission->father_phone ?? $admission->contact_mobile ?? '',
                'address'          => $admission->full_address ?? '',
                'address2'         => $admission->village ?? '',
                'city'             => $admission->city ?? '',
                'zip'              => $admission->zip  ?? '',
                'birthday'         => $admission->date_of_birth,
                'religion'         => $admission->religion,
                'role'             => 'student',
                'fee_category'     => $data['fee_category'],
                'dga_admission_no' => $admission->dga_admission_no,
                'general_id'       => $admission->general_id,
                'village'          => $admission->village,
                'distance_from_school' => $admission->distance_from_school,
                'student_status'   => 'active',
                'admission_id'     => $admission->id,
                'blood_type' => $admission->blood_type,
            ]);

            // Assign student role
            $student->assignRole('student');

            // Create promotion record so student appears in existing student list
            $promotionRepository = new \App\Repositories\PromotionRepository();
            $promotionRepository->assignClassSection([
                'session_id'    => $admission->session_id,
                'class_id'      => $admission->class_id,
                'section_id'    => $data['section_id'],
                'id_card_number' => $admission->dga_admission_no ?? $admission->general_id ?? '',
            ], $student->id);

            // Link student back to admission
            $admission->student_user_id = $student->id;
            $admission->status          = Admission::STATUS_CONFIRMED;
            $admission->confirmed_date  = now()->toDateString();
            $admission->save();

            DB::commit();
            return $admission;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Cancel admission — soft deletes the record
    public function cancel($id, $reason)
    {
        DB::beginTransaction();
        try {
            $admission = Admission::findOrFail($id);
            $admission->status        = Admission::STATUS_CANCELLED;
            $admission->cancel_reason = $reason;
            $admission->save();
            $admission->delete(); // soft delete
            DB::commit();
            return $admission;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Get cancelled admissions (soft deleted)
    public function getCancelled()
    {
        return Admission::onlyTrashed()
                        ->where('status', 'cancelled')
                        ->with(['schoolClass'])
                        ->orderBy('deleted_at', 'desc')
                        ->get();
    }

    // Get admissions by status
    public function getByStatus($status)
    {
        return Admission::where('status', $status)
                        ->with(['schoolClass', 'section'])
                        ->orderBy('created_at', 'desc')
                        ->get();
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────

    // Create document checklist for new admission
    private function createDocumentChecklist(Admission $admission)
    {
        $documents = [
            'birth_certificate' => 'pending',
            'aadhaar_card'      => 'pending',
            'passport_photos'   => 'pending',
            'previous_school_lc'=> 'pending',
            'caste_certificate' => 'pending',
            'rte_documents'     => 'pending',
        ];

        foreach ($documents as $type => $status) {
            AdmissionDocument::create([
                'admission_id'  => $admission->id,
                'document_type' => $type,
                'status'        => $status,
            ]);
        }
    }

    // Generate a unique email for student: firstname.lastname@deepgriha.com
    // If taken, append 2, 3, … before @
    private function generateStudentEmail($name, $id)
    {
        $slug = strtolower(str_replace(' ', '.', trim($name)));
        $slug = preg_replace('/[^a-z0-9.]/', '', $slug);
        $base = $slug . '@deepgriha.com';
        if (!\App\Models\User::where('email', $base)->exists()) {
            return $base;
        }
        $counter = 2;
        while (\App\Models\User::where('email', $slug . $counter . '@deepgriha.com')->exists()) {
            $counter++;
        }
        return $slug . $counter . '@deepgriha.com';
    }
}
