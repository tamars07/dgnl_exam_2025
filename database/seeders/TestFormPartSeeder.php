<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TestFormPart;

class TestFormPartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TestFormPart::create([
            'desc' => 'DGNL-TN1 Phần thi Trắc nghiệm có 1 phương án đúng dùng cho Kì thi ĐGNL',
            'order' => 1,
            'no_questions' => 20,
            'list_questions' => '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20',
            'test_form_id' => 1,
            'test_part_id' => 1,
        ]);
        TestFormPart::create([
            'desc' => 'DGNL-TNN Phần thi Trắc nghiệm có nhiều phương án đúng dùng cho Kì thi ĐGNL',
            'order' => 2,
            'no_questions' => 5,
            'list_questions' => '21,22,23,24,25',
            'test_form_id' => 1,
            'test_part_id' => 2,
        ]);
        TestFormPart::create([
            'desc' => 'DGNL-TNC Phần thi Trắc nghiệm theo nhóm câu có chung ngữ cảnh, mỗi câu 1 phương án đúng dùng cho Kì thi ĐGNL',
            'order' => 3,
            'no_questions' => 2,
            'list_questions' => '26,27',
            'test_form_id' => 1,
            'test_part_id' => 3,
        ]);
        TestFormPart::create([
            'desc' => 'DGNL-TLN Phần thi Trắc nghiệm điền đáp án dùng cho Kì thi ĐGNL',
            'order' => 4,
            'no_questions' => 10,
            'list_questions' => '31,32,33,34,35,36,37,38,39,40',
            'test_form_id' => 1,
            'test_part_id' => 4,
        ]);
        TestFormPart::create([
            'desc' => 'DGNL-TNC-VA Phần thi Trắc nghiệm theo nhóm câu có chung ngữ cảnh, mỗi câu 1 phương án đúng dùng cho Kì thi ĐGNL',
            'order' => 1,
            'no_questions' => 3,
            'list_questions' => '1,2,3',
            'test_form_id' => 2,
            'test_part_id' => 5,
        ]);
        TestFormPart::create([
            'desc' => 'DGNL-LNN-VA Phần thi viết bài luận môn Ngữ văn dùng cho Kì thi ĐGNL',
            'order' => 2,
            'no_questions' => 2,
            'list_questions' => '21,22',
            'test_form_id' => 2,
            'test_part_id' => 6,
        ]);

        TestFormPart::create([
            'desc' => 'DGNL-TNC-VA Phần thi Trắc nghiệm theo nhóm câu có chung ngữ cảnh, mỗi câu 1 phương án đúng dùng cho Kì thi ĐGNL',
            'order' => 1,
            'no_questions' => 3,
            'list_questions' => '1,2,3',
            'test_form_id' => 3,
            'test_part_id' => 5,
        ]);
        TestFormPart::create([
            'desc' => 'DGNL-LNN-VA Phần thi viết bài luận môn Ngữ văn dùng cho Kì thi ĐGNL',
            'order' => 2,
            'no_questions' => 2,
            'list_questions' => '22',
            'test_form_id' => 3,
            'test_part_id' => 6,
        ]);
    }
}
