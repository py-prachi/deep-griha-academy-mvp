<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;

class ImportDgaFees extends Command
{
    protected $signature   = 'fees:import-dga {file? : Path to Excel file}';
    protected $description = 'Import already-collected fee payments from DGA Excel file (one challan per student)';

    const CLASS_MAP = [
        'Nursery' => 'Nursery',
        'LKG'     => 'Lower KG',
        'UKG'     => 'Upper KG',
        '1st'     => 'Class 1',
        '2nd'     => 'Class 2',
        '3rd'     => 'Class 3',
        '4th'     => 'Class 4',
        '5th'     => 'Class 5',
        '6th'     => 'Class 6',
        '7th'     => 'Class 7',
        '8th'     => 'Class 8',
    ];

    public function handle()
    {
        $filePath = $this->argument('file') ?? '/var/www/schoolDocs/DGA_students.xlsx';

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $session   = DB::table('school_sessions')->orderByDesc('id')->first();
        $admin     = DB::table('users')->where('role', 'admin')->orderBy('id')->first();
        $nextChal  = ((int) DB::table('fee_payments')->max('challan_no')) + 1;

        // Build tab → class_id map
        $classIdMap = [];
        foreach (self::CLASS_MAP as $tab => $className) {
            $class = DB::table('school_classes')->where('class_name', $className)->orderByDesc('id')->first();
            if ($class) $classIdMap[$tab] = $class->id;
        }

        $this->info("Reading: {$filePath}");
        $spreadsheet = IOFactory::load($filePath);

        $imported = 0;
        $skipped  = 0;
        $noFee    = 0;

        foreach ($spreadsheet->getSheetNames() as $tabName) {
            if (!isset($classIdMap[$tabName])) {
                $this->warn("  Tab '{$tabName}' — no class mapping, skipped.");
                continue;
            }

            $classId  = $classIdMap[$tabName];
            $ws       = $spreadsheet->getSheetByName($tabName);
            $tabCount = 0;

            foreach ($ws->getRowIterator(3) as $row) {
                $r   = $row->getRowIndex();
                $sr  = trim((string) $ws->getCell('A' . $r)->getValue());
                $name = preg_replace('/\s+/', ' ', trim((string) $ws->getCell('B' . $r)->getValue()));

                if (!is_numeric($sr) || $name === '') continue;

                // ── Collected fee (H column — may be formula for COC) ──
                $collectedRaw = $ws->getCell('H' . $r)->getCalculatedValue();
                $collected    = is_numeric($collectedRaw) ? (float) $collectedRaw : 0;

                if ($collected <= 0) {
                    $noFee++;
                    continue; // RTE/nothing paid — skip
                }

                // ── Find the student's admission ──────────────────────
                $admission = DB::table('admissions')
                    ->where('student_name', $name)
                    ->where('class_id', $classId)
                    ->first();

                if (!$admission || !$admission->student_user_id) {
                    $this->warn("  Not found in DB: '{$name}' ({$tabName})");
                    $skipped++;
                    continue;
                }

                // ── Payment date (K column) ───────────────────────────
                $dateRaw     = $ws->getCell('K' . $r)->getCalculatedValue();
                $paymentDate = $this->parseDate($dateRaw);

                // ── Challan number (J column) ─────────────────────────
                $challanRaw = trim((string) $ws->getCell('J' . $r)->getValue());
                if (is_numeric($challanRaw) && (int) $challanRaw > 0) {
                    $challanNo = (int) $challanRaw;
                    // Avoid collision with auto-sequence
                    if ($challanNo >= $nextChal) $nextChal = $challanNo + 1;
                    // If already used, fall back to auto
                    if (DB::table('fee_payments')->where('challan_no', $challanNo)->exists()) {
                        $challanNo = $nextChal++;
                    }
                } else {
                    $challanNo = $nextChal++;
                }

                DB::beginTransaction();
                try {
                    $paymentId = DB::table('fee_payments')->insertGetId([
                        'student_user_id' => $admission->student_user_id,
                        'session_id'      => $session->id,
                        'challan_no'      => $challanNo,
                        'payment_date'    => $paymentDate,
                        'amount_paid'     => $collected,
                        'payment_mode'    => 'cash',
                        'payment_category'=> 'fee',
                        'recorded_by'     => $admin->id,
                        'notes'           => 'Imported from school records (2025-26)',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    DB::table('fee_line_items')->insert([
                        'fee_payment_id' => $paymentId,
                        'description'    => 'tuition_fee',
                        'amount'         => $collected,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);

                    DB::commit();
                    $tabCount++;
                    $imported++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->warn("  Failed for '{$name}': " . $e->getMessage());
                    $skipped++;
                }
            }

            $this->info("  {$tabName}: {$tabCount} payment(s) imported");
        }

        $this->newLine();
        $this->info("Done — {$imported} payment(s) created, {$noFee} skipped (zero/RTE), {$skipped} errors.");
        return 0;
    }

    private function parseDate($raw): string
    {
        if (is_numeric($raw) && $raw > 1000) {
            // Excel serial date
            try {
                return XlDate::excelToDateTimeObject($raw)->format('Y-m-d');
            } catch (\Exception $e) {}
        }
        if (is_string($raw) && strlen($raw) >= 6) {
            try {
                return \Carbon\Carbon::parse($raw)->toDateString();
            } catch (\Exception $e) {}
        }
        return now()->toDateString();
    }
}
