<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserRole::updateOrCreate(
            [
                'code' => 'admin'
            ],
            [
                'code' => 'admin',
                'name' => 'Administrador'
            ]
        );

        UserRole::updateOrCreate(
            [
                'code' => 'advisor'
            ],
            [
                'code' => 'advisor',
                'name' => 'Asesor'
            ]
        );

        UserRole::updateOrCreate(
            [
                'code' => 'student'
            ],
            [
                'code' => 'student',
                'name' => 'Alumno'
            ]
        );
    }
}
