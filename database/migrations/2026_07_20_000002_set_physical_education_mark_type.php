<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Physical Education moves from a bare "grade_only" entry (single grade,
 * no breakdown) to "oral_practical_project" — Oral/Practical/Project marks
 * that sum to a total, with an auto-derived grade and a remark, matching
 * the school's existing Sports marks register.
 */
class SetPhysicalEducationMarkType extends Migration
{
    public function up()
    {
        DB::table('subjects')
            ->where('name', 'Physical Education')
            ->update(['mark_type' => 'oral_practical_project']);
    }

    public function down()
    {
        DB::table('subjects')
            ->where('name', 'Physical Education')
            ->update(['mark_type' => 'grade_only']);
    }
}
