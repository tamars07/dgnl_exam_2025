<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomLASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::create([
            'code' => 'HCMUE_LA.P01',
            'name' => 'P01',
            'organization_code' => 'HCMUE_LA',
            'desc' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Phân hiệu Long An',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'HCMUE_LA.P02',
            'name' => 'P02',
            'organization_code' => 'HCMUE_LA',
            'desc' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Phân hiệu Long An',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'HCMUE_LA.P03',
            'name' => 'P03',
            'organization_code' => 'HCMUE_LA',
            'desc' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Phân hiệu Long An',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'HCMUE_LA.P04',
            'name' => 'P04',
            'organization_code' => 'HCMUE_LA',
            'desc' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Phân hiệu Long An',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'HCMUE_LA.P05',
            'name' => 'P05',
            'organization_code' => 'HCMUE_LA',
            'desc' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Phân hiệu Long An',
            'no_slots' => '40'
        ]);
    }
}
