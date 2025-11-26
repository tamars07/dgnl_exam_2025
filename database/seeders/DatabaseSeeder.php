<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\MonitorSeeder;
use Database\Seeders\DifficultSeeder;
use Database\Seeders\GradeSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\QuestionMarkSeeder;
use Database\Seeders\QuestionStoreSeeder;
use Database\Seeders\QuestionTypeSeeder;
use Database\Seeders\TestFormSeeder;
use Database\Seeders\TestPartSeeder;
use Database\Seeders\TestFormPartSeeder;
use Database\Seeders\CompetencySeeder;
use Database\Seeders\TaxonomySeeder;
use Database\Seeders\TopicSeeder;
use Database\Seeders\RoomHCMUESeeder;
use Database\Seeders\RoomHUITSeeder;
use Database\Seeders\RoomHUEduSeeder;
use Database\Seeders\RoomTNUSeeder;
use Database\Seeders\RoomLASeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {   
        $this->call([
            OrganizationSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            DifficultSeeder::class,
            GradeSeeder::class,
            SubjectSeeder::class,
            CompetencySeeder::class,
            TaxonomySeeder::class,
            TopicSeeder::class,
            // MonitorSeeder::class,
            RoomHCMUESeeder::class,
            RoomHUITSeeder::class,
            RoomHUEduSeeder::class,
            RoomTNUSeeder::class,
            RoomLASeeder::class,
            QuestionMarkSeeder::class,
            QuestionStoreSeeder::class,
            QuestionTypeSeeder::class,
            TestFormSeeder::class,
            TestPartSeeder::class,
            TestFormPartSeeder::class,
        ]);
    }
}
