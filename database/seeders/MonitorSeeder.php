<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Monitor;
use App\Models\Organization;

class MonitorSeeder extends Seeder
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
        
        //chairman role
        foreach($orgs as $org){
            $username = 'hd.' . strtolower($org->code);
            $fullname = 'Điểm trưởng ' . $org->name;
            $password = $this->getRandomString(6);
            $role_id = 7;
            $org_id = $org->id;
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

        //monitor
        foreach($orgs as $org){
            for($i = 1; $i <= 50; $i++){
                $username = 'giamthi' . $org->id . str_pad($i, 2, '0', STR_PAD_LEFT);
                $fullname = 'Giám thị ' . $org->id . str_pad($i, 2, '0', STR_PAD_LEFT);
                $password = $this->getRandomString(6);
                $role_id = 4;
                $org_id = $org->id;
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
}
