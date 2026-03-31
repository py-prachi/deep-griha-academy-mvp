<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SchoolSession;
use App\Models\User;
use App\Models\AdmissionDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentImportController extends Controller
{
    // Column order in the template (1-based)
    const COLUMNS = [
        1  => 'student_name',
        2  => 'date_of_birth',
        3  => 'gender',
        4  => 'class_name',
        5  => 'section_name',
        6  => 'fee_category',
        7  => 'father_name',
        8  => 'father_phone',
        9  => 'mother_name',
        10 => 'mother_phone',
        11 => 'contact_mobile',
        12 => 'full_address',
        13 => 'city',
        14 => 'general_id',
        15 => 'dga_admission_no',
        16 => 'blood_type',
        17 => 'religion',
        18 => 'caste',
        19 => 'previous_school',
    ];

    const REQUIRED_FIELDS = ['student_name', 'date_of_birth', 'gender', 'class_name', 'fee_category'];

    const PRE_PRIMARY = ['Nursery', 'Lower KG', 'Upper KG'];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403);
            }
            return $next($request);
        });
    }

    // ── Show upload form ──────────────────────────────────────────────────

    public function showForm()
    {
        return view('import.students');
    }

    // ── Download template .xlsx ───────────────────────────────────────────

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students');

        $headers = [
            'student_name *',
            'date_of_birth * (DD/MM/YYYY)',
            'gender * (male/female)',
            'class_name *',
            'section_name (A/B)',
            'fee_category * (general/rte/coc/discount)',
            'father_name',
            'father_phone',
            'mother_name',
            'mother_phone',
            'contact_mobile',
            'full_address',
            'city',
            'general_id (Class 1+)',
            'dga_admission_no (pre-primary only)',
            'blood_type',
            'religion',
            'caste',
            'previous_school',
        ];

        $requiredCols = [1, 2, 3, 4, 6];

        foreach ($headers as $i => $header) {
            $col = $i + 1;
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($colLetter . '1', $header);

            if (in_array($col, $requiredCols)) {
                $sheet->getStyle($colLetter . '1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0392B']],
                ]);
            } else {
                $sheet->getStyle($colLetter . '1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E4057']],
                ]);
            }
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Example row
        $example = [
            'Rahul Kumar', '15/08/2015', 'male', 'Class 3', 'A', 'general',
            'Suresh Kumar', '9876543210', 'Priya Kumar', '9876543211',
            '', 'Near Temple, Village Road', 'Pune', '12345678901',
            '', 'B+', 'Hindu', 'Brahmin', 'Previous School Name',
        ];
        foreach ($example as $i => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }

        $sheet->getStyle('A2:S2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF5FB']],
        ]);

        $sheet->freezePane('A2');

        // Instructions sheet
        $info = $spreadsheet->createSheet();
        $info->setTitle('Instructions');
        $info->setCellValue('A1', 'INSTRUCTIONS — Deep Griha Academy Student Import');
        $info->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $lines = [
            '',
            'Red header columns are REQUIRED. Blue are optional.',
            '',
            'class_name must exactly match one of:',
            '  Nursery, Lower KG, Upper KG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8',
            '',
            'gender: male or female (lowercase)',
            'fee_category: general, rte, coc, or discount (lowercase)',
            'date_of_birth: DD/MM/YYYY  e.g. 15/08/2015',
            'section_name: A or B — leave blank to auto-assign section A',
            '',
            'general_id: For Class 1 and above — 11-digit ZP portal ID (optional)',
            'dga_admission_no: For Nursery/LKG/UKG — leave blank to auto-generate',
            '',
            'DO NOT change column order or add/remove columns.',
            'Row 2 is an example — replace or delete it before uploading.',
            'Save as .xlsx before uploading.',
        ];

        foreach ($lines as $i => $line) {
            $info->setCellValue('A' . ($i + 2), $line);
        }
        $info->getColumnDimension('A')->setWidth(90);

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="StudentImportTemplate.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ── Preview: validate file, return row-by-row results ────────────────

    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);

        $path = $request->file('file')->getPathname();

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Could not read the Excel file. Upload the correct template (.xlsx).']);
        }

        $rawRows = $sheet->toArray(null, true, true, false);
        array_shift($rawRows); // remove header row

        // Load lookup tables
        $classMap  = SchoolClass::pluck('id', 'class_name')->toArray();
        $allSections = Section::all();
        $session   = SchoolSession::latest('id')->first();

        $parsed = [];

        foreach ($rawRows as $rowIndex => $row) {
            $lineNo = $rowIndex + 2;

            // Skip blank rows
            $allEmpty = true;
            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            // Map columns to fields
            $d = [];
            foreach (self::COLUMNS as $colIdx => $field) {
                $d[$field] = isset($row[$colIdx - 1]) ? trim((string) $row[$colIdx - 1]) : '';
            }

            $errors   = [];
            $warnings = [];

            // Required fields
            foreach (self::REQUIRED_FIELDS as $field) {
                if ($d[$field] === '') {
                    $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required';
                }
            }

            // Date of birth
            if ($d['date_of_birth'] !== '') {
                $dob = \DateTime::createFromFormat('d/m/Y', $d['date_of_birth']);
                if (!$dob) {
                    $errors[] = 'Date of birth must be DD/MM/YYYY (e.g. 15/08/2015)';
                } else {
                    $d['dob_parsed'] = $dob->format('Y-m-d');
                }
            }

            // Gender
            if ($d['gender'] !== '') {
                $d['gender'] = strtolower($d['gender']);
                if (!in_array($d['gender'], ['male', 'female'])) {
                    $errors[] = 'Gender must be "male" or "female"';
                }
            }

            // Fee category
            if ($d['fee_category'] !== '') {
                $d['fee_category'] = strtolower($d['fee_category']);
                if (!in_array($d['fee_category'], ['general', 'rte', 'coc', 'discount'])) {
                    $errors[] = 'Fee category must be general, rte, coc, or discount';
                }
            }

            // Class lookup
            $classId   = null;
            $className = $d['class_name'];
            if ($className !== '') {
                if (!isset($classMap[$className])) {
                    $errors[] = 'Class "' . $className . '" not recognised — check spelling exactly';
                } else {
                    $classId       = $classMap[$className];
                    $d['class_id'] = $classId;
                }
            }

            // Section lookup
            $sectionId = null;
            if ($classId) {
                $sectionName = $d['section_name'] !== '' ? strtoupper($d['section_name']) : 'A';
                $section = $allSections->where('class_id', $classId)
                                       ->where('section_name', $sectionName)
                                       ->first();
                if (!$section) {
                    $fallback = $allSections->where('class_id', $classId)->first();
                    if ($fallback) {
                        $warnings[]        = 'Section "' . $d['section_name'] . '" not found — assigned to ' . $fallback->section_name;
                        $sectionId         = $fallback->id;
                        $d['section_name'] = $fallback->section_name;
                    } else {
                        $errors[] = 'No sections found for class "' . $className . '"';
                    }
                } else {
                    $sectionId = $section->id;
                }
                $d['section_id'] = $sectionId;
            }

            // Duplicate check
            if (isset($d['dob_parsed']) && $classId && $d['student_name'] !== '') {
                $dup = Admission::where('student_name', $d['student_name'])
                    ->where('date_of_birth', $d['dob_parsed'])
                    ->where('class_id', $classId)
                    ->exists();
                if ($dup) {
                    $errors[] = 'Duplicate — a student with the same name, DOB and class already exists (skipped)';
                }
            }

            // Session / academic year
            $d['session_id']    = $session ? $session->id : null;
            $d['academic_year'] = $session ? $session->session_name : '';

            $status = count($errors) > 0 ? 'error' : (count($warnings) > 0 ? 'warning' : 'valid');

            $parsed[] = [
                'line'     => $lineNo,
                'data'     => $d,
                'errors'   => $errors,
                'warnings' => $warnings,
                'status'   => $status,
            ];
        }

        if (empty($parsed)) {
            return back()->withErrors(['file' => 'No data rows found in the uploaded file.']);
        }

        // Store importable rows (valid + warning) in session for commit
        $importable = array_values(array_filter($parsed, function ($r) {
            return $r['status'] !== 'error';
        }));
        session(['import_preview' => $importable]);

        $validCount   = count(array_filter($parsed, function ($r) { return $r['status'] === 'valid'; }));
        $warningCount = count(array_filter($parsed, function ($r) { return $r['status'] === 'warning'; }));
        $errorCount   = count(array_filter($parsed, function ($r) { return $r['status'] === 'error'; }));

        return view('import.students', compact('parsed', 'validCount', 'warningCount', 'errorCount'));
    }

    // ── Commit: import valid rows from session ────────────────────────────

    public function commit(Request $request)
    {
        $rows = session('import_preview');

        if (empty($rows)) {
            return redirect()->route('import.students')
                ->withErrors(['file' => 'Preview session expired — please re-upload the file.']);
        }

        $imported = 0;
        $skipped  = 0;
        $rowErrors = [];

        foreach ($rows as $row) {
            $d = $row['data'];
            DB::beginTransaction();
            try {
                $admission = Admission::create([
                    'student_name'    => $d['student_name'],
                    'date_of_birth'   => isset($d['dob_parsed']) ? $d['dob_parsed'] : null,
                    'gender'          => $d['gender'],
                    'class_id'        => $d['class_id'],
                    'section_id'      => isset($d['section_id']) ? $d['section_id'] : null,
                    'session_id'      => $d['session_id'],
                    'academic_year'   => $d['academic_year'],
                    'fee_category'    => $d['fee_category'],
                    'father_name'     => $d['father_name'] !== '' ? $d['father_name'] : null,
                    'father_phone'    => $d['father_phone'] !== '' ? $d['father_phone'] : null,
                    'mother_name'     => $d['mother_name'] !== '' ? $d['mother_name'] : null,
                    'mother_phone'    => $d['mother_phone'] !== '' ? $d['mother_phone'] : null,
                    'contact_mobile'  => $d['contact_mobile'] !== '' ? $d['contact_mobile'] : null,
                    'full_address'    => $d['full_address'] !== '' ? $d['full_address'] : null,
                    'city'            => $d['city'] !== '' ? $d['city'] : null,
                    'general_id'      => $d['general_id'] !== '' ? $d['general_id'] : null,
                    'blood_type'      => $d['blood_type'] !== '' ? $d['blood_type'] : null,
                    'religion'        => $d['religion'] !== '' ? $d['religion'] : null,
                    'caste'           => $d['caste'] !== '' ? $d['caste'] : null,
                    'previous_school' => $d['previous_school'] !== '' ? $d['previous_school'] : null,
                    'inquiry_date'    => now()->toDateString(),
                    'status'          => Admission::STATUS_CONFIRMED,
                    'confirmed_date'  => now()->toDateString(),
                ]);

                // Assign admission number
                if (in_array($d['class_name'], self::PRE_PRIMARY)) {
                    $admission->dga_admission_no = $d['dga_admission_no'] !== ''
                        ? $d['dga_admission_no']
                        : Admission::generateDgaAdmissionNo($d['academic_year']);
                    $admission->save();
                } else {
                    // general_id already saved above; no change needed
                }

                // Create student user
                $nameParts = explode(' ', $admission->student_name, 2);
                $student = User::create([
                    'first_name'           => $nameParts[0],
                    'last_name'            => isset($nameParts[1]) ? $nameParts[1] : '',
                    'email'                => $this->generateStudentEmail($admission->student_name, $admission->id),
                    'password'             => Hash::make('dga@student2026'),
                    'gender'               => ucfirst($admission->gender ?? 'male'),
                    'nationality'          => 'Indian',
                    'phone'                => $admission->father_phone ?? $admission->contact_mobile ?? '',
                    'address'              => $admission->full_address ?? '',
                    'address2'             => $admission->village ?? '',
                    'city'                 => $admission->city ?? '',
                    'zip'                  => $admission->zip ?? '',
                    'birthday'             => $admission->date_of_birth,
                    'religion'             => $admission->religion,
                    'role'                 => 'student',
                    'fee_category'         => $admission->fee_category,
                    'dga_admission_no'     => $admission->dga_admission_no,
                    'general_id'           => $admission->general_id,
                    'village'              => $admission->village ?? '',
                    'distance_from_school' => $admission->distance_from_school ?? '',
                    'student_status'       => 'active',
                    'admission_id'         => $admission->id,
                    'blood_type'           => $admission->blood_type,
                ]);

                $student->assignRole('student');

                // Create promotion record
                $promotionRepo = new \App\Repositories\PromotionRepository();
                $promotionRepo->assignClassSection([
                    'session_id'     => $admission->session_id,
                    'class_id'       => $admission->class_id,
                    'section_id'     => $admission->section_id,
                    'id_card_number' => $admission->dga_admission_no ?? $admission->general_id ?? 'DGA-' . $admission->id,
                ], $student->id);

                // Link student to admission
                $admission->student_user_id = $student->id;
                $admission->save();

                // Create document checklist
                $this->createDocumentChecklist($admission);

                DB::commit();
                $imported++;

            } catch (\Exception $e) {
                DB::rollBack();
                $skipped++;
                $rowErrors[] = 'Row ' . $row['line'] . ' (' . $d['student_name'] . '): ' . $e->getMessage();
            }
        }

        session()->forget('import_preview');

        return redirect()->route('import.students')->with('import_result', [
            'imported'  => $imported,
            'skipped'   => $skipped,
            'rowErrors' => $rowErrors,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function generateStudentEmail($name, $id)
    {
        $slug = strtolower(str_replace(' ', '.', trim($name)));
        $slug = preg_replace('/[^a-z0-9.]/', '', $slug);
        $base = $slug . '.' . $id . '@deepgriha.com';
        if (!User::where('email', $base)->exists()) {
            return $base;
        }
        $counter = 2;
        while (User::where('email', $slug . '.' . $id . '.' . $counter . '@deepgriha.com')->exists()) {
            $counter++;
        }
        return $slug . '.' . $id . '.' . $counter . '@deepgriha.com';
    }

    private function createDocumentChecklist($admission)
    {
        $documents = [
            'birth_certificate'  => 'pending',
            'previous_school_lc' => 'pending',
            'caste_certificate'  => 'pending',
            'rte_documents'      => 'pending',
        ];
        foreach ($documents as $type => $status) {
            AdmissionDocument::create([
                'admission_id'  => $admission->id,
                'document_type' => $type,
                'status'        => $status,
            ]);
        }
    }
}
