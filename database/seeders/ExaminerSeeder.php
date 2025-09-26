<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Monitor;
use App\Models\Organization;

class ExaminerSeeder extends Seeder
{
    private function getRandomString($n){
        $characters = '123456789';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = random_int(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgs = Organization::all();

        //examiner
        for($i = 1; $i <= 20; $i++){
            $username = 'giamkhao' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $fullname = 'Cán bộ chấm thi ' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $password = $this->getRandomString(6);
            $role_id = 5;
            $org_id = 1;
            $user = User::factory()->create([
                'email' => $username,
                'name' => $fullname,
                'password' => $password,
            ]);
            $user->roles()->attach($role_id);
            Monitor::create([
                'code' => $username,
                'name' => $fullname,
                'password' => $password,
                'role_id' => $role_id,
                'user_id' => $user->id,
                'organization_id' => $org_id,
            ]);
        }
        for($i = 1; $i <= 10; $i++){
            $username = 'phuckhao' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $fullname = 'Cán bộ phúc khảo ' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $password = $this->getRandomString(6);
            $role_id = 6;
            $org_id = 1;
            $user = User::factory()->create([
                'email' => $username,
                'name' => $fullname,
                'password' => $password,
            ]);
            $user->roles()->attach($role_id);
            Monitor::create([
                'code' => $username,
                'name' => $fullname,
                'password' => $password,
                'role_id' => $role_id,
                'user_id' => $user->id,
                'organization_id' => $org_id,
            ]);
        }
    }
}
