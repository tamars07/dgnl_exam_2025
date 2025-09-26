<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Difficult;

class DifficultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Difficult::create([
            'name' => 'Dễ',
            'desc' => 'Độ khó mức dễ'
        ]);
        Difficult::create([
            'name' => 'Trung bình',
            'desc' => 'Độ khó mức Trung bình'
        ]);
        Difficult::create([
            'name' => 'Khó 1',
            'desc' => 'Độ khó mức Khó 1'
        ]);
        Difficult::create([
            'name' => 'Khó 2',
            'desc' => 'Độ khó mức Khó 2'
        ]);
    }
}
