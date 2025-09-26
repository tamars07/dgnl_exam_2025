<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuestionStore;

class QuestionStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        QuestionStore::create([
            'code' => 'B1',
            'name' => 'B1 - Câu hỏi thô',
            'desc' => '',
        ]);
        QuestionStore::create([
            'code' => 'B2',
            'name' => 'B2',
            'desc' => '',
        ]);
        QuestionStore::create([
            'code' => 'B3',
            'name' => 'B3',
            'desc' => '',
        ]);
        QuestionStore::create([
            'code' => 'B4',
            'name' => 'B4',
            'desc' => '',
        ]);
        QuestionStore::create([
            'code' => 'B5',
            'name' => 'B5',
            'desc' => '',
        ]);
        QuestionStore::create([
            'code' => 'B6',
            'name' => 'B6 - Câu hỏi đã được chuẩn hóa',
            'desc' => 'Câu hỏi đã được chuẩn hóa',
        ]);
        QuestionStore::create([
            'code' => 'B7',
            'name' => 'B7 - Loại bỏ khỏi ngân hàng',
            'desc' => 'Câu đã thi nhiều lần, bị lộ, ... và sẽ không sử dụng nữa.',
        ]);
        QuestionStore::create([
            'code' => 'B8',
            'name' => 'B8',
            'desc' => '',
        ]);
    }
}
