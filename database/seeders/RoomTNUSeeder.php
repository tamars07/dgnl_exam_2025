<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomTNUSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::create([
            'code' => 'TNU.P7320',
            'name' => 'P7320',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'TNU.P7321',
            'name' => 'P7321',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'TNU.P7323',
            'name' => 'P7323',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'TNU.P7324',
            'name' => 'P7324',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'TNU.P7426',
            'name' => 'P7426',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'TNU.P7427',
            'name' => 'P7427',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'TNU.P7428',
            'name' => 'P7428',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'TNU.P7429',
            'name' => 'P7429',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
        Room::create([
            'code' => 'TNU.P7430',
            'name' => 'P7430',
            'organization_code' => 'TNU',
            'desc' => 'Trường Đại học Tây Nguyên',
            'no_slots' => '40'
        ]);
    }
}
