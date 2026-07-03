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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentImportController extends Controller
{
    /*
     * Template column layout (1-based, matches downloadTemplate())
     *
     *  A(1)   Sr.No
     *  B(2)   Student Name       *required
     *  C(3)   Date of Birth      DD/MM/YYYY
     *  D(4)   Gender             *required  male / female
     *  E(5)   Class              *required
     *  F(6)   Section            A / B  (default A)
     *  G(7)   Father's Contact No
     *  H(8)   Fee Category       *required  general / rte / coc / discount
     *  I(9)   Discount %         required when H = discount
     *  J(10)  Custom Tuition Fee
     *  K(11)  Already Collected (₹)  creates a fee_payment record; enter 0 if nothing collected
     *  L(12)  Admission Date (DD/MM/YYYY)  used for inquiry_date & confirmed_date; leave blank = today
     *  M(13)  Village
     *  N(14)  Distance from School
     *  O(15)  Father Name
     *  P(16)  Father Occupation
     *  Q(17)  Mother Name
     *  R(18)  Mother Occupation
     *  S(19)  General ID         11-digit ZP/SARAL ID
     *  T(20)  DGA Admission No   pre-primary only
     *  U(21)  RTE Application No e.g. 26MS011157 (only for fee_category = rte)
     *  V(22)+ Notes / anything else — ignored on import
     */
    const COLUMNS = [
        1  => 'sr_no',
        2  => 'student_name',
        3  => 'date_of_birth',
        4  => 'gender',
        5  => 'class_name',
        6  => 'section_name',
        7  => 'father_phone',
        8  => 'fee_category',
        9  => 'discount_percentage',
        10 => 'custom_tuition_fee',
        11 => 'already_collected',
        12 => 'admission_date',
        13 => 'village',
        14 => 'distance_from_school',
        15 => 'father_name',
        16 => 'father_occupation',
        17 => 'mother_name',
        18 => 'mother_occupation',
        19 => 'general_id',
        20 => 'dga_admission_no',
        21 => 'rte_application_no',
    ];

    const REQUIRED_FIELDS = ['student_name', 'gender', 'class_name', 'fee_category'];

    const PRE_PRIMARY = ['Nursery', 'Lower KG', 'Upper KG'];

    const VALID_CLASSES = [
        'Nursery', 'Lower KG', 'Upper KG',
        'Class 1', 'Class 2', 'Class 3', 'Class 4',
        'Class 5', 'Class 6', 'Class 7', 'Class 8',
    ];

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

        // ── Header row ────────────────────────────────────────────────────
        $headers = [
            ['label' => 'Sr.No',                   'required' => false, 'note' => 'Reference only — ignored on import'],
            ['label' => 'Student Name',             'required' => true,  'note' => 'Full name as it should appear in the system'],
            ['label' => 'Date of Birth (DD/MM/YYYY)', 'required' => false, 'note' => 'e.g. 15/08/2015 — optional, can be updated later via admission edit'],
            ['label' => 'Gender',                   'required' => true,  'note' => 'male or female'],
            ['label' => 'Class',                    'required' => true,  'note' => 'Nursery / Lower KG / Upper KG / Class 1 … Class 8'],
            ['label' => 'Section',                  'required' => false, 'note' => 'A or B — leave blank to default to A'],
            ['label' => "Father's Contact No",       'required' => false, 'note' => '10-digit mobile — main contact for this student'],
            ['label' => 'Fee Category',             'required' => true,  'note' => 'general / rte / coc / discount'],
            ['label' => 'Discount %',               'required' => false, 'note' => 'Required if Fee Category = discount. Enter number only, e.g. 50 for 50% off.'],
            ['label' => 'Custom Tuition Fee (Rs)', 'required' => false, 'note' => 'Fill ONLY if actual fee is a flat negotiated amount. Leave blank to let the system calculate from category/discount.'],
            ['label' => 'Already Collected (Rs)',  'required' => false, 'note' => 'Fees already collected for this student. A payment record will be created automatically. Enter 0 if nothing collected.'],
            ['label' => 'Admission Date (DD/MM/YYYY)', 'required' => false, 'note' => 'Date of admission e.g. 15/01/2025. Leave blank to use today\'s date. Affects roll number ordering.'],
            ['label' => 'Village',                  'required' => false, 'note' => ''],
            ['label' => 'Distance from School',     'required' => false, 'note' => 'e.g. 2.5km'],
            ['label' => 'Father Name',              'required' => false, 'note' => ''],
            ['label' => 'Father Occupation',        'required' => false, 'note' => ''],
            ['label' => 'Mother Name',              'required' => false, 'note' => ''],
            ['label' => 'Mother Occupation',        'required' => false, 'note' => ''],
            ['label' => 'General ID (Class 1+)',    'required' => false, 'note' => '11-digit ZP / SARAL ID'],
            ['label' => 'DGA Admission No',         'required' => false, 'note' => 'Pre-primary only. Leave blank to auto-generate.'],
            ['label' => 'RTE Application No',       'required' => false, 'note' => 'Only for RTE students. Alphanumeric, e.g. 26MS011157 (Year+StateCode+Sequence).'],
        ];

        foreach ($headers as $i => $h) {
            $col = $i + 1;
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $cell = $letter . '1';

            $label = $h['required'] ? $h['label'] . ' *' : $h['label'];
            $sheet->setCellValue($cell, $label);

            if ($h['note'] !== '') {
                $sheet->getComment($cell)->getText()->createTextRun($h['note']);
                $sheet->getComment($cell)->setWidth('200pt');
                $sheet->getComment($cell)->setHeight('40pt');
            }

            $bgColor = $h['required'] ? 'C0392B' : '2E4057';
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getColumnDimension($letter)->setWidth(20);
        }
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ── Example rows ─────────────────────────────────────────────────
        $examples = [
            // General boy — paid Rs.16200 in full
            [1, 'Rahul Kumar', '15/08/2015', 'male', 'Class 3', 'A', '9876543210',
             'general', '', '', 16200, '10/01/2022', 'Yawat', '2km', 'Suresh Kumar', 'Farmer',
             'Priya Kumar', 'Homemaker', '', ''],
            // Discount girl — 50% + custom Rs.6000, collected Rs.3000 so far
            [2, 'Aradhya Devidas Kalaphad', '10/03/2017', 'female', 'Nursery', 'A', '7972024744',
             'discount', '50', 6000, 3000, '15/01/2025', 'Baravkarvadi', '3km', 'Devidas Kalaphad', 'Worker',
             'Sunita Kalaphad', 'Homemaker', '', ''],
            // RTE — zero fees paid (govt pays), enter 0; fill RTE App No in column U
            [3, 'Mohammed Arif Khan', '20/06/2016', 'male', 'Class 2', 'A', '8765432109',
             'rte', '', '', 0, '05/01/2023', 'Kedgaon', '3km', 'Anwar Khan', 'Driver',
             '', '', '12345678901', '', '26MS011157'],
        ];

        foreach ($examples as $ri => $row) {
            $rowNum = $ri + 2;
            foreach ($row as $ci => $val) {
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                $sheet->setCellValue($letter . $rowNum, $val);
            }
            $bgEx = $ri === 0 ? 'EBF5FB' : ($ri === 1 ? 'FEF9E7' : 'EAFAF1');
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgEx]],
                'font' => ['italic' => true, 'color' => ['rgb' => '555555']],
            ]);
        }

        $sheet->setCellValue('A5', '← DELETE rows 2-4 (examples) before uploading your real data');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'E74C3C']],
        ]);

        $sheet->freezePane('A2');

        // ── Instructions sheet ────────────────────────────────────────────
        $info = $spreadsheet->createSheet();
        $info->setTitle('Instructions');

        $infoRows = [
            ['Deep Griha Academy — Student Import Template', true, 13],
            ['', false, 11],
            ['REQUIRED columns (red header) — must be filled for every student:', true, 11],
            ['  • Student Name        — full name as it should appear in the system', false, 10],
            ['  • Date of Birth       — DD/MM/YYYY format, e.g. 15/08/2015', false, 10],
            ['  • Gender              — male or female (lowercase)', false, 10],
            ['  • Class               — must exactly match: Nursery, Lower KG, Upper KG, Class 1, Class 2, Class 3, Class 4, Class 5, Class 6, Class 7, Class 8', false, 10],
            ['  • Fee Category        — general / rte / coc / discount (lowercase)', false, 10],
            ['', false, 11],
            ['FEE CATEGORY guide:', true, 11],
            ['  general   — standard fee as per fee structure (system calculates automatically)', false, 10],
            ['  rte       — Right to Education (zero tuition)', false, 10],
            ['  coc       — Corporation of Chinchwad subsidy (boys only)', false, 10],
            ['  discount  — partial discount; enter the % in "Discount %" column (e.g. 50 for 50% off)', false, 10],
            ['', false, 11],
            ['DISCOUNT % column:', true, 11],
            ['  Required when Fee Category = discount. Enter a number, e.g. 50 for 50%.', false, 10],
            ['  System calculates: Boys fee x (1 - %) or Girls fee x (1 - %) based on gender.', false, 10],
            ['  Leave blank for general / rte / coc.', false, 10],
            ['', false, 11],
            ['CUSTOM TUITION FEE column:', true, 11],
            ['  Fill ONLY when the negotiated fee does not match the discount calculation.', false, 10],
            ['  Example: student is 50% discount but agreed fee is Rs.6000 (not Rs.6100).', false, 10],
            ['  Enter 6000 here. Leave blank for all other students.', false, 10],
            ['', false, 11],
            ['ALREADY COLLECTED (Rs) column:', true, 11],
            ['  Enter the total fees already collected for this student (from your records).', false, 10],
            ['  A fee payment record will be created automatically during import.', false, 10],
            ['  Enter 0 if nothing has been collected yet.', false, 10],
            ['  RTE/COC students: enter 0 (their fees are covered by government/scheme).', false, 10],
            ['', false, 11],
            ['ADMISSION DATE column:', true, 11],
            ['  Enter the date of admission in DD/MM/YYYY format, e.g. 15/01/2025.', false, 10],
            ['  This affects roll number ordering — students admitted earlier get lower roll numbers.', false, 10],
            ['  Leave blank to use today\'s date.', false, 10],
            ['', false, 11],
            ['OTHER NOTES:', true, 11],
            ['  Section — enter A or B. Leave blank to default to A.', false, 10],
            ['  General ID — 11-digit ZP/SARAL number for Class 1 and above (optional).', false, 10],
            ['  DGA Admission No — for Nursery/LKG/UKG only. Leave blank to auto-generate.', false, 10],
            ['  RTE Application No — alphanumeric, e.g. 26MS011157. Fill only for RTE students.', false, 10],
            ['  Sr.No column is ignored — just for your reference while filling the sheet.', false, 10],
            ['', false, 11],
            ['IMPORTANT:', true, 11],
            ['  • Delete the 3 example rows (rows 2-4) before uploading.', false, 10],
            ['  • Do NOT change column order or add/remove columns.', false, 10],
            ['  • Save as .xlsx before uploading.', false, 10],
            ['  • Use the Preview step to check for errors before committing.', false, 10],
        ];

        foreach ($infoRows as $i => $r) {
            $cell = 'A' . ($i + 1);
            $info->setCellValue($cell, $r[0]);
            if ($r[1]) {
                $info->getStyle($cell)->getFont()->setBold(true)->setSize($r[2]);
            } else {
                $info->getStyle($cell)->getFont()->setSize($r[2]);
            }
        }
        $info->getColumnDimension('A')->setWidth(100);

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="DGA_StudentImportTemplate.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ── Preview: validate file, return row-by-row results ────────────────

    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:10240']);

        $path = $request->file('file')->getPathname();

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Could not read the Excel file. Please use the downloaded template (.xlsx).']);
        }

        $rawRows = $sheet->toArray(null, true, true, false);
        array_shift($rawRows); // remove header row

        // Lookup tables
        $session     = SchoolSession::latest('id')->first();
        $classMap    = SchoolClass::where('session_id', $session->id)->pluck('id', 'class_name')->toArray();
        $allSections = Section::whereIn('class_id', array_values($classMap))->get();

        $parsed = [];

        foreach ($rawRows as $rowIndex => $row) {
            $lineNo = $rowIndex + 2;

            // Skip blank rows and the "delete these examples" notice row
            $allEmpty = true;
            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') { $allEmpty = false; break; }
            }
            if ($allEmpty) continue;

            $first = trim((string) ($row[0] ?? ''));
            if (strpos($first, '←') !== false || strpos($first, 'DELETE') !== false) continue;

            // Map columns → fields
            $d = [];
            foreach (self::COLUMNS as $colIdx => $field) {
                $d[$field] = isset($row[$colIdx - 1]) ? trim((string) $row[$colIdx - 1]) : '';
            }

            $errors   = [];
            $warnings = [];

            // ── Required fields ───────────────────────────────────────────
            foreach (self::REQUIRED_FIELDS as $field) {
                if ($d[$field] === '') {
                    $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required';
                }
            }

            // ── Date of birth ─────────────────────────────────────────────
            if ($d['date_of_birth'] !== '') {
                $dob = \DateTime::createFromFormat('d/m/Y', $d['date_of_birth']);
                if (!$dob) {
                    $errors[] = 'Date of Birth must be DD/MM/YYYY (e.g. 15/08/2015)';
                } else {
                    $d['dob_parsed'] = $dob->format('Y-m-d');
                }
            }

            // ── Admission date ────────────────────────────────────────────
            if ($d['admission_date'] !== '') {
                $admDate = \DateTime::createFromFormat('d/m/Y', $d['admission_date']);
                if (!$admDate) {
                    $warnings[] = 'Admission Date "' . $d['admission_date'] . '" is not DD/MM/YYYY — using today';
                    $d['admission_date_parsed'] = now()->toDateString();
                } else {
                    $d['admission_date_parsed'] = $admDate->format('Y-m-d');
                }
            } else {
                $d['admission_date_parsed'] = now()->toDateString();
            }

            // ── Gender ────────────────────────────────────────────────────
            if ($d['gender'] !== '') {
                $d['gender'] = strtolower($d['gender']);
                if (!in_array($d['gender'], ['male', 'female'])) {
                    $errors[] = 'Gender must be "male" or "female"';
                }
            }

            // ── Class lookup ──────────────────────────────────────────────
            $classId = null;
            if ($d['class_name'] !== '') {
                if (!isset($classMap[$d['class_name']])) {
                    $errors[] = 'Class "' . $d['class_name'] . '" not recognised — must exactly match: ' . implode(', ', self::VALID_CLASSES);
                } else {
                    $classId       = $classMap[$d['class_name']];
                    $d['class_id'] = $classId;
                }
            }

            // ── Section lookup ────────────────────────────────────────────
            if ($classId) {
                $sectionName = $d['section_name'] !== '' ? strtoupper($d['section_name']) : 'A';
                $section = $allSections->where('class_id', $classId)->where('section_name', $sectionName)->first();
                if (!$section) {
                    $fallback = $allSections->where('class_id', $classId)->first();
                    if ($fallback) {
                        $warnings[]        = 'Section "' . $d['section_name'] . '" not found — assigned to ' . $fallback->section_name;
                        $d['section_id']   = $fallback->id;
                        $d['section_name'] = $fallback->section_name;
                    } else {
                        $errors[] = 'No sections found for class "' . $d['class_name'] . '"';
                    }
                } else {
                    $d['section_id'] = $section->id;
                }
            }

            // ── Fee category ──────────────────────────────────────────────
            if ($d['fee_category'] !== '') {
                $d['fee_category'] = strtolower($d['fee_category']);
                if (!in_array($d['fee_category'], ['general', 'rte', 'coc', 'discount'])) {
                    $errors[] = 'Fee Category must be: general, rte, coc, or discount';
                } elseif ($d['fee_category'] === 'coc' && ($d['gender'] ?? '') === 'female') {
                    $errors[] = 'CoC fee category is only available for male students';
                } elseif ($d['fee_category'] === 'discount') {
                    if ($d['discount_percentage'] === '' && $d['custom_tuition_fee'] === '') {
                        $errors[] = 'Discount students need either a Discount % or a Custom Tuition Fee (or both)';
                    } elseif ($d['discount_percentage'] !== '') {
                        if (!is_numeric($d['discount_percentage']) || (float)$d['discount_percentage'] <= 0 || (float)$d['discount_percentage'] > 100) {
                            $errors[] = 'Discount % must be a number between 1 and 100';
                        } else {
                            $d['discount_percentage'] = (float) $d['discount_percentage'];
                        }
                    }
                } else {
                    $d['discount_percentage'] = null;
                }
            }

            // ── Custom tuition fee ────────────────────────────────────────
            if ($d['custom_tuition_fee'] !== '') {
                if (!is_numeric($d['custom_tuition_fee']) || (float)$d['custom_tuition_fee'] < 0) {
                    $errors[] = 'Custom Tuition Fee must be a positive number (or leave blank)';
                } else {
                    $d['custom_tuition_fee'] = (float) $d['custom_tuition_fee'];
                }
            } else {
                $d['custom_tuition_fee'] = null;
            }

            // ── Already collected ─────────────────────────────────────────
            $raw = $d['already_collected'];
            if ($raw === '' || $raw === null || strtolower((string)$raw) === 'rte' || strtolower((string)$raw) === 'free') {
                $d['already_collected'] = 0;
            } elseif (!is_numeric($raw) || (float)$raw < 0) {
                $warnings[] = 'Already Collected "' . $raw . '" is not a valid number — treating as 0';
                $d['already_collected'] = 0;
            } else {
                $d['already_collected'] = (float) $raw;
            }

            // ── General ID ────────────────────────────────────────────────
            if ($d['general_id'] !== '') {
                if (!preg_match('/^\d{11}$/', $d['general_id'])) {
                    $errors[] = 'General ID must be exactly 11 digits';
                } elseif (Admission::where('general_id', $d['general_id'])->exists()) {
                    $errors[] = 'General ID ' . $d['general_id'] . ' already exists in the system';
                }
            }

            // ── RTE Application No ────────────────────────────────────────
            if (($d['rte_application_no'] ?? '') !== '') {
                $d['rte_application_no'] = strtoupper(trim($d['rte_application_no']));
                if (!preg_match('/^[A-Z0-9]{1,20}$/', $d['rte_application_no'])) {
                    $errors[] = 'RTE Application No must be alphanumeric, max 20 characters';
                } elseif (($d['fee_category'] ?? '') !== 'rte') {
                    $errors[] = 'RTE Application No should only be filled for RTE students';
                }
            }

            // ── Duplicate check ───────────────────────────────────────────
            if ($classId && $d['student_name'] !== '') {
                $dupQuery = Admission::where('student_name', $d['student_name'])
                    ->where('class_id', $classId);
                if (isset($d['dob_parsed'])) {
                    $dupQuery->where('date_of_birth', $d['dob_parsed']);
                }
                if ($dupQuery->exists()) {
                    $errors[] = 'Duplicate — a student with this name' . (isset($d['dob_parsed']) ? ', DOB' : '') . ' and class already exists';
                }
            }

            // ── Session ───────────────────────────────────────────────────
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
            return back()->withErrors(['file' => 'No data rows found. Make sure to delete the example rows and save as .xlsx.']);
        }

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

        $session = SchoolSession::latest('id')->first();

        $imported   = 0;
        $skipped    = 0;
        $rowErrors  = [];

        foreach ($rows as $row) {
            $d = $row['data'];
            DB::beginTransaction();
            try {
                // ── Create admission record ───────────────────────────────
                $admission = Admission::create([
                    'student_name'         => $d['student_name'],
                    'date_of_birth'        => isset($d['dob_parsed']) ? $d['dob_parsed'] : null,
                    'gender'               => $d['gender'],
                    'class_id'             => $d['class_id'],
                    'section_id'           => isset($d['section_id']) ? $d['section_id'] : null,
                    'session_id'           => $d['session_id'],
                    'academic_year'        => $d['academic_year'],
                    'fee_category'         => $d['fee_category'],
                    'discount_percentage'  => (isset($d['discount_percentage']) && $d['discount_percentage'] !== '') ? $d['discount_percentage'] : null,
                    'custom_tuition_fee'   => (isset($d['custom_tuition_fee']) && $d['custom_tuition_fee'] !== '') ? $d['custom_tuition_fee'] : null,
                    'father_name'          => $d['father_name'] !== '' ? $d['father_name'] : null,
                    'father_phone'         => $d['father_phone'] !== '' ? $d['father_phone'] : null,
                    'father_occupation'    => $d['father_occupation'] !== '' ? $d['father_occupation'] : null,
                    'mother_name'          => $d['mother_name'] !== '' ? $d['mother_name'] : null,
                    'mother_phone'         => null,
                    'mother_occupation'    => $d['mother_occupation'] !== '' ? $d['mother_occupation'] : null,
                    'contact_mobile'       => $d['father_phone'] !== '' ? $d['father_phone'] : null,
                    'village'              => $d['village'] !== '' ? $d['village'] : null,
                    'distance_from_school' => $d['distance_from_school'] !== '' ? $d['distance_from_school'] : null,
                    'general_id'           => $d['general_id'] !== '' ? $d['general_id'] : null,
                    'rte_application_no'   => ($d['rte_application_no'] ?? '') !== '' ? strtoupper($d['rte_application_no']) : null,
                    'inquiry_date'         => $d['admission_date_parsed'] ?? now()->toDateString(),
                    'status'               => Admission::STATUS_CONFIRMED,
                    'confirmed_date'       => $d['admission_date_parsed'] ?? now()->toDateString(),
                ]);

                // ── Assign admission number ───────────────────────────────
                if (in_array($d['class_name'], self::PRE_PRIMARY)) {
                    $admission->dga_admission_no = ($d['dga_admission_no'] !== '')
                        ? $d['dga_admission_no']
                        : Admission::generateDgaAdmissionNo($d['academic_year']);
                    $admission->save();
                }

                // ── Create student user ───────────────────────────────────
                $nameParts = explode(' ', $admission->student_name, 2);
                $student = User::create([
                    'first_name'           => $nameParts[0],
                    'last_name'            => isset($nameParts[1]) ? $nameParts[1] : '',
                    'email'                => $this->generateStudentEmail($admission->student_name, $admission->id),
                    'password'             => Hash::make('dga@student2026'),
                    'gender'               => ucfirst($admission->gender ?? 'male'),
                    'nationality'          => 'Indian',
                    'phone'                => $admission->father_phone ?? $admission->contact_mobile ?? '',
                    'address'              => '',
                    'address2'             => '',
                    'city'                 => '',
                    'zip'                  => '',
                    'birthday'             => $admission->date_of_birth,
                    'role'                 => 'student',
                    'fee_category'         => $admission->fee_category,
                    'dga_admission_no'     => $admission->dga_admission_no,
                    'general_id'           => $admission->general_id,
                    'village'              => $admission->village ?? '',
                    'distance_from_school' => $admission->distance_from_school ?? '',
                    'student_status'       => 'active',
                    'admission_id'         => $admission->id,
                ]);

                // Link student back to admission
                $admission->student_user_id = $student->id;
                $admission->save();

                // ── Create promotion ──────────────────────────────────────
                DB::table('promotions')->insert([
                    'student_id'     => $student->id,
                    'session_id'     => $admission->session_id,
                    'class_id'       => $admission->class_id,
                    'section_id'     => $admission->section_id,
                    'id_card_number' => $d['general_id'] !== '' ? $d['general_id'] : ($admission->dga_admission_no ?? ''),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // ── Record already-collected fee payment ──────────────────
                if (!empty($d['already_collected']) && (float)$d['already_collected'] > 0) {
                    $nextChallan = \App\Models\FeePayment::nextChallanNo();
                    DB::table('fee_payments')->insert([
                        'student_user_id'      => $student->id,
                        'session_id'           => $admission->session_id,
                        'challan_no'           => $nextChallan,
                        'payment_date'         => now()->toDateString(),
                        'amount_paid'          => (float) $d['already_collected'],
                        'payment_mode'         => 'cash',
                        'payment_category'     => 'fee',
                        'is_internal_transfer' => false,
                        'recorded_by'          => auth()->id() ?? 1,
                        'notes'                => 'Imported from DGA records (2024-25 collection data)',
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);
                }

                // ── Create document checklist ─────────────────────────────
                $this->createDocumentChecklist($admission);

                DB::commit();
                $imported++;

            } catch (\Exception $e) {
                DB::rollBack();
                $skipped++;
                $rowErrors[] = 'Row ' . $row['line'] . ' (' . ($d['student_name'] ?? '?') . '): ' . $e->getMessage();
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
        $base = $slug . '@deepgriha.com';
        if (!User::where('email', $base)->exists()) {
            return $base;
        }
        $counter = 2;
        while (User::where('email', $slug . $counter . '@deepgriha.com')->exists()) {
            $counter++;
        }
        return $slug . $counter . '@deepgriha.com';
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
