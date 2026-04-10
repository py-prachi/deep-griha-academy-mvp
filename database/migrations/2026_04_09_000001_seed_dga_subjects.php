<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Replace placeholder subjects with the 14 official Deep Griha Academy subjects
 * as seen on the Class 1–8 report card.
 *
 * mark_type:
 *  'marks'      – full oral/written breakdown (English, Marathi, Hindi, Maths, Gen.Science&EVS, Social Science)
 *  'grade_only' – direct grade entry only (PE, Library, Computer, Agriculture, Vocational Studies, Vocal, Tabla, Dance)
 */
class SeedDgaSubjects extends Migration
{
    public function up()
    {
        // Remove old placeholder subjects
        DB::table('subject_teachers')->delete();
        DB::table('class_subjects')->delete();
        if (\Illuminate\Support\Facades\Schema::hasTable('student_term_marks')) {
            DB::table('student_term_marks')->delete();
        }
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('subjects')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else {
            DB::table('subjects')->delete();
        }

        $subjects = [
            ['name' => 'English',                'code' => 'ENG', 'sort_order' => 1,  'mark_type' => 'marks',      'is_active' => 1],
            ['name' => 'Marathi',                'code' => 'MAR', 'sort_order' => 2,  'mark_type' => 'marks',      'is_active' => 1],
            ['name' => 'Hindi',                  'code' => 'HIN', 'sort_order' => 3,  'mark_type' => 'marks',      'is_active' => 1],
            ['name' => 'Mathematics',            'code' => 'MTH', 'sort_order' => 4,  'mark_type' => 'marks',      'is_active' => 1],
            ['name' => 'General Science & EVS',  'code' => 'GSE', 'sort_order' => 5,  'mark_type' => 'marks',      'is_active' => 1],
            ['name' => 'Social Science',         'code' => 'SST', 'sort_order' => 6,  'mark_type' => 'marks',      'is_active' => 1],
            ['name' => 'Physical Education',     'code' => 'PE',  'sort_order' => 7,  'mark_type' => 'grade_only', 'is_active' => 1],
            ['name' => 'Library',                'code' => 'LIB', 'sort_order' => 8,  'mark_type' => 'grade_only', 'is_active' => 1],
            ['name' => 'Computer',               'code' => 'COM', 'sort_order' => 9,  'mark_type' => 'grade_only', 'is_active' => 1],
            ['name' => 'Agriculture',            'code' => 'AGR', 'sort_order' => 10, 'mark_type' => 'grade_only', 'is_active' => 1],
            ['name' => 'Vocational Studies',     'code' => 'VOC', 'sort_order' => 11, 'mark_type' => 'grade_only', 'is_active' => 1],
            ['name' => 'Vocal',                  'code' => 'VCL', 'sort_order' => 12, 'mark_type' => 'grade_only', 'is_active' => 1],
            ['name' => 'Tabla',                  'code' => 'TBL', 'sort_order' => 13, 'mark_type' => 'grade_only', 'is_active' => 1],
            ['name' => 'Dance',                  'code' => 'DNC', 'sort_order' => 14, 'mark_type' => 'grade_only', 'is_active' => 1],
        ];

        $now = now();
        foreach ($subjects as &$s) {
            $s['created_at'] = $now;
            $s['updated_at'] = $now;
        }

        DB::table('subjects')->insert($subjects);
    }

    public function down()
    {
        // Nothing to restore — subjects were replaced
        DB::table('subject_teachers')->delete();
        DB::table('class_subjects')->delete();
        if (\Illuminate\Support\Facades\Schema::hasTable('student_term_marks')) {
            DB::table('student_term_marks')->delete();
        }
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('subjects')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else {
            DB::table('subjects')->delete();
        }
    }
}
