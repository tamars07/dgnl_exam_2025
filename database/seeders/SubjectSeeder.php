<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Subject::create([
            'code' => 'TO',
            'code_number' => 1,
            'name' => 'Toán',
            'desc' => 'Toán học'
        ]);
        // Subject::create([
        //     'code' => 'LI',
        //     'code_number' => 2,
        //     'name' => 'Lí',
        //     'desc' => 'Vật lí'
        // ]);
        // Subject::create([
        //     'code' => 'HO',
        //     'code_number' => 3,
        //     'name' => 'Hoá',
        //     'desc' => 'Hoá học'
        // ]);
        // Subject::create([
        //     'code' => 'SI',
        //     'code_number' => 4,
        //     'name' => 'Sinh',
        //     'desc' => 'Sinh học'
        // ]);
        // Subject::create([
        //     'code' => 'VA',
        //     'code_number' => 5,
        //     'name' => 'Văn',
        //     'desc' => 'Ngữ văn'
        // ]);
        // Subject::create([
        //     'code' => 'N1',
        //     'code_number' => 6,
        //     'name' => 'Anh',
        //     'desc' => 'Tiếng Anh'
        // ]);
    }
}
