<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomCTUSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::create([
            'code' => '',
            'name' => '',
            'organization_code' => 'CTU',
            'desc' => 'Trường Đại học Cần Thơ',
            'no_slots' => ''
        ]);
    }
}
