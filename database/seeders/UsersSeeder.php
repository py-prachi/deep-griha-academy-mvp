<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $adminRole   = Role::findByName('admin');
        $teacherRole = Role::findByName('teacher');

        $admin = User::updateOrCreate(
            ['email' => 'admin@deepgriha.com'],
            [
                'first_name'  => 'Admin',
                'last_name'   => 'DGA',
                'gender'      => 'Male',
                'nationality' => 'Indian',
                'phone'       => '9999999999',
                'address'     => 'Deep Griha Academy, Vidyanagari',
                'address2'    => 'Deulgaon Gada, Chaufula',
                'city'        => 'Daund',
                'zip'         => '413801',
                'role'        => 'admin',
                'password'    => Hash::make('dga@admin2026'),
            ]
        );
        $admin->syncRoles([$adminRole]);

        $teachers = [
            [
                'first_name' => 'Anita',  'last_name' => 'Sharma',
                'email'      => 'anita.sharma@deepgriha.com',
                'gender'     => 'Female', 'nationality' => 'Indian',
                'phone'      => '9000000003', 'address' => '',
                'address2'   => '', 'city' => 'Daund', 'zip' => '413801',
            ],
            [
                'first_name' => 'Nikita', 'last_name' => 'Verma',
                'email'      => 'nikita.verma@deepgriha.com',
                'gender'     => 'Female', 'nationality' => 'Indian',
                'phone'      => '9999777788', 'address' => '',
                'address2'   => '', 'city' => 'Daund', 'zip' => '413801',
            ],
            [
                'first_name' => 'Neena',  'last_name' => 'Gupta',
                'email'      => 'neena.gupta@deepgriha.com',
                'gender'     => 'Female', 'nationality' => 'Indian',
                'phone'      => '9863465183', 'address' => '',
                'address2'   => '', 'city' => 'Daund', 'zip' => '413801',
            ],
        ];

        foreach ($teachers as $data) {
            $teacher = User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'role'     => 'teacher',
                    'password' => Hash::make('dga@teacher2026'),
                ])
            );
            $teacher->syncRoles([$teacherRole]);
        }

        $this->command->info('✅ Users seeded.');
        $this->command->info('   Admin   → admin@deepgriha.com / dga@admin2026');
        $this->command->info('   Teacher → [email]@deepgriha.com / dga@teacher2026');
        $this->command->warn('   ⚠️  Change passwords after first login!');
    }
}
