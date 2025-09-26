<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomUEDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::create([
            'code' => '',
            'name' => '',
            'organization_code' => 'UED',
            'desc' => 'Trường Đại học Sư phạm, Đại học Đà Nẵng',
            'no_slots' => ''
        ]);
    }
}
