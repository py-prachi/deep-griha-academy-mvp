<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'take attendances', 'view attendances', 'update attendances type',
            'create assignments', 'view assignments', 'submit assignments',
            'save marks', 'view marks', 'update marks submission window',
            'create users', 'view users', 'edit users', 'delete users', 'promote students',
            'create notices', 'view notices', 'edit notices', 'delete notices',
            'create events', 'view events', 'edit events', 'delete events',
            'create syllabi', 'view syllabi', 'edit syllabi', 'delete syllabi',
            'create routines', 'view routines', 'edit routines', 'delete routines',
            'create exams', 'view exams', 'delete exams', 'view exams history',
            'create exams rule', 'view exams rule', 'edit exams rule', 'delete exams rule',
            'create grading systems', 'view grading systems', 'edit grading systems', 'delete grading systems',
            'create grading systems rule', 'view grading systems rule', 'edit grading systems rule', 'delete grading systems rule',
            'view academic settings', 'create school sessions', 'create semesters', 'view semesters', 'edit semesters', 'update browse by session',
            'create courses', 'view courses', 'edit courses',
            'create classes', 'view classes', 'edit classes',
            'create sections', 'view sections', 'edit sections',
            'assign teachers',
            'manage admissions',
            'manage fee structures',
            'record fee payments',
            'view fee reports',
            'issue leaving certificates',
            'view leaving certificates',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole   = Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $adminRole->syncPermissions(Permission::all());

        $teacherRole->syncPermissions([
            'take attendances', 'view attendances',
            'create assignments', 'view assignments',
            'save marks', 'view marks',
            'view syllabi', 'view routines',
            'create exams', 'view exams',
            'create exams rule', 'view exams rule', 'edit exams rule',
            'view exams history',
            'view grading systems', 'view grading systems rule',
            'view courses',
            'view leaving certificates',
        ]);

        $this->command->info('✅ Roles and permissions seeded.');
    }
}
