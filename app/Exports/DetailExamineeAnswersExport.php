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
use App\Models\Question;
use App\Models\QuestionMark;
use App\Models\Rubric;

class DetailExamineeAnswersExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
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
        $num = 0;
        foreach($examine_tests as $obj){
            $num = $num + 1;
            if(!str_contains($obj->examinee_test_code, $council->code)) continue;
            $examinee_test_mix = ExamineeTestMix::where('examinee_test_code',$obj->examinee_test_code)->first();
            // if($examinee_test_mix->subject_id == 5) continue;
            $examinee = Examinee::where('code',$examinee_test_mix->examinee_code)->first();
            $subject = Subject::find($examinee_test_mix->subject_id);
            $council_turn = CouncilTurn::where('code',$examinee_test_mix->council_turn_code)->first();
            
            $total_score = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->sum('score');
            // $examinee_answers = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->orderBy('matrix_location','asc')->get();


            //lay du lieu chi tiet
            //bài làm thí sinh
            $_data = [
                'num' => $num,
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                'council_code' => $council->desc,  //HD thi
                'turn_code' => $council_turn->name,  //Ca thi
                'subject' => $subject->desc,  //Mon thi
                'tn1_score' => $total_score?$total_score:'0',
                'type' => 'Bài làm'
            ];
            for($i=1; $i<=40;$i++){
                $item = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',$i)->first();
                if(!$item || $item->rubric_id) {
                    $_data[] = ''; 
                    continue;
                }else {
                    $_data[] = $item->examinee_answer;
                }
            }
            $data[] = $_data;
            
            //điểm thí sinh đạt được
            $_data = [
                'num' => '',
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                'council_code' => $council->desc,  //HD thi
                'turn_code' => $council_turn->name,  //Ca thi
                'subject' => $subject->desc,  //Mon thi
                'tn1_score' => '',
                'type' => 'Điểm'
            ];
            for($i=1; $i<=40;$i++){
                $item = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',$i)->first();
                if(!$item){
                    $_data[] = ''; 
                    continue;
                }else {
                    $_data[] = $item->score?$item->score:'0';
                }
            }
            $data[] = $_data;
            
            //loại đáp án
            $_data = [
                'num' => '',
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                'council_code' => $council->desc,  //HD thi
                'turn_code' => $council_turn->name,  //Ca thi
                'subject' => $subject->desc,  //Mon thi
                'tn1_score' => '',
                'type' => 'Loại đáp án'
            ];
            for($i=1; $i<=40;$i++){
                $item = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',$i)->first();
                if(!$item){
                    $_data[] = ''; 
                    continue;
                }else{
                    $_data[] = ($item->answer_type != '1')?$item->answer_type:'';
                }
            }
            $data[] = $_data;

            //đáp án gốc
            $_data = [
                'num' => '',
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                'council_code' => $council->desc,  //HD thi
                'turn_code' => $council_turn->name,  //Ca thi
                'subject' => $subject->desc,  //Mon thi
                'tn1_score' => '',
                'type' => 'Đáp án gốc'
            ];
            for($i=1; $i<=40;$i++){
                $item = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',$i)->first();
                if(!$item){
                    $_data[] = ''; 
                    continue;
                }else{
                    $_data[] = $item->answer_key;
                }
            }
            $data[] = $_data;

            //điểm gốc
            $_data = [
                'num' => '',
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                'council_code' => $council->desc,  //HD thi
                'turn_code' => $council_turn->name,  //Ca thi
                'subject' => $subject->desc,  //Mon thi
                'tn1_score' => '',
                'type' => 'Điểm gốc'
            ];
            for($i=1; $i<=40;$i++){
                $item = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',$i)->first();
                if(!$item){
                    $_data[] = ''; 
                    continue;
                }else{
                    $mark = QuestionMark::find($item->question_mark_id);
                    $_data[] = $mark->value;
                }
            }
            $data[] = $_data;

            //mã câu hỏi
            $_data = [
                'num' => '',
                'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                'cccd' => $examinee->id_card_number,  //CCCD
                'birthday' => $examinee->birthday,  //Ngay sinh
                'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                'council_code' => $council->desc,  //HD thi
                'turn_code' => $council_turn->name,  //Ca thi
                'subject' => $subject->desc,  //Mon thi
                'tn1_score' => '',
                'type' => 'Mã câu hỏi'
            ];
            for($i=1; $i<=40;$i++){
                $item = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',$i)->first();
                if(!$item){
                    $_data[] = ''; 
                    continue;
                }else{
                    $question = Question::find($item->question_id);
                    $_data[] = $question->code;
                }
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
            'SBD',
            'Họ tên thí sinh',
            'CCCD',
            'Ngày sinh',
            'Mã phách',
            'Hội đồng thi',
            'Ca thi',
            'Môn thi',
            'Điểm',
            'Loại dữ liệu',
        ];
        for($i=1; $i<=40;$i++){
            $header[] = $i;
        }

        // dd($header);
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
