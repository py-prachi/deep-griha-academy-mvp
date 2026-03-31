<?php
/**
 * Run: docker exec app php /var/www/generate_test_import.php
 * Then: docker cp app:/var/www/TestImport.xlsx .
 */
require '/var/www/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Students');

// Headers (must match template exactly)
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

// Test rows
// [student_name, dob, gender, class_name, section, fee_category, father_name, father_phone,
//  mother_name, mother_phone, contact_mobile, full_address, city, general_id, dga_admission_no,
//  blood_type, religion, caste, previous_school]
$rows = [
    // ── Valid rows ─────────────────────────────────────────────────────
    // Pre-primary — no general_id, dga_admission_no auto-generated
    ['Anaya Sharma',       '10/05/2021', 'female', 'Nursery',   'A', 'general', 'Rajesh Sharma',  '9876501001', 'Sunita Sharma',  '9876501002', '', 'Plot 12, Nagar Road', 'Pune', '', '', 'B+', 'Hindu', 'Brahmin', ''],
    ['Karan Patel',        '22/07/2020', 'male',   'Lower KG',  'A', 'general', 'Vikram Patel',   '9876501003', 'Meena Patel',    '9876501004', '', 'Flat 4, Shivaji Nagar', 'Pune', '', '', 'O+', 'Hindu', 'Patel', ''],
    ['Priya Desai',        '14/01/2020', 'female', 'Upper KG',  'A', 'rte',     'Mahesh Desai',   '9876501005', 'Kavita Desai',   '9876501006', '', 'Village Rd, Daund', 'Daund', '', '', 'A+', 'Hindu', 'OBC', ''],

    // Class 1+ — with general_id
    ['Rohan Joshi',        '03/09/2017', 'male',   'Class 1',   'A', 'general', 'Anil Joshi',     '9876501007', 'Reka Joshi',     '9876501008', '', 'Near Temple, Pimpri', 'Pune', '12345678901', '', 'AB+', 'Hindu', 'Brahmin', 'City Primary School'],
    ['Sneha Kulkarni',     '19/03/2016', 'female', 'Class 2',   'A', 'general', 'Suresh Kulkarni','9876501009', 'Asha Kulkarni',  '9876501010', '', 'Sector 7, Camp Area', 'Pune', '12345678902', '', 'B-', 'Hindu', 'Brahmin', 'St. Marys School'],
    ['Aarav Singh',        '28/11/2015', 'male',   'Class 3',   'A', 'coc',     'Harpreet Singh', '9876501011', 'Manpreet Singh', '9876501012', '', 'Near Bus Stand', 'Pune', '12345678903', '', 'O-', 'Sikh', 'Singh', 'DGA Branch'],
    ['Diya Nair',          '07/06/2014', 'female', 'Class 4',   'A', 'general', 'Rajan Nair',     '9876501013', 'Lakshmi Nair',   '9876501014', '', 'Koregaon Park', 'Pune', '12345678904', '', 'A-', 'Hindu', 'Nair', 'Kerala Vidyalaya'],
    ['Arjun Yadav',        '15/04/2013', 'male',   'Class 5',   'A', 'discount','Ramesh Yadav',   '9876501015', 'Sushma Yadav',   '9876501016', '', 'Shastri Nagar', 'Pune', '12345678905', '', 'B+', 'Hindu', 'Yadav', 'Government School'],
    ['Ishaan Mehta',       '30/08/2012', 'male',   'Class 6',   'A', 'general', 'Vivek Mehta',    '9876501017', 'Pooja Mehta',    '9876501018', '', 'MG Road', 'Pune', '12345678906', '', 'O+', 'Hindu', 'Brahmin', 'New English School'],
    ['Kavya Reddy',        '12/12/2011', 'female', 'Class 7',   'A', 'rte',     'Srinivas Reddy', '9876501019', 'Padma Reddy',    '9876501020', '', 'Hadapsar Colony', 'Pune', '12345678907', '', 'AB-', 'Hindu', 'Reddy', ''],
    ['Vihaan Kapoor',      '25/02/2011', 'male',   'Class 8',   'A', 'general', 'Deepak Kapoor',  '9876501021', 'Nisha Kapoor',   '9876501022', '', 'Baner Road', 'Pune', '12345678908', '', 'A+', 'Hindu', 'Kapoor', 'Delhi Public School'],

    // ── Warning rows (duplicates check — only if already in DB) ────────
    // This one has valid data but no general_id for Class 1+ → still valid (optional)
    ['Meera Patil',        '01/01/2016', 'female', 'Class 2',   'A', 'general', 'Ganesh Patil',   '9876501023', 'Suman Patil',    '9876501024', '', 'Bhosari', 'Pune', '', '', 'B+', 'Hindu', 'Maratha', 'Pune Municipal School'],

    // ── Error rows ──────────────────────────────────────────────────────
    // Missing student_name
    ['',                   '10/05/2020', 'male',   'Class 1',   'A', 'general', 'Father Name',    '9876501099', '', '', '', '', '', '', '', '', '', '', ''],
    // Bad date format
    ['Ravi Kumar',         '2020-05-10', 'male',   'Class 2',   'A', 'general', 'Sunil Kumar',    '9876501098', '', '', '', '', 'Pune', '', '', '', '', '', ''],
    // Bad class name
    ['Tina Thomas',        '10/05/2018', 'female', 'Grade 3',   'A', 'general', 'George Thomas',  '9876501097', '', '', '', '', 'Pune', '', '', '', '', '', ''],
    // Bad fee category
    ['Raj Verma',          '10/05/2017', 'male',   'Class 3',   'A', 'premium', 'Suresh Verma',   '9876501096', '', '', '', '', 'Pune', '', '', '', '', '', ''],
    // Bad gender
    ['Nisha Gupta',        '10/05/2018', 'other',  'Class 1',   'A', 'general', 'Amit Gupta',     '9876501095', '', '', '', '', 'Pune', '', '', '', '', '', ''],
    // Missing fee_category
    ['Sumit Shah',         '10/05/2016', 'male',   'Class 2',   'A', '',        'Ravi Shah',      '9876501094', '', '', '', '', 'Pune', '', '', '', '', '', ''],
];

foreach ($rows as $rowIndex => $rowData) {
    $rowNum = $rowIndex + 2;
    foreach ($rowData as $colIndex => $val) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($colLetter . $rowNum, $val);
    }
}

// Colour-code test sections for readability
// Rows 2–12: valid (light green)
$sheet->getStyle('A2:S12')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAFAF1']]]);
// Row 13: warning (light yellow)
$sheet->getStyle('A13:S13')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDFDE7']]]);
// Rows 14–19: error (light red)
$sheet->getStyle('A14:S19')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDEDEC']]]);

$sheet->freezePane('A2');

$writer = new Xlsx($spreadsheet);
$writer->save('/var/www/TestImport.xlsx');
echo "TestImport.xlsx generated successfully.\n";
echo "Run: docker cp app:/var/www/TestImport.xlsx .\n";
