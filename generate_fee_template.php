<?php
/**
 * Run: docker exec app php generate_fee_template.php
 * Output: FeeStructureTemplate.xlsx in project root
 */

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

$spreadsheet = new Spreadsheet();

// ── Sheet 1: Fee Structure ────────────────────────────────────────────────────

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Fee Structure 2025-2026');

$headers = [
    'class_name *',
    'fee_category *',
    'admission_fee',
    'tuition_fee (boys)',
    'transport_fee',
    'other_fee',
];

$requiredCols = [1, 2];
$colLetters   = ['A', 'B', 'C', 'D', 'E', 'F'];
$colWidths    = [16, 14, 14, 18, 14, 12];

// Header row
foreach ($headers as $i => $h) {
    $col  = $colLetters[$i];
    $cell = $col . '1';
    $sheet->setCellValue($cell, $h);

    $isRequired = in_array($i + 1, $requiredCols);
    $sheet->getStyle($cell)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $isRequired ? 'C0392B' : '1A5276']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    ]);
    $sheet->getColumnDimension($col)->setWidth($colWidths[$i]);
}
$sheet->getRowDimension(1)->setRowHeight(32);

// ── Fee data ──────────────────────────────────────────────────────────────────
// Format: [class_name, fee_category, admission, tuition_boys, transport, other]
// girls_tuition_fee is auto-calculated at 75% of tuition_fee for general category

$prePrimary = ['Nursery', 'Lower KG', 'Upper KG'];
$primary    = ['Class 1', 'Class 2', 'Class 3', 'Class 4'];
$upper      = ['Class 5', 'Class 6', 'Class 7', 'Class 8'];

$rows = [];
foreach ($prePrimary as $cls) {
    $rows[] = [$cls, 'general',  500,    4800, 1200, 0];
    $rows[] = [$cls, 'rte',      0,      0,    0,    0];
    $rows[] = [$cls, 'discount', 500,    3600, 1200, 0];
    $rows[] = [$cls, 'coc',      0,      0,    0,    0];
}
foreach ($primary as $cls) {
    $rows[] = [$cls, 'general',  500,    6000, 1200, 0];
    $rows[] = [$cls, 'rte',      0,      0,    0,    0];
    $rows[] = [$cls, 'discount', 500,    4500, 1200, 0];
    $rows[] = [$cls, 'coc',      0,      0,    0,    0];
}
foreach ($upper as $cls) {
    $rows[] = [$cls, 'general',  1000,   9600, 1200, 0];
    $rows[] = [$cls, 'rte',      0,      0,    0,    0];
    $rows[] = [$cls, 'discount', 1000,   7200, 1200, 0];
    $rows[] = [$cls, 'coc',      0,      0,    0,    0];
}

// Category row colours (light fills)
$catFill = [
    'general'  => 'EBF5FB',
    'rte'      => 'EAFAF1',
    'discount' => 'FEF9E7',
    'coc'      => 'FDEDEC',
];

