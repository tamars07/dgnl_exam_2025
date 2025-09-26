<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuestionType;

class QuestionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        QuestionType::create([
            'code' => 'TN1',
            'name' => 'Trắc nghiệm khách quan 1 PA đúng',
            'desc' => 'Multiple choice',
        ]);
        QuestionType::create([
            'code' => 'TNN',
            'name' => 'Trắc nghiệm khách quan nhiều PA đúng',
            'desc' => 'Calculated multichoice: Numerical, Calculated, Multiple choice',
        ]);
        QuestionType::create([
            'code' => 'TNC',
            'name' => 'Ngữ cảnh',
            'desc' => 'Context question',
        ]);
        QuestionType::create([
            'code' => 'TLN',
            'name' => 'Trả lời ngắn',
            'desc' => 'Short answer',
        ]);
        QuestionType::create([
            'code' => 'LNN',
            'name' => 'Bài văn',
            'desc' => 'Essay',
        ]);
    }
}
