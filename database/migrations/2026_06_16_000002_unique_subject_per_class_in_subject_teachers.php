<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UniqueSubjectPerClassInSubjectTeachers extends Migration
{
    public function up()
    {
        // Remove older duplicate rows, keeping the most recent assignment per subject+class+section+session
        DB::statement("
            DELETE st1 FROM subject_teachers st1
            INNER JOIN subject_teachers st2
                ON  st1.subject_id  = st2.subject_id
                AND st1.class_id    = st2.class_id
                AND st1.section_id  = st2.section_id
                AND st1.session_id  = st2.session_id
                AND st1.id < st2.id
        ");

        // Drop FK that relies on subj_teacher_unique, drop the old unique key, then recreate both
        DB::statement("ALTER TABLE subject_teachers DROP FOREIGN KEY subject_teachers_teacher_id_foreign");
        DB::statement("ALTER TABLE subject_teachers DROP INDEX subj_teacher_unique");

        // One teacher per subject per class+section+session
        DB::statement("ALTER TABLE subject_teachers ADD UNIQUE KEY subj_class_unique (subject_id, class_id, section_id, session_id)");

        // Restore the FK on teacher_id (backed by its own index now)
        DB::statement("ALTER TABLE subject_teachers ADD INDEX idx_teacher_id (teacher_id)");
        DB::statement("ALTER TABLE subject_teachers ADD CONSTRAINT subject_teachers_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE");
    }

    public function down()
    {
        DB::statement("ALTER TABLE subject_teachers DROP FOREIGN KEY subject_teachers_teacher_id_foreign");
        DB::statement("ALTER TABLE subject_teachers DROP INDEX idx_teacher_id");
        DB::statement("ALTER TABLE subject_teachers DROP INDEX subj_class_unique");
        DB::statement("ALTER TABLE subject_teachers ADD UNIQUE KEY subj_teacher_unique (teacher_id, subject_id, class_id, section_id, session_id)");
        DB::statement("ALTER TABLE subject_teachers ADD CONSTRAINT subject_teachers_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE");
    }
}