$rowNum = 2;
$prevClass = '';
foreach ($rows as $row) {

    // Thin top border when class changes
    if ($cls !== $prevClass && $rowNum > 2) {
        $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getBorders()
            ->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('2E4057');
    }
    $prevClass = $cls;

    $fill = $catFill[$cat];
    foreach ($colLetters as $col) {
        $sheet->getStyle($col . $rowNum)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    [$cls, $cat, $adm, $tuit, $trans, $other] = $row;

    $sheet->setCellValue('A' . $rowNum, $cls);
    $sheet->setCellValue('B' . $rowNum, $cat);
    $sheet->setCellValue('C' . $rowNum, $adm);
    $sheet->setCellValue('D' . $rowNum, $tuit);
    $sheet->setCellValue('E' . $rowNum, $trans);
    $sheet->setCellValue('F' . $rowNum, $other);

    // Auto-total formula in a helper column (not imported, for reference only)
    // We'll add it as a comment on column G header instead

    $rowNum++;
}

// Freeze header
$sheet->freezePane('A2');

// Outer border
$lastRow = $rowNum - 1;
$sheet->getStyle('A1:F' . $lastRow)->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('AEB6BF');

// ── Sheet 2: Instructions ─────────────────────────────────────────────────────

$info = $spreadsheet->createSheet();
$info->setTitle('Instructions');

$info->setCellValue('A1', 'Deep Griha Academy — Fee Structure Import Instructions');
$info->getStyle('A1')->getFont()->setBold(true)->setSize(13);
$info->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2E4057');
$info->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
$info->getRowDimension(1)->setRowHeight(24);
$info->getColumnDimension('A')->setWidth(100);

$lines = [
    '',
    'HOW TO USE THIS TEMPLATE',
    '─────────────────────────────────────────────────────────',
    '',
    '1. Edit the amounts in the "Fee Structure 2025-2026" sheet.',
    '   - The amounts shown are SAMPLE values — replace with actual DGA fees.',
    '   - Rows highlighted in RED (coc) are Internal Transfer rows — amounts are usually 0.',
    '   - Rows highlighted in GREEN (rte) are RTE students — all amounts are usually 0.',
    '',
    'COLUMNS EXPLAINED',
    '─────────────────────────────────────────────────────────',
    '',
    '  class_name *       : Must match exactly — Nursery / Lower KG / Upper KG / Class 1 … Class 8',
    '  fee_category *     : general / rte / coc / discount  (lowercase)',
    '  admission_fee      : One-time fee charged at admission (per year / per student)',
    '  tuition_fee (boys) : Annual tuition fee for boys (or all students if no gender split)',
    '                       Girls tuition fee is auto-calculated at 75% of boys rate for general category.',
    '  transport_fee      : Annual transport fee (0 if not applicable)',
    '  other_fee          : Any other annual charge (books, uniform fund, etc.)',
    '',
    '  Total fee is auto-calculated by the system — do NOT add a total column.',
    '  Girls tuition fee is auto-calculated — do NOT add it to the template.',
    '',
    'UPLOADING',
    '─────────────────────────────────────────────────────────',
    '',
    '  - Save this file as .xlsx before uploading.',
    '  - Go to: Admin → Fees → Fee Structures → Import Fee Structure',
    '  - Preview screen will show any validation errors row by row.',
    '  - Existing rows for the same class + category are UPDATED (safe to re-upload after changes).',
    '  - New rows are INSERTED.',
    '',
    'NOTES',
    '─────────────────────────────────────────────────────────',
    '',
    '  - You can leave entire rows as-is if you have not decided the amounts yet.',
    '    Rows with 0 in all amount columns will still be imported.',
    '  - You can delete rows for categories not used by your school (e.g. if no COC students, delete those rows).',
    '  - After uploading, any row can be edited individually from Fee Structures → Edit.',
];

foreach ($lines as $i => $line) {
    $row = $i + 2;
    $info->setCellValue('A' . $row, $line);
    if (in_array($line, ['HOW TO USE THIS TEMPLATE', 'COLUMNS EXPLAINED', 'UPLOADING', 'NOTES'])) {
        $info->getStyle('A' . $row)->getFont()->setBold(true);
    }
    $info->getRowDimension($row)->setRowHeight(15);
}

// ── Sheet 3: Legend ───────────────────────────────────────────────────────────

$legend = $spreadsheet->createSheet();
$legend->setTitle('Colour Legend');
$legend->getColumnDimension('A')->setWidth(20);
$legend->getColumnDimension('B')->setWidth(60);

$legend->setCellValue('A1', 'Colour');
$legend->setCellValue('B1', 'Meaning');
$legend->getStyle('A1:B1')->getFont()->setBold(true);

$legendRows = [
    ['EBF5FB', 'General category — standard fee-paying students. Girls tuition auto-calculated at 75%.'],
    ['EAFAF1', 'RTE — Right to Education students, fees are typically 0 (government reimbursed)'],
    ['FEF9E7', 'Discount — students on fee concession, reduced from general rate'],
    ['FDEDEC', 'COC — Centre of Change / internal transfer, fees shown as 0 (internal accounting)'],
];

foreach ($legendRows as $i => $lr) {
    $row = $i + 2;
    $legend->setCellValue('A' . $row, '');
    $legend->setCellValue('B' . $row, $lr[1]);
    $legend->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($lr[0]);
    $legend->getStyle('A' . $row . ':B' . $row)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('AEB6BF');
    $legend->getRowDimension($row)->setRowHeight(18);
}

// ── Save ──────────────────────────────────────────────────────────────────────

$spreadsheet->setActiveSheetIndex(0);

$writer = new Xlsx($spreadsheet);
$writer->save('FeeStructureTemplate.xlsx');

echo "FeeStructureTemplate.xlsx created successfully.\n";
