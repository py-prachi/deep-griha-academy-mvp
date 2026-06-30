<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeStudents extends Command
{
    protected $signature   = 'students:wipe {--force : Skip confirmation prompt}';
    protected $description = 'Remove ALL student accounts and related data (staging use only)';

    public function handle()
    {
        $count = DB::table('users')->where('role', 'student')->count();

        if ($count === 0) {
            $this->info('No student records found. Nothing to wipe.');
            return 0;
        }

        $this->warn("This will permanently delete {$count} student(s) and ALL related data:");
        $this->line('  admissions, promotions, fee payments, attendance, counselling, marks, exits…');

        if (!$this->option('force') && !$this->confirm('Continue?', false)) {
            $this->line('Aborted.');
            return 0;
        }

        $studentIds = DB::table('users')->where('role', 'student')->pluck('id');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('counselling_remarks')
            ->whereIn('counselling_id',
                DB::table('student_counsellings')->whereIn('student_user_id', $studentIds)->pluck('id')
            )->delete();
        DB::table('student_counsellings')->whereIn('student_user_id', $studentIds)->delete();

        $admissionIds = DB::table('admissions')->whereIn('student_user_id', $studentIds)->pluck('id');
        DB::table('leaving_certificates')->whereIn('admission_id', $admissionIds)->delete();
        DB::table('student_exits')->whereIn('admission_id', $admissionIds)->delete();
        DB::table('admission_documents')->whereIn('admission_id', $admissionIds)->delete();
        DB::table('admissions')->whereIn('id', $admissionIds)->delete();

        DB::table('fee_payments')->whereIn('student_user_id', $studentIds)->delete();
        DB::table('attendances')->whereIn('student_id', $studentIds)->delete();
        DB::table('marks')->whereIn('student_id', $studentIds)->delete();
        DB::table('final_marks')->whereIn('student_id', $studentIds)->delete();
        DB::table('student_term_marks')->whereIn('student_id', $studentIds)->delete();
        DB::table('preprimary_skill_grades')->whereIn('student_id', $studentIds)->delete();
        DB::table('student_observations')->whereIn('student_id', $studentIds)->delete();
        DB::table('student_academic_infos')->whereIn('student_id', $studentIds)->delete();
        DB::table('student_parent_infos')->whereIn('student_id', $studentIds)->delete();

        DB::table('promotions')->whereIn('student_id', $studentIds)->delete();
        DB::table('users')->whereIn('id', $studentIds)->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info("Done. {$count} student(s) and all related records removed.");
        return 0;
    }
}
