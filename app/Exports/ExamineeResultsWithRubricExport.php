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
use App\Models\Rubric;

class ExamineeResultsWithRubricExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    private $council_id;
    
    public function __construct($council_id = 0)
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
        $data = [];
        
        $examine_tests = DB::table('examiner_assignments')
                    ->select('examinee_test_code')
                    ->distinct()
                    // ->whereLike('examinee_test_code',$council->code)
                    ->orderBy('examiner_pair_id','asc')
                    ->get();
        $num = 0;
        foreach($examine_tests as $obj){
            if(!str_contains($obj->examinee_test_code, $council->code)) continue;
            $num = $num + 1;
            $item = ExaminerAssignment::where('examinee_test_code',$obj->examinee_test_code)->first();
            $examiner_pair = ExaminerPair::find($item->examiner_pair_id);
            $examiner1_pair = ExaminerPairDetail::where('examiner_pair_id',$item->examiner_pair_id)->where('examiner_role',1)->first();
            $examiner1 = Monitor::find($examiner1_pair->examiner_id);
            $examiner2_pair = ExaminerPairDetail::where('examiner_pair_id',$item->examiner_pair_id)->where('examiner_role',2)->first();
            $examiner2 = Monitor::find($examiner2_pair->examiner_id);

            $examinee_test_mix = ExamineeTestMix::where('examinee_test_code',$item->examinee_test_code)->first();
            $examinee = Examinee::where('code',$examinee_test_mix->examinee_code)->first();
            $turn = CouncilTurn::where('code',$examinee_test_mix->council_turn_code)->first();
            $subject = Subject::find($examinee_test_mix->subject_id);
            $room = Room::where('code',$examinee_test_mix->room_code)->first();
            
            $examinee_answer21 = AnswerKey::where('examinee_test_code',$item->examinee_test_code)->where('matrix_location',21)->first();
            
            $rubric_criteria_21 = RubricCriteria::where('rubric_id',$examinee_answer21->rubric_id)->get();
            $_total_score121 = 0;
            $detail121 = [];
            foreach($rubric_criteria_21 as $rubric){
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner1->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
                $_total_score121 += floatval($_detail?$_detail->score:0);
                $detail121[] = floatval($_detail?$_detail->score:0);
            }
            $_total_score221 = 0;
            $detail221 = [];
            foreach($rubric_criteria_21 as $rubric){
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
                $_total_score221 += floatval($_detail?$_detail->score:0);
                $detail221[] = floatval($_detail?$_detail->score:0);
            }

            $examinee_answer22 = AnswerKey::where('examinee_test_code',$item->examinee_test_code)->where('matrix_location',22)->first();
            $rubric_criteria_22 = RubricCriteria::where('rubric_id',$examinee_answer22->rubric_id)->get();
            $_total_score122 = 0;
            $detail122 = [];
            foreach($rubric_criteria_22 as $rubric){
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner1->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
                $_total_score122 += floatval($_detail?$_detail->score:0);
                $detail122[] = floatval($_detail?$_detail->score:0);
            }
            $_total_score222 = 0;
            $detail222 = [];
            foreach($rubric_criteria_22 as $rubric){
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
                $_total_score222 += floatval($_detail?$_detail->score:0);
                $detail222[] = floatval($_detail?$_detail->score:0);
            }
            
            $diff_score21 = abs($_total_score121 - $_total_score221)?abs($_total_score121 - $_total_score221):'0';
            $diff_score22 = abs($_total_score122 - $_total_score222)?abs($_total_score122 - $_total_score222):'0';
            
            $avg_score21 = 0;
            $avg_score22 = 0;
            if($diff_score21 <= 1 && $diff_score22 <= 1){
                $avg_score21 = floatval($_total_score121 + $_total_score221) / 2;
                $avg_score22 = floatval($_total_score122 + $_total_score222) / 2;
            }
            $tn1_score = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('rubric_id',0)->sum('score');
            $avg_score21 = $avg_score21?$avg_score21:'0';
            $avg_score22 = $avg_score22?$avg_score22:'0';
            $score = floatval($tn1_score + $avg_score21 * 2 + $avg_score22 * 3) / 10;
            $_data = [
                // 'num' => ($num + 1),
                'num' => $num,
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phach
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'council' => $examinee_test_mix->council_code,
                'turn' => $turn->name,
                'room' => $room->name,
                'subject' => $subject->desc,
                'score' => $score?$score:'0',
                'tn1_score' => $tn1_score?$tn1_score:'0',
                'avg_score21' => $avg_score21,  //diem TB
                'avg_score22' => $avg_score22,  //diem TB
            ];

            //lay diem chi tiet rubric cuua  CBChT 1
            $rubric21 = Rubric::where('id',$examinee_answer21->rubric_id)->where('matrix_location',21)->first();
            $rubric_criteria_21 = RubricCriteria::where('rubric_id',$rubric21->id)->get();
            foreach($rubric_criteria_21 as $i => $item){
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$item->id)->where('examiner_id',$examiner1->id)->where('examinee_test_code',$examinee_test_mix->examinee_test_code)->orderBy('id','desc')->first();
                $_score = $_detail?floatval($_detail->score):'0';
                $_data[] = $_score?$_score:'0';
                
            }
            $rubric22 = Rubric::where('id',$examinee_answer22->rubric_id)->where('matrix_location',22)->first();
            $rubric_criteria_22 = RubricCriteria::where('rubric_id',$rubric22->id)->get();
            foreach($rubric_criteria_22 as $i => $item){
                // $header[] = 'TC' . ($i+1);
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$item->id)->where('examiner_id',$examiner1->id)->where('examinee_test_code',$examinee_test_mix->examinee_test_code)->orderBy('id','desc')->first();
                $_score = $_detail?floatval($_detail->score):'0';
                $_data[] = $_score?$_score:'0';
            }
            //lay diem chi tiet rubric cuua  CBChT 2
            $rubric21 = Rubric::where('id',$examinee_answer21->rubric_id)->where('matrix_location',21)->first();
            $rubric_criteria_21 = RubricCriteria::where('rubric_id',$rubric21->id)->get();
            foreach($rubric_criteria_21 as $i => $item){
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$item->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$examinee_test_mix->examinee_test_code)->orderBy('id','desc')->first();
                $_score = $_detail?floatval($_detail->score):'0';
                $_data[] = $_score?$_score:'0';
                
            }
            $rubric22 = Rubric::where('id',$examinee_answer22->rubric_id)->where('matrix_location',22)->first();
            $rubric_criteria_22 = RubricCriteria::where('rubric_id',$rubric22->id)->get();
            foreach($rubric_criteria_22 as $i => $item){
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$item->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$examinee_test_mix->examinee_test_code)->orderBy('id','desc')->first();
                $_score = $_detail?floatval($_detail->score):'0';
                $_data[] = $_score?$_score:'0';
            }

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
            'Mã phách',
            'SBD',
            'Họ tên thí sinh',
            'CCCD',
            'Ngày sinh',
            'Hội đồng thi',
            'Ca thi',
            'Phòng thi',
            'Môn thi',
            'Điểm tổng hợp',
            'Đọc',
            'Đoạn',
            'Bài',
        ];
        $rubric21 = Rubric::where('matrix_location',21)->first();
        $rubric_criteria_21 = RubricCriteria::where('rubric_id',$rubric21->id)->where('status',1)->get();
        $rubric22 = Rubric::where('matrix_location',22)->first();
        $rubric_criteria_22 = RubricCriteria::where('rubric_id',$rubric22->id)->where('status',1)->get();
        foreach($rubric_criteria_21 as $i => $item){
            $header[] = '1.TC' . ($i+1);
        }
        foreach($rubric_criteria_22 as $i => $item){
            $header[] = '1.TC' . ($i+1);
        }
        foreach($rubric_criteria_21 as $i => $item){
            $header[] = '2.TC' . ($i+1);
        }
        foreach($rubric_criteria_22 as $i => $item){
            $header[] = '2.TC' . ($i+1);
        }
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
