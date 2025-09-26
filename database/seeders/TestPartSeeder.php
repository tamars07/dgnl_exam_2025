<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TestPart;

class TestPartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TestPart::create([
            'name' => 'DGNL-TN1',
            'part_title' => '<p><b style="font-size:13.0pt"><span style="font-family:&quot;Times New Roman&quot;,serif">Th&iacute; sinh lựa chọn một phương &aacute;n đ&uacute;ng theo y&ecirc;u cầu từ c&acirc;u 1 đến c&acirc;u 20.</span></b></p>',
            'desc' => 'Phần thi Trắc nghiệm có 1 phương án đúng dùng cho Kì thi ĐGNL',
            'caltype' => 'auto',
            'is_shuffled' => 1,
        ]);
        TestPart::create([
            'name' => 'DGNL-TNN',
            'part_title' => '<p><b lang="VI" style="font-size:13.0pt"><span style="font-family:&quot;Times New Roman&quot;,serif">Th&iacute; sinh chọn c&aacute;c phương &aacute;n đ&uacute;ng theo y&ecirc;u cầu từ c&acirc;u 21 đến c&acirc;u 25.</span></b></p>',
            'desc' => 'Phần thi Trắc nghiệm có nhiều phương án đúng dùng cho Kì thi ĐGNL',
            'caltype' => 'auto',
            'is_shuffled' => 1,
        ]);
        TestPart::create([
            'name' => 'DGNL-TNC',
            'part_title' => '<p><b style="font-size:13.0pt"><span style="font-family:&quot;Times New Roman&quot;,serif">Th&iacute; sinh điền đ&aacute;p &aacute;n v&agrave;o &ocirc; trống theo y&ecirc;u cầu từ c&acirc;u 31 đến c&acirc;u 40.</span></b></p>',
            'desc' => 'Phần thi Trắc nghiệm theo nhóm câu có chung ngữ cảnh, mỗi câu 1 phương án đúng dùng cho Kì thi ĐGNL',
            'caltype' => 'auto_context',
            'is_shuffled' => 0,
        ]);
        TestPart::create([
            'name' => 'DGNL-TLN',
            'part_title' => '<p><b style="font-size:13.0pt"><span style="font-family:&quot;Times New Roman&quot;,serif">Th&iacute; sinh điền đ&aacute;p &aacute;n v&agrave;o &ocirc; trống theo y&ecirc;u cầu từ c&acirc;u 31 đến c&acirc;u 40.</span></b></p>',
            'desc' => 'Phần thi Trắc nghiệm điền đáp án dùng cho Kì thi ĐGNL',
            'caltype' => 'auto_fill',
            'is_shuffled' => 1,
        ]);
        TestPart::create([
            'name' => 'DGNL-TNC-VA',
            'part_title' => '<p><b><span style="font-size:13.0pt"><span style="line-height:107%"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="color:black">PHẦN I: ĐỌC HIỂU</span></span></span></span></b></p>',
            'desc' => 'Phần thi Trắc nghiệm theo nhóm câu có chung ngữ cảnh, mỗi câu 1 phương án đúng dùng cho Kì thi ĐGNL',
            'caltype' => 'auto_context',
            'is_shuffled' => 0,
        ]);
        TestPart::create([
            'name' => 'DGNL-LNN-VA',
            'part_title' => '<p><b><span style="font-size:13.0pt"><span style="line-height:107%"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="color:black">PHẦN II: NGHỊ LUẬN</span></span></span></span></b></p>',
            'desc' => 'Phần thi viết bài luận môn Ngữ văn dùng cho Kì thi ĐGNL',
            'caltype' => 'reader',
            'is_shuffled' => 0,
        ]);
    }
}
