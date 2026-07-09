<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgricultureFieldsToPlanLessons extends Migration
{
    public function up()
    {
        Schema::table('plan_lessons', function (Blueprint $table) {
            // Practical / Theory type indicator for agriculture plans
            $table->string('lesson_type', 50)->nullable()->after('period_id');
            // Description of the practical activity
            $table->text('practical_notes')->nullable()->after('lesson_type');
        });
    }

    public function down()
    {
        Schema::table('plan_lessons', function (Blueprint $table) {
            $table->dropColumn(['lesson_type', 'practical_notes']);
        });
    }
}
