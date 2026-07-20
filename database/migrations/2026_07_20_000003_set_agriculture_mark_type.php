<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agriculture moves from a bare "grade_only" entry (single grade, no
 * breakdown) to "oral_practical_obs_hw" — Oral/Practical/Overall
 * Observation/Homework marks that sum to a total, with an auto-derived
 * grade and a remark, matching the school's existing Agriculture marks
 * register.
 */
class SetAgricultureMarkType extends Migration
{
    public function up()
    {
        DB::table('subjects')
            ->where('name', 'Agriculture')
            ->update(['mark_type' => 'oral_practical_obs_hw']);
    }

    public function down()
    {
        DB::table('subjects')
            ->where('name', 'Agriculture')
            ->update(['mark_type' => 'grade_only']);
    }
}
