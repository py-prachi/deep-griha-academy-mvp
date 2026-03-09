<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class AddFeeCategoryToFeeStructuresTable extends Migration
{
    public function up()
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->enum('fee_category', ['general', 'rte', 'coc', 'discount'])
                  ->default('general')->after('academic_year');
            $table->unsignedBigInteger('session_id')->nullable()->after('fee_category');
        });

        if (DB::getDriverName() === 'mysql') {
            // Drop foreign key on fee_payments that references fee_structures first
            DB::statement('ALTER TABLE fee_payments DROP FOREIGN KEY fee_payments_fee_structure_id_foreign');
            // Now safe to drop the unique index
            DB::statement('ALTER TABLE fee_structures DROP INDEX fee_structures_class_id_academic_year_unique');
            // Recreate with fee_category included
            DB::statement('ALTER TABLE fee_structures ADD UNIQUE KEY fee_structures_class_academic_category_unique (class_id, academic_year, fee_category)');
            // Restore the foreign key
            DB::statement('ALTER TABLE fee_payments ADD CONSTRAINT fee_payments_fee_structure_id_foreign FOREIGN KEY (fee_structure_id) REFERENCES fee_structures (id) ON DELETE CASCADE');
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            // Drop foreign key first
            DB::statement('ALTER TABLE fee_payments DROP FOREIGN KEY fee_payments_fee_structure_id_foreign');
            // Drop new unique index
            DB::statement('ALTER TABLE fee_structures DROP INDEX fee_structures_class_academic_category_unique');
            // Restore original unique index
            DB::statement('ALTER TABLE fee_structures ADD UNIQUE KEY fee_structures_class_id_academic_year_unique (class_id, academic_year)');
            // Restore foreign key
            DB::statement('ALTER TABLE fee_payments ADD CONSTRAINT fee_payments_fee_structure_id_foreign FOREIGN KEY (fee_structure_id) REFERENCES fee_structures (id) ON DELETE CASCADE');
        }

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn(['fee_category', 'session_id']);
        });
    }
}
