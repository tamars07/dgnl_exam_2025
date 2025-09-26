<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Topic;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Topic::create([
            'code' => 'T1.1.01',
            'name' => 'Đơn vị kiến thức chung',
            'desc' => '',
            'grade_id' => '1',
            'subject_id' => '1',
            'competency_id' => '1',
            'taxonomy_id' => '1',
            'status' => '1',
        ]);
    }
}
