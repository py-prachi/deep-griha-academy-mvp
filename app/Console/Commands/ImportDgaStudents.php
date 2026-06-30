<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDgaStudents extends Command
{
    protected $signature   = 'students:import-dga {file? : Path to Excel file} {--wipe : Wipe existing students first}';
    protected $description = 'Import students from DGA multi-tab Excel file into the current session';

    // Tab name → class name in DB
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

        // ── Wipe existing students if requested ──────────────────────────
        if ($this->option('wipe')) {
            $this->call('students:wipe', ['--force' => true]);
        }

        // ── Load session + class/section maps ────────────────────────────
        $session = DB::table('school_sessions')->orderByDesc('id')->first();
        if (!$session) {
            $this->error('No school session found. Please set one up first.');
            return 1;
        }
        $this->info("Importing into session: {$session->session_name} (id={$session->id})");

        // Build class_name → {class_id, section_id} map
        $classMap = [];
        foreach (self::CLASS_MAP as $tab => $className) {
            $class = DB::table('school_classes')->where('class_name', $className)->orderByDesc('id')->first();
            if (!$class) {
                $this->warn("Class '{$className}' not found in DB — skipping tab '{$tab}'");
                continue;
            }
            $section = DB::table('sections')->where('class_id', $class->id)->orderBy('id')->first();
            if (!$section) {
                $this->warn("No section for class '{$className}' — skipping tab '{$tab}'");
                continue;
            }
            $classMap[$tab] = [
                'class_id'   => $class->id,
                'class_name' => $className,
                'section_id' => $section->id,
            ];
        }

        // ── Read Excel ───────────────────────────────────────────────────
        $this->info("Reading: {$filePath}");
        $spreadsheet = IOFactory::load($filePath);

        $imported = 0;
        $skipped  = 0;

        foreach ($spreadsheet->getSheetNames() as $tabName) {
            if (!isset($classMap[$tabName])) {
                $this->warn("  Tab '{$tabName}' — no class mapping, skipped.");
                continue;
            }

            $classInfo = $classMap[$tabName];
            $ws        = $spreadsheet->getSheetByName($tabName);
            $tabCount  = 0;

            foreach ($ws->getRowIterator(3) as $row) {
                $cells = [];
                foreach ($row->getCellIterator() as $col => $cell) {
                    $cells[$col] = trim((string) $cell->getValue());
                }

                $sr   = $cells['A'] ?? '';
                $name = $cells['B'] ?? '';

                if (!is_numeric($sr) || $name === '') {
                    continue;
                }

                $name   = preg_replace('/\s+/', ' ', $name); // collapse extra spaces
                $gender = strtolower($cells['D'] ?? '') === 'girl' ? 'female' : 'male';
                $phone  = preg_replace('/\D/', '', $cells['C'] ?? '');
                $phone  = strlen($phone) >= 10 ? substr($phone, -10) : null;

                $feeCategory = $this->parseFeeCategory($cells['G'] ?? '');
                $village     = $cells['M'] ?? null;
                $distance    = $cells['N'] ?? null;
                $fatherOcc   = $cells['O'] ?? null;
                $motherOcc   = $cells['P'] ?? null;

                // Split name: first word = first_name, rest = last_name
                $nameParts = explode(' ', $name, 2);
                $firstName = $nameParts[0];
                $lastName  = $nameParts[1] ?? '';

                // Generate a unique email
                $emailBase = strtolower(preg_replace('/[^a-z0-9]/i', '', $name));
                $email     = $emailBase . '.' . $classInfo['class_id'] . '.' . $sr . '@dga.local';

                DB::beginTransaction();
                try {
                    // Create student user
                    $userId = DB::table('users')->insertGetId([
                        'first_name'   => $firstName,
                        'last_name'    => $lastName,
                        'email'        => $email,
                        'password'     => Hash::make('Student@123'),
                        'role'         => 'student',
                        'gender'       => $gender,
                        'phone'        => $phone ?? '',
                        'nationality'  => 'Indian',
                        'address'      => '',
                        'address2'     => '',
                        'city'         => '',
                        'zip'          => '',
                        'fee_category' => $feeCategory,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);

                    // Create admission record
                    $admissionId = DB::table('admissions')->insertGetId([
                        'status'          => 'confirmed',
                        'session_id'      => $session->id,
                        'class_id'        => $classInfo['class_id'],
                        'section_id'      => $classInfo['section_id'],
                        'academic_year'   => $session->session_name,
                        'student_user_id' => $userId,
                        'fee_category'    => $feeCategory,
                        'student_name'    => $name,
                        'gender'          => $gender,
                        'contact_mobile'  => $phone,
                        'village'         => $village ?: null,
                        'distance_from_school' => $distance ?: null,
                        'father_occupation'    => $fatherOcc ?: null,
                        'mother_occupation'    => $motherOcc ?: null,
                        'inquiry_date'    => now()->toDateString(),
                        'confirmed_date'  => now()->toDateString(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    // Create promotion (assign to current session + class + section)
                    DB::table('promotions')->insert([
                        'student_id' => $userId,
                        'session_id' => $session->id,
                        'class_id'   => $classInfo['class_id'],
                        'section_id' => $classInfo['section_id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::commit();
                    $tabCount++;
                    $imported++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->warn("  Skipped '{$name}': " . $e->getMessage());
                    $skipped++;
                }
            }

            $this->info("  {$tabName} ({$classInfo['class_name']}): {$tabCount} students imported");
        }

        $this->newLine();
        $this->info("Import complete — {$imported} imported, {$skipped} skipped.");
        $this->line("Default password for all students: Student@123");
        return 0;
    }

    private function parseFeeCategory(string $g): string
    {
        $g = strtolower(trim($g));
        if ($g === 'rte' || $g === 'free')       return 'rte';
        if ($g === 'coc')                          return 'coc';
        if (is_numeric($g) && (float) $g > 0)     return 'discount';
        return 'general';
    }
}
