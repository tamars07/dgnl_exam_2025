<?php

namespace App\Exports;

use App\Models\AnswerKey;
use App\Models\Council;
use App\Models\CouncilTurn;
use App\Models\Subject;
use App\Models\Room;
use App\Models\Examinee;
use App\Models\ExamineeTestMix;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

use App\Models\ExaminerPair;
use App\Models\ExaminerPairDetail;
use App\Models\ExaminerAssignment;
use App\Models\ExaminerRubricDetail;
use App\Models\RubricCriteria;
use App\Models\Monitor;
use App\Models\QuestionMark;
use App\Models\Rubric;

class ExamineeResultsByTurnExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    private $code;
    
    public function __construct($code = 0)
	{
		$this->code = $code;
	}
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        //
        $council_turn = CouncilTurn::where('code',$this->code)->first();
        $data = [];
        
        $examine_tests = DB::table('answer_keys')
                    ->whereLike('examinee_test_code','%' . $council_turn->code . '%')
                    ->select('examinee_test_code')
                    ->distinct()
                    ->orderBy('examinee_test_code','asc')
                    ->get();
        
        $i = 0;
        foreach($examine_tests as $num => $obj){
            if(!str_contains($obj->examinee_test_code, $council_turn->code)) continue;
            $examinee_test_mix = ExamineeTestMix::where('examinee_test_code',$obj->examinee_test_code)->first();
            
            $examinee = Examinee::where('code',$examinee_test_mix->examinee_code)->first();
            $subject = Subject::find($examinee_test_mix->subject_id);
            $room = Room::where('code',$examinee_test_mix->room_code)->first();
            
            $total_score = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->sum('score');
            if($examinee_test_mix->subject_id == 5){
                $tn1_score = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('rubric_id',0)->sum('score');
                $avg1_score = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',21)->sum('score');
                $avg2_score = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',22)->sum('score');
                $total_score = floatval($tn1_score + $avg1_score * 2 + $avg2_score * 3) / 10;
            }

            $i = $i + 1;
            $_data = [
                'num' => $i,
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                'council_code' => $council_turn->council_code,  //HD thi
                'turn_code' => $council_turn->name,  //Ca thi
                'room' => $room->name,  //Phong thi
                'subject' => $subject->desc,  //Mon thi
                'tn1_score' => $total_score?$total_score:'0',
            ];

            $data[] = $_data;
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
        $header = [
            'STT',
            'SBD',
            'Họ tên thí sinh',
            'CCCD',
            'Ngày sinh',
            'Mã phách',
            'Hội đồng thi',
            'Ca thi',
            'Phòng thi',
            'Môn thi',
            'Điểm',
        ];
        return $header;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
