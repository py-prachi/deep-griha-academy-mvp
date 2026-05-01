<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActivityFieldsToEventsTable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('activity_type')->nullable()->after('title');
            $table->text('description')->nullable()->after('activity_type');
            $table->text('purpose')->nullable()->after('description');
            $table->string('location')->nullable()->after('purpose');
            $table->string('duration')->nullable()->after('location');
            $table->string('participants')->nullable()->after('duration');
            $table->unsignedInteger('participant_count')->nullable()->after('participants');
            $table->text('skills_values')->nullable()->after('participant_count');
            $table->string('photo_url')->nullable()->after('skills_values');
            $table->text('outcome')->nullable()->after('photo_url');
            $table->unsignedBigInteger('created_by')->nullable()->after('outcome');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'activity_type', 'description', 'purpose', 'location', 'duration',
                'participants', 'participant_count', 'skills_values', 'photo_url',
                'outcome', 'created_by',
            ]);
        });
    }
}
