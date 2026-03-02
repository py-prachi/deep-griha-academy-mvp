<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolStructureSeeder extends Seeder
{
    public function run()
    {
        $sessionId = DB::table('school_sessions')->insertGetId([
            'session_name' => '2025-2026',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('semesters')->insert([
            [
                'semester_name' => 'Semester 1',
                'start_date'    => '2025-04-01',
                'end_date'      => '2025-10-31',
                'session_id'    => $sessionId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'semester_name' => 'Semester 2',
                'start_date'    => '2025-11-01',
                'end_date'      => '2026-03-31',
                'session_id'    => $sessionId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);

        $classes = [
            'Nursery', 'Lower KG', 'Upper KG',
            'Class 1', 'Class 2', 'Class 3', 'Class 4',
            'Class 5', 'Class 6', 'Class 7', 'Class 8',
        ];

        $classIds = [];
        foreach ($classes as $className) {
            $classIds[$className] = DB::table('school_classes')->insertGetId([
                'class_name' => $className,
                'session_id' => $sessionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sectionSetup = [
            'Nursery'   => [['A', '101']],
            'Lower KG'  => [['A', '102']],
            'Upper KG'  => [['A', '103']],
            'Class 1'   => [['A', '104']],
            'Class 2'   => [['A', '105']],
            'Class 3'   => [['A', '106']],
            'Class 4'   => [['A', '107']],
            'Class 5'   => [['A', '108']],
            'Class 6'   => [['A', '109'], ['B', '110']],
            'Class 7'   => [['A', '111']],
            'Class 8'   => [['A', '112']],
        ];

        foreach ($sectionSetup as $className => $sections) {
            foreach ($sections as [$sectionName, $roomNo]) {
                DB::table('sections')->insert([
                    'section_name' => $sectionName,
                    'room_no'      => $roomNo,
                    'class_id'     => $classIds[$className],
                    'session_id'   => $sessionId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        $this->command->info('✅ School structure seeded.');
        $this->command->info('   Session  : 2025-2026');
        $this->command->info('   Classes  : Nursery, LKG, UKG, Class 1–8');
        $this->command->info('   Sections : Section A for all (+ B for Class 6)');
        $this->command->info('   Semesters: Apr–Oct + Nov–Mar');
    }
}
