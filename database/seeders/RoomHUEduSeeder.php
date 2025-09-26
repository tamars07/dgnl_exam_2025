<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomHUEduSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::create([
            'code' => 'HUEdu.P01',
            'name' => 'P01',
            'organization_code' => 'HUEdu',
            'desc' => 'Trường Đại học Sư phạm, Đại học Huế',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'HUEdu.P02',
            'name' => 'P02',
            'organization_code' => 'HUEdu',
            'desc' => 'Trường Đại học Sư phạm, Đại học Huế',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'HUEdu.P03',
            'name' => 'P03',
            'organization_code' => 'HUEdu',
            'desc' => 'Trường Đại học Sư phạm, Đại học Huế',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'HUEdu.P04',
            'name' => 'P04',
            'organization_code' => 'HUEdu',
            'desc' => 'Trường Đại học Sư phạm, Đại học Huế',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'HUEdu.P05',
            'name' => 'P05',
            'organization_code' => 'HUEdu',
            'desc' => 'Trường Đại học Sư phạm, Đại học Huế',
            'no_slots' => '40'
        ]);
    }
}
