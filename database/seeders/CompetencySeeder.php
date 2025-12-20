<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Competency;

class CompetencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Competency::create([
            'code' => 'T1',
            'name' => 'Năng lực nhận biết',
            'desc' => '',
            'subject_id' => '1',
            'status' => '1',
        ]);
    }
}
