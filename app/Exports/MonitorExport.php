<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use App\Models\Monitor;
use App\Models\Council;
use App\Models\CouncilTurn;
use App\Models\CouncilTurnRoom;
use App\Models\Room;

class MonitorExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    private $council_id;
    
    public function __construct($council_id)
	{
		$this->council_id = $council_id;
	}
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        //
        $council = Council::find($this->council_id);
        $council_turn_rooms = CouncilTurnRoom::whereLike('council_turn_code','%' . $council->code . '%')->get();
        $data = [];
        $chairman = Monitor::where('user_id',$council->monitor_id)->first();
        $data[] = [
            'num' => 1,
            'council_code' => $council->code,
            'council_name' => $council->desc,
            'username' => $chairman->code,
            'password' => $chairman->password,
            'date' => '',
            'time' => '',
            'room' => ''
        ];
        foreach($council_turn_rooms as $num => $council_turn_room){
            $council_turn = CouncilTurn::where('code',$council_turn_room->council_turn_code)->first();
            $monitor = Monitor::where('code',$council_turn_room->monitor_code)->first();
            $room = Room::where('code',$council_turn_room->room_code)->first();
            $data[] = [
                'num' => ($num+2),
                'council_code' => $council->code,
                'council_name' => $council->desc,
                'username' => $monitor->code,
                'password' => $monitor->password,
                'date' => date('d/m/Y', strtotime($council_turn->start_at)),
                'time' => date('H:i', strtotime($council_turn->start_at)),
                'room' => $room->code
            ];
        }
        return $data;
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return ["STT", "Mã HĐ", "Tên HĐ", "Tài khoản", "Mật khẩu", "Ngày thi", "Giờ thi", "Phòng thi"];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
