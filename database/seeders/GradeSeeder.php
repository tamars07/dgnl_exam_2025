<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Grade::create([
            'code' => '10',
            'name' => 'Lớp 10'
        ]);
        Grade::create([
            'code' => '11',
            'name' => 'Lớp 11'
        ]);
        Grade::create([
            'code' => '12',
            'name' => 'Lớp 12'
        ]);
    }
}
