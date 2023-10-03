<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            [
                'email' => 'admin@admin.com'
            ],
            [
                'email' => 'admin@admin.com',
                'password' => '$2y$10$9CxebWNrl1bzBfW3dHw.0etq9MQFWdLO2Y8FibDPVJfmdmCU/25LK',
                'role_id' => UserRole::where('code', 'admin')->first()->id
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'alumno@alumno.com'
            ],
            [
                'email' => 'alumno@alumno.com',
                'password' => '$2y$10$9CxebWNrl1bzBfW3dHw.0etq9MQFWdLO2Y8FibDPVJfmdmCU/25LK',
                'role_id' => UserRole::where('code', 'student')->first()->id
            ]
        );
    }
}
