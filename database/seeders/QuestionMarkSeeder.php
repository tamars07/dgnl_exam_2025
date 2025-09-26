<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuestionMark;

class QuestionMarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        QuestionMark::create([
            'name' => 'Dễ 1',
            'desc' => 'Điểm cho phần dễ 1',
            'value' => 1.5,
        ]);
        QuestionMark::create([
            'name' => 'Dễ 2',
            'desc' => 'Điểm cho phần dễ 2',
            'value' => 1.7,
        ]);
        QuestionMark::create([
            'name' => 'TB 1',
            'desc' => 'Điểm cho phần TB 1',
            'value' => 2,
        ]);
        QuestionMark::create([
            'name' => 'TB 2',
            'desc' => 'Điểm cho phần TB 2',
            'value' => 2.5,
        ]);
        QuestionMark::create([
            'name' => 'Khó 1',
            'desc' => 'Điểm cho phần Khó 1',
            'value' => 3,
        ]);
        QuestionMark::create([
            'name' => 'Ngữ văn Dễ',
            'desc' => 'Điểm cho phần Ngữ văn Dễ',
            'value' => 1.6,
        ]);
        QuestionMark::create([
            'name' => 'Ngữ văn TB',
            'desc' => 'Điểm cho phần Ngữ văn TB',
            'value' => 2.2,
        ]);
        QuestionMark::create([
            'name' => 'Ngữ văn Khó',
            'desc' => 'Điểm cho phần Ngữ văn Khó',
            'value' => 2.7,
        ]);
    }
}
