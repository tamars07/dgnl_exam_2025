<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Monitor;
use App\Models\Examinee;
use App\Models\User;
use App\Models\Subject;

class MonitorImport implements ToCollection
{
    private $council_code;
    private $council_turn_code;
    private $room_code;
    
    public function __construct($council_code, $council_turn_code, $room_code)
	{
		$this->council_code = $council_code;
		$this->council_turn_code = $council_turn_code;
		$this->room_code = $room_code;
	}
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
        // dd($this->council_code . ' ' . $this->council_turn_code . ' ' . $this->room_code);
        // dd($collection);
        
        foreach($collection as $key => $item){
            if($key == 0) continue;
            // dd($item[1]);
            $user = User::create([
                'name' => $item[3] . ' ' . $item[4],
                'email' => $item[1],
                'email_verified_at' => now(),
                'password' => Hash::make($item[6] ?? '123456'),
                'remember_token' => Str::random(10),
                'status' => 1
            ]);
            $user->roles()->attach(8);
            $subject = Subject::where('code',$item[8])->first();
            if(!$subject) continue;
            Examinee::create([
                'code' => $item[1],
                'id_card_number' => $item[2],
                'lastname' => $item[3],
                'firstname' => $item[4],
                'birthday' => '2007/01/01',
                'password' => $item[6] ?? '123456',
                'seat_number' => $item[7],
                'subject_id' => $subject->id,
                'council_code' => $this->council_code,
                'council_turn_code' => $this->council_turn_code,
                'room_code' => $this->room_code,
                'user_id' => $user->id,
                'role_id' => 8,
                'is_backup' => 0,
                'status' => 1
            ]);
        }
    }
}
