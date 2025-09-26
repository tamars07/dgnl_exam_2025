<?php

namespace App\Exports;

use App\Models\AnswerKey;
use App\Models\Examinee;
use App\Models\ExamineeTestMix;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

use App\Models\ReviewerPair;
use App\Models\ReviewerPairDetail;
use App\Models\ExaminerAssignment;
use App\Models\ExaminerRubricDetail;
use App\Models\RubricCriteria;
use App\Models\Monitor;
use App\Models\Rubric;

class ReviewerRubricDetailDiffExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    private $examiner_pair_id;
    
    public function __construct($examiner_pair_id)
	{
		$this->examiner_pair_id = $examiner_pair_id;
	}
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        //
        $header = [
            'STT',
            'SBD',
            'Họ tên thí sinh',
            'Mã phách',
            'CBChT1',
            'Tài khoản',
            'Điểm CBChT1',
            'CBChT2',
            'Tài khoản',
            'Điểm CBChT2',
            'Điểm lệch',
            'Điểm TB',
        ];
        $header21 = [];
        $header22 = [];
        $data = [];

        $examiner_pair = ReviewerPair::find($this->examiner_pair_id);
        $examiner1_pair = ReviewerPairDetail::where('reviewer_pair_id',$this->examiner_pair_id)->where('reviewer_role',1)->first();
        $examiner1 = Monitor::find($examiner1_pair->reviewer_id);
        $examiner2_pair = ReviewerPairDetail::where('reviewer_pair_id',$this->examiner_pair_id)->where('reviewer_role',2)->first();
        $examiner2 = Monitor::find($examiner2_pair->reviewer_id);
        
        $examine_tests = DB::table('examiner_assignments')
                    ->select('examinee_test_code')
                    ->distinct()
                    ->where('examiner_pair_id',$this->examiner_pair_id)
                    ->where('examiner_id',$examiner1->id)
                    ->where('is_review',1)
                    ->get();
        // dd($examine_tests);
        $num = 0;
        foreach($examine_tests as $item){
            $examinee_test_mix = ExamineeTestMix::where('examinee_test_code',$item->examinee_test_code)->first();
            
            $examinee_answer21 = AnswerKey::where('examinee_test_code',$item->examinee_test_code)->where('matrix_location',21)->first();
            // dd($examinee_answer21);
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
            // dd($_total_score222);
            $diff_score21 = abs($_total_score121 - $_total_score221)?abs($_total_score121 - $_total_score221):'0';
            $diff_score22 = abs($_total_score122 - $_total_score222)?abs($_total_score122 - $_total_score222):'0';

            if((!($_total_score121 && $_total_score221)) || ($diff_score21 <= 1 && $diff_score22 <= 1)) continue;
            $num = $num+1;
            $_data = [
                'num' => ($num),
                // 'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'pair_code' => $examiner_pair->code,
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phach
                'examiner1_name' => $examiner1->name,  //ho ten GK1
                // 'examiner1_account' => '',  //tk GK1
                'examiner1_score21' => $_total_score121?$_total_score121:'Chưa chấm',  //diem GK1 Doan
                'examiner1_score22' => $_total_score122?$_total_score122:'Chưa chấm',  //diem GK1 Bai
                'examiner2_name' => $examiner2->name,  //ho ten GK2
                // 'examiner2_account' => '',  //tk GK2
                'examiner2_score21' => $_total_score221?$_total_score221:'Chưa chấm',  //diem GK2 Doan
                'examiner2_score22' => $_total_score222?$_total_score222:'Chưa chấm',  //diem GK2 Bai
                'diff_score21' => ($_total_score121 && $_total_score221)?$diff_score21:'Chưa chấm xong',  //diem lech Doan
                'diff_score22' => ($_total_score122 && $_total_score222)?$diff_score22:'Chưa chấm xong',  //diem lech Bai
                // 'avg_score' => 0,  //diem TB
                // 'detail121' => $detail121,  //detail RR 21
                // 'detail221' => $detail221,  //detail RR 21
                // 'detail122' => $detail122,  //detail RR 22
                // 'detail222' => $detail222,  //detail RR 22
            ];

            //lay diem chi tiet rubric cuua  CBChT 1
            $rubric21 = Rubric::where('id',$examinee_answer21->rubric_id)->where('matrix_location',21)->first();
            $rubric_criteria_21 = RubricCriteria::where('rubric_id',$rubric21->id)->get();
            foreach($rubric_criteria_21 as $i => $item){
                // $header[] = 'TC' . ($i+1);
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
                // $header[] = 'TC' . ($i+1);
                $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$item->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$examinee_test_mix->examinee_test_code)->orderBy('id','desc')->first();
                $_score = $_detail?floatval($_detail->score):'0';
                $_data[] = $_score?$_score:'0';
                
            }
            $rubric22 = Rubric::where('id',$examinee_answer22->rubric_id)->where('matrix_location',22)->first();
            $rubric_criteria_22 = RubricCriteria::where('rubric_id',$rubric22->id)->get();
            foreach($rubric_criteria_22 as $i => $item){
                // $header[] = 'TC' . ($i+1);
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
            // 'SBD',
            'Mã cặp chấm',
            'Mã phách',
            'CBChT1',
            // 'Tài khoản',
            'Điểm CBChT1 Đoạn',
            'Điểm CBChT1 Bài',
            'CBChT2',
            // 'Tài khoản',
            'Điểm CBChT2 Đoạn',
            'Điểm CBChT2 Bài',
            'Điểm lệch Đoạn',
            'Điểm lệch Bài',
            // 'Điểm TB',
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
