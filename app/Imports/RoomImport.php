<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use App\Models\Room;

class RoomImport implements ToCollection, WithHeadingRow
{
    private $organization_code;

    public function __construct($organization_code)
	{
		$this->organization_code = $organization_code;
	}
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        foreach($collection as $key => $item){
            // dd($item);
            if($this->organization_code != $item['ma_diem_thi']) {
                continue;
            }
            if(Room::where('code',$this->organization_code . '.' . $item['phong_thi'])->count()) {
                continue;
            }
            Room::create([
                'code' => $this->organization_code . '.' . $item['phong_thi'],
                'name' => $item['phong_thi'],
                'desc' => $item['ghi_chu'],
                'no_slots' => $item['so_may'],
                'organization_code' => $this->organization_code,
                'status' => $item['su_dung']
            ]);
        }
    }

    public function headingRow(): int
    {
        return 3;
    }
}
