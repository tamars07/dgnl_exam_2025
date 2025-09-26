<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Taxonomy;

class TaxonomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Taxonomy::create([
            'code' => 'T1.1',
            'name' => 'Kiến thức chung',
            'desc' => '',
            'grade_id' => '1',
            'subject_id' => '1',
            'competency_id' => '1',
            'status' => '1',
        ]);
    }
}
