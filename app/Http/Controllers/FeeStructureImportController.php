<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Repositories\FeeStructureRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $templatePath = base_path('FeeStructureTemplate.xlsx');
        if (!file_exists($templatePath)) {
            return back()->withErrors(['file' => 'Template file not found on server.']);
        }
        return response()->download($templatePath, 'FeeStructureTemplate.xlsx');
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
                'class_name'    => trim((string) ($row[0] ?? '')),
                'fee_category'  => strtolower(trim((string) ($row[1] ?? ''))),
                'tuition_fee'   => trim((string) ($row[2] ?? '')),
                'transport_fee' => trim((string) ($row[3] ?? '')),
                'other_fee'     => trim((string) ($row[4] ?? '')),
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

            foreach (['tuition_fee', 'transport_fee', 'other_fee'] as $field) {
                if ($d[$field] !== '' && !is_numeric($d[$field])) {
                    $errors[] = $field . ' must be a number';
                }
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
                $compareFields = [
                    'tuition_fee'   => (float) ($d['tuition_fee']   !== '' ? $d['tuition_fee']   : 0),
                    'transport_fee' => (float) ($d['transport_fee'] !== '' ? $d['transport_fee'] : 0),
                    'other_fee'     => (float) ($d['other_fee']     !== '' ? $d['other_fee']     : 0),
                ];
                foreach ($compareFields as $field => $newVal) {
                    $oldVal = (float) $existing->{$field};
                    if ($oldVal !== $newVal) {
                        $diff[$field] = ['old' => $oldVal, 'new' => $newVal];
                    }
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
                    'transport_fee'     => $d['transport_fee'] !== '' ? $d['transport_fee'] : 0,
                    'other_fee'         => $d['other_fee']     !== '' ? $d['other_fee']     : 0,
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
