<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::create([
            'name' => 'ADMIN',
            'desc' => 'Quản trị hệ thống'
        ]);
        Role::create([
            'name' => 'MODERATOR',
            'desc' => 'Cán bộ sử dụng phần mềm quản lý'
        ]);
        Role::create([
            'name' => 'EDITOR',
            'desc' => 'Cán bộ nhập liệu và chỉnh sửa câu hỏi, đề thi'
        ]);
        Role::create([
            'name' => 'MONITOR',
            'desc' => 'Cán bộ trong phòng thi'
        ]);
        Role::create([
            'name' => 'EXAMINER',
            'desc' => 'Cán bộ chấm bài thi'
        ]);
        Role::create([
            'name' => 'REVIEWER',
            'desc' => 'Cán bộ chấm thi phúc khảo'
        ]);
        Role::create([
            'name' => 'CHAIRMAN',
            'desc' => 'Điểm trưởng hội đồng tổ chức thi'
        ]);
        Role::create([
            'name' => 'EXAMINEE',
            'desc' => 'Thí sinh dự thi'
        ]);
    }
}
