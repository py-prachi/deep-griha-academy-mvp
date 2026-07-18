<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUnitTest4ToDiagnosticResultsEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE diagnostic_results MODIFY COLUMN assessment_type ENUM(
            'diagnostic', 'unit_test_1', 'unit_test_2',
            'first_term', 'unit_test_3', 'second_term', 'unit_test_4', 'annual'
        )");
    }

    public function down()
    {
        DB::statement("DELETE FROM diagnostic_results WHERE assessment_type = 'unit_test_4'");
        DB::statement("ALTER TABLE diagnostic_results MODIFY COLUMN assessment_type ENUM(
            'diagnostic', 'unit_test_1', 'unit_test_2',
            'first_term', 'unit_test_3', 'second_term', 'annual'
        )");
    }
}
