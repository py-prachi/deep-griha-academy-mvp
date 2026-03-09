<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class AddFeeCategoryToFeeStructuresTable extends Migration
{
    public function up()
    {
        Schema::table("fee_structures", function (Blueprint $table) {
            $table->enum("fee_category", ["general", "rte", "coc", "discount"])
                  ->default("general")->after("academic_year");
            $table->unsignedBigInteger("session_id")->nullable()->after("fee_category");
        });
        if (DB::getDriverName() === "mysql") {
            // Drop class_id FK so unique index can be modified
            Schema::table("fee_structures", function (Blueprint $table) {
                $table->dropForeign(["class_id"]);
            });
            DB::statement("ALTER TABLE fee_structures DROP INDEX fee_structures_class_id_academic_year_unique");
            DB::statement("ALTER TABLE fee_structures ADD UNIQUE KEY fee_structures_class_academic_category_unique (class_id, academic_year, fee_category)");
            // Restore class_id FK
            Schema::table("fee_structures", function (Blueprint $table) {
                $table->foreign("class_id")->references("id")->on("school_classes")->onDelete("cascade");
            });
        }
    }
    public function down()
    {
        if (DB::getDriverName() === "mysql") {
            Schema::table("fee_structures", function (Blueprint $table) {
                $table->dropForeign(["class_id"]);
            });
            DB::statement("ALTER TABLE fee_structures DROP INDEX fee_structures_class_academic_category_unique");
            DB::statement("ALTER TABLE fee_structures ADD UNIQUE KEY fee_structures_class_id_academic_year_unique (class_id, academic_year)");
            Schema::table("fee_structures", function (Blueprint $table) {
                $table->foreign("class_id")->references("id")->on("school_classes")->onDelete("cascade");
            });
        }
        Schema::table("fee_structures", function (Blueprint $table) {
            $table->dropColumn(["fee_category", "session_id"]);
        });
    }
}
