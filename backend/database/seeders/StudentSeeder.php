<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            ['name' => 'Ana Souza', 'email' => 'ana@fenix.test'],
            ['name' => 'Bruno Lima', 'email' => 'bruno@fenix.test'],
            ['name' => 'Carla Mendes', 'email' => 'carla@fenix.test'],
            ['name' => 'Diego Rocha', 'email' => 'diego@fenix.test'],
            ['name' => 'Elisa Castro', 'email' => 'elisa@fenix.test'],
        ];

        foreach ($students as $student) {
            Student::updateOrCreate(['email' => $student['email']], $student);
        }
    }
}
