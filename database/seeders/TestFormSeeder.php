<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TestForm;

class TestFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TestForm::create([
            'code' => 'DGNL_TLHS',
            'name' => 'Dạng đề thi ĐGNL môn Toán Lí Hóa Sinh',
            'time' => 90,
            'no_questions' => 37,
            'no_parts' => 4,
        ]);
        TestForm::create([
            'code' => 'DGNL_VA',
            'name' => 'Dạng đề thi ĐGNL môn Ngữ văn',
            'time' => 90,
            'no_questions' => 5,
            'no_parts' => 2,
        ]);
        TestForm::create([
            'code' => 'DGNL_VA_2',
            'name' => 'Dạng đề thi ĐGNL môn Ngữ văn 2',
            'time' => 90,
            'no_questions' => 5,
            'no_parts' => 2,
        ]);
    }
}
