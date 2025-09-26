<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\Examinee;
use App\Models\User;
use App\Models\Subject;
use App\Models\Room;
use App\Models\CouncilTurnRoom;

class ExamineeImport implements ToCollection, WithHeadingRow
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
        foreach($collection as $key => $item){
            // if($key == 0) continue;
            // dd($item);
            if($this->room_code && $this->room_code != $item['phong_thi']) {
                continue;
            }
            // if(!$item['phong_thi']){
            //     continue;
            // }
            if($item['hd_thi']){
                $room = Room::where('organization_code',$item['hd_thi'])->where('name',$item['phong_thi'])->first();
            }else{
                $room = Room::where('name',$item['phong_thi'])->first();
            }
            
            if(!$room) continue;
            $check = CouncilTurnRoom::where('council_turn_code',$this->council_turn_code)->where('room_code',$room->code)->count();
            if(!$check){
                continue;
            }

            // if(User::where('email',$item['tai_khoan'])->count()) continue;
            $user = User::where('email',$item['tai_khoan'])->first();
            if(!$user) {
                $user = User::create([
                    'name' => $item['ho'] . ' ' . $item['ten'],
                    'email' => $item['tai_khoan'],
                    'email_verified_at' => now(),
                    'password' => Hash::make($item['mat_khau'] ?? '123456'),
                    'remember_token' => Str::random(10),
                    'status' => 1
                ]);
                $user->roles()->attach(8);
            }
            
            $subject = Subject::where('desc',$item['mon_thi'])->first();
            if(!$subject) continue;
            
            // if(Examinee::where('id_card_number',$item['cccd'])->where('council_code',$this->council_code)->where('council_turn_code',$this->council_turn_code)->count()) continue;
            $examinee = Examinee::where('user_id',$user->id)->where('council_code',$this->council_code)->where('council_turn_code',$this->council_turn_code)->first();
            if(!$examinee){
                $examinee = Examinee::create([
                'code' => $item['sbd'],
                'id_card_number' => $item['cccd'],
                'lastname' => $item['ho'],
                'firstname' => $item['ten'],
                'birthday' => $item['ngay_sinh'],
                'password' => $item['mat_khau'] ?? '123456',
                'seat_number' => $item['so_may'] ?? $item['stt'],
                'subject_id' => $subject->id,
                'council_code' => $this->council_code,
                'council_turn_code' => $this->council_turn_code,
                // 'room_code' => $this->room_code,
                'room_code' => $room->code,
                'user_id' => $user->id,
                'role_id' => 8,
                'is_backup' => ($item['sbd'][0] == 'X')?1:0,
                'status' => 1
                ]);
            }
            // if($item['sbd'] == 'XDP0002') dd($examinee);
        }
    }

    public function headingRow(): int
    {
        return 3;
    }
}
