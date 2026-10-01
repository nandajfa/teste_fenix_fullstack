<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['name' => 'Mariana Costa', 'email' => 'mariana.costa@fenix.test'],
            ['name' => 'Lucas Ferreira', 'email' => 'lucas.ferreira@fenix.test'],
            ['name' => 'Beatriz Almeida', 'email' => 'beatriz.almeida@fenix.test'],
            ['name' => 'Rafael Nogueira', 'email' => 'rafael.nogueira@fenix.test'],
            ['name' => 'Ana Júlia Martins', 'email' => 'julia.martins@fenix.test'],
            ['name' => 'Thiago Ribeiro', 'email' => 'thiago.ribeiro@fenix.test'],
            ['name' => 'Larissa Carvalho Mendes', 'email' => 'larissa.carvalho@fenix.test'],
            ['name' => 'Gabriel Moreira da Silva', 'email' => 'gabriel.moreira@fenix.test'],
        ];

        foreach ($students as $student) {
            Student::updateOrCreate(['email' => $student['email']], $student);
        }
    }
}
