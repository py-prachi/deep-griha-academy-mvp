<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Repositories\FeeStructureRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FeeStructureImportController extends Controller
{
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

    public function showForm()
    {
        return view('fee-structures.import');
    }

    public function downloadTemplate()
    {
        // Load all classes for the current session, ordered by id
        $session = SchoolSession::orderBy('id', 'desc')->first();
        $classes = $session
            ? SchoolClass::where('session_id', $session->id)->orderBy('id')->pluck('class_name')->toArray()
            : [];

        $categories = [
            'general' => 16200,
            'rte'     => 16200,
            'coc'     => 16000,
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fee Structure');

        // Header row
        $sheet->setCellValue('A1', 'Class Name');
        $sheet->setCellValue('B1', 'Fee Category');
        $sheet->setCellValue('C1', 'Tuition Fee');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        // One row per class × category
        $row = 2;
        foreach ($classes as $className) {
            foreach ($categories as $cat => $fee) {
                $sheet->setCellValue("A{$row}", $className);
                $sheet->setCellValue("B{$row}", $cat);
                $sheet->setCellValue("C{$row}", $fee);
                $row++;
            }
        }

        // Note row (one blank row gap)
        $noteRow = $row + 1;
        $sheet->setCellValue("A{$noteRow}", 'Fee categories: general / rte / coc   |   Girls tuition fee is auto-calculated (75% of general + Rs.50)   |   Transport and other fees default to 0');
        $sheet->getStyle("A{$noteRow}")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF888888'));
        $sheet->mergeCells("A{$noteRow}:C{$noteRow}");

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(16);

        $writer = new Xlsx($spreadsheet);
        $filename = 'FeeStructureTemplate.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);

        $path = $request->file('file')->getPathname();

        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Could not read the Excel file. Upload the correct template (.xlsx).']);
        }

        $rawRows = $sheet->toArray(null, true, true, false);
        array_shift($rawRows); // remove header row

        $classMap = SchoolClass::pluck('id', 'class_name')->toArray();
        $session  = SchoolSession::orderBy('id', 'desc')->first();

        $parsed = [];

        foreach ($rawRows as $rowIndex => $row) {
            $lineNo = $rowIndex + 2;

            // Skip blank rows
            $empty = true;
            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') { $empty = false; break; }
            }
            if ($empty) continue;

            $d = [
                'class_name'   => trim((string) ($row[0] ?? '')),
                'fee_category' => strtolower(trim((string) ($row[1] ?? ''))),
                'tuition_fee'  => trim((string) ($row[2] ?? '')),
            ];

            $errors = [];

            if ($d['class_name'] === '') {
                $errors[] = 'class_name is required';
            } elseif (!isset($classMap[$d['class_name']])) {
                $errors[] = 'Class "' . $d['class_name'] . '" not recognised — must match exactly';
            } else {
                $d['class_id'] = $classMap[$d['class_name']];
            }

            if ($d['fee_category'] === '') {
                $errors[] = 'fee_category is required';
            } elseif (!in_array($d['fee_category'], ['general', 'rte', 'coc'])) {
                $errors[] = 'fee_category must be general / rte / coc';
            }

            if ($d['tuition_fee'] !== '' && !is_numeric($d['tuition_fee'])) {
                $errors[] = 'tuition_fee must be a number';
            }

            // Fetch existing record for diff comparison
            $existing = null;
            if (isset($d['class_id']) && $d['fee_category'] !== '' && $session) {
                $existing = FeeStructure::where('class_id', $d['class_id'])
                    ->where('academic_year', $session->session_name)
                    ->where('fee_category', $d['fee_category'])
                    ->first();
            }

            $d['session_id']    = $session ? $session->id : null;
            $d['academic_year'] = $session ? $session->session_name : '';
            $d['is_update']     = $existing !== null;

            // Build field-level diff for update rows
            $diff = [];
            if ($existing) {
                $newTuition = (float) ($d['tuition_fee'] !== '' ? $d['tuition_fee'] : 0);
                $oldTuition = (float) $existing->tuition_fee;
                if ($oldTuition !== $newTuition) {
                    $diff['tuition_fee'] = ['old' => $oldTuition, 'new' => $newTuition];
                }
            }
            $d['diff'] = $diff;

            $parsed[] = [
                'line'   => $lineNo,
                'data'   => $d,
                'errors' => $errors,
                'status' => count($errors) > 0 ? 'error' : 'valid',
            ];
        }

        if (empty($parsed)) {
            return back()->withErrors(['file' => 'No data rows found in the file.']);
        }

        $importable = array_values(array_filter($parsed, fn($r) => $r['status'] === 'valid'));
        session(['fee_import_preview' => $importable]);

        $validCount = count($importable);
        $errorCount = count($parsed) - $validCount;

        return view('fee-structures.import', compact('parsed', 'validCount', 'errorCount'));
    }

    public function commit(Request $request)
    {
        $rows = session('fee_import_preview');

        if (empty($rows)) {
            return redirect()->route('fee-structures.import')
                ->withErrors(['file' => 'Preview session expired — please re-upload the file.']);
        }

        $repo = new FeeStructureRepository();
        $imported = 0;
        $updated  = 0;
        $failed   = 0;

        foreach ($rows as $row) {
            $d = $row['data'];
            try {
                $tuitionFee = $d['tuition_fee'] !== '' ? (float) $d['tuition_fee'] : 0;
                $repo->store([
                    'class_id'          => $d['class_id'],
                    'session_id'        => $d['session_id'],
                    'academic_year'     => $d['academic_year'],
                    'fee_category'      => $d['fee_category'],
                    'tuition_fee'       => $tuitionFee,
                    'girls_tuition_fee' => $d['fee_category'] === 'general' ? round($tuitionFee * 0.75) + 50 : null,
                    'transport_fee'     => 0,
                    'other_fee'         => 0,
                ]);
                $d['is_update'] ? $updated++ : $imported++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        session()->forget('fee_import_preview');

        $msg = $imported . ' row(s) added, ' . $updated . ' row(s) updated.';
        if ($failed) $msg .= ' ' . $failed . ' row(s) failed.';

        return redirect()->route('fee-structures.index')->with('status', $msg);
    }
}
