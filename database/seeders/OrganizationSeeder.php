<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Organization::create([
            'code' => 'HCMUE',
            'name' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Cơ sở chính',
            'address' => ''
        ]);
        Organization::create([
            'code' => 'HCMUE_LA',
            'name' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Phân hiệu Long An',
            'address' => ''
        ]);
        Organization::create([
            'code' => 'HCMUE_GL',
            'name' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Phân hiệu Gia Lai',
            'address' => ''
        ]);
        Organization::create([
            'code' => 'HUIT',
            'name' => 'Trường Đại học Công Thương Thành phố Hồ Chí Minh',
            'address' => ''
        ]);
        Organization::create([
            'code' => 'HUEdu',
            'name' => 'Trường Đại học Sư phạm, Đại học Huế',
            'address' => ''
        ]);
        Organization::create([
            'code' => 'UED',
            'name' => 'Trường Đại học Sư phạm, Đại học Đà Nẵng',
            'address' => ''
        ]);
        Organization::create([
            'code' => 'TNU',
            'name' => 'Trường Đại học Tây Nguyên',
            'address' => ''
        ]);
        Organization::create([
            'code' => 'CTU',
            'name' => 'Trường Đại học Cần Thơ',
            'address' => ''
        ]);
    }
}
