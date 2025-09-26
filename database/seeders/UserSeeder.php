<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Monitor;
use App\Models\Organization;

class UserSeeder extends Seeder
{
    private function getRandomString($n){
        $characters = '0123456789';
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
        //admin role
        $username = 'quantri';
        $fullname = 'Quản trị hệ thống';
        $password = '123456';
        $role_id = 1;
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

        //editor role
        for($i = 1; $i <= 10; $i++){
            $username = 'nhapde_' . $i;
            $fullname = 'Cán bộ nhập đề ' . $i;
            $password = $this->getRandomString(6);
            $role_id = 3;
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

        $orgs = Organization::all();
        
        //tech role
        foreach($orgs as $org){
            $username = 'kt.' . strtolower($org->code);
            $fullname = 'Cán bộ kĩ thuật ' . $org->name;
            $password = $this->getRandomString(6);
            $role_id = 2;
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
