<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomGLSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::create([
            'code' => '',
            'name' => '',
            'organization_code' => 'HCMUE_GL',
            'desc' => 'Trường Đại học Sư phạm Thành phố Hồ Chí Minh - Phân hiệu Gia Lai',
            'no_slots' => ''
        ]);
    }
}
