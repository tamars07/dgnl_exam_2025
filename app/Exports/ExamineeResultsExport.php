<?php

namespace App\Exports;

use App\Models\AnswerKey;
use App\Models\Council;
use App\Models\CouncilTurn;
use App\Models\Subject;
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

class ExamineeResultsExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
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
        
        $examine_tests = DB::table('answer_keys')
                    ->whereLike('examinee_test_code','%' . $council->code . '%')
                    ->select('examinee_test_code')
                    ->distinct()
                    ->orderBy('examinee_test_code','asc')
                    ->get();
        // dd(count($examine_tests));
        $i = 0;
        foreach($examine_tests as $num => $obj){
            if(!str_contains($obj->examinee_test_code, $council->code)) continue;
            $examinee_test_mix = ExamineeTestMix::where('examinee_test_code',$obj->examinee_test_code)->first();
            // if($examinee_test_mix->subject_id == 5) continue;
            $examinee = Examinee::where('code',$examinee_test_mix->examinee_code)->first();
            $subject = Subject::find($examinee_test_mix->subject_id);
            $council_turn = CouncilTurn::where('code',$examinee_test_mix->council_turn_code)->first();
            
            $total_score = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->sum('score');

            $i = $i + 1;
            $_data = [
                // 'num' => ($num + 1),
                'num' => $i,
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                'council_code' => $council->desc,  //HD thi
                'turn_code' => $council_turn->name,  //Ca thi
                'subject' => $subject->desc,  //Ngay sinh
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
            'Môn thi',
            'Điểm',
            // 'Đoạn',
            // 'Bài',
        ];
        // $rubric21 = Rubric::where('matrix_location',21)->first();
        // $rubric_criteria_21 = RubricCriteria::where('rubric_id',$rubric21->id)->where('status',1)->get();
        // $rubric22 = Rubric::where('matrix_location',22)->first();
        // $rubric_criteria_22 = RubricCriteria::where('rubric_id',$rubric22->id)->where('status',1)->get();
        // foreach($rubric_criteria_21 as $i => $item){
        //     $header[] = '1.TC' . ($i+1);
        // }
        // foreach($rubric_criteria_22 as $i => $item){
        //     $header[] = '1.TC' . ($i+1);
        // }
        // foreach($rubric_criteria_21 as $i => $item){
        //     $header[] = '2.TC' . ($i+1);
        // }
        // foreach($rubric_criteria_22 as $i => $item){
        //     $header[] = '2.TC' . ($i+1);
        // }
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
