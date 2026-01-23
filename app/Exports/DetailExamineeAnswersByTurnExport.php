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
use App\Models\Question;
use App\Models\QuestionMark;
use App\Models\QuestionType;
use App\Models\Rubric;
use App\Models\Test;
use App\Models\TestMix;
use App\Models\TestForm;

class DetailExamineeAnswersByTurnExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
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
        $num = 0;
        foreach($examine_tests as $obj){
            $num = $num + 1;
            if(!str_contains($obj->examinee_test_code, $council_turn->code)) continue;
            $examinee_test_mix = ExamineeTestMix::where('examinee_test_code',$obj->examinee_test_code)->first();
            $test_mix = TestMix::find($examinee_test_mix->test_mix_id);
            $test_form = TestForm::find($test_mix->test_form_id);
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
            // $examinee_answers = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->orderBy('matrix_location','asc')->get();

            //lay du lieu chi tiet
            //bài làm thí sinh
            // dd($obj->examinee_test_code);
            $_questions = array();
            $test_mix_content = json_decode($test_mix->content,TRUE);
            foreach($test_mix_content as $part_id => $questions){
                foreach($questions as $location => $question){
                    foreach($question as $question_id => $option_order){
                        $_questions[] = $question_id;
                    }
                }
            }
            // dd(($_questions));
            $_questions = array_unique($_questions);

            // $num_q = ($examinee_test_mix->subject_id == 5)?22:40;
            $num_q = ($test_form)?$test_form->no_questions:0;
            for($i=1; $i<=$num_q;$i++){
                $_data = [
                    'num' => $i,
                    'examinee_code' => $examinee_test_mix->examinee_code,  //SBD
                    'examinee_name' => $examinee->lastname . ' ' . $examinee->firstname,  //Ho ten thi sinh
                    'cccd' => $examinee->id_card_number,  //CCCD
                    'birthday' => $examinee->birthday,  //Ngay sinh
                    'test_code' => $examinee_test_mix->examinee_test_code,  //ma phachm
                    'council_code' => $council_turn->council_code,  //HD thi
                    'turn_code' => $council_turn->code,  //Ca thi
                    'room' => $room->name,  //Phong thi
                    'subject' => $subject->desc,  //Mon thi
                    'tn1_score' => $total_score?$total_score:'0',
                	'test_root_id' => $test_mix->test_root_id,
		];
                $item = AnswerKey::where('examinee_test_code',$obj->examinee_test_code)->where('matrix_location',$i)->first();
                if(!$item) {
                    // neu thi sinh khong lam cau nay thi van show thong tin cau hoi
                    $_question = Question::find($_questions[$i-1]);
                    $_question_mark = QuestionMark::find($_question->question_mark_id);
                    $_question_type = QuestionType::find($_question->question_type_id);
                    $_data[] = strval($_question->code);
                    $_data[] = strval($i);
                    $_data[] = strval($_question_type->code);
                    $_data[] = ($_question->question_type_id != 5)?strval($_question_mark->value):'0';
                    $_data[] = ($_question->question_type_id != 5)?strval($_question->answer_key):'';
                    $_data[] = '';
                    $_data[] = '0';
                    $_data[] = strval(($_question->answer_type != '1')?$_question->answer_type:'');
                }else {
                    // $_question = Question::find($_questions[$i-1]);
                    $_question = Question::find($item->question_id);
                    $_question_mark = QuestionMark::find($item->question_mark_id);
                    $_question_type = QuestionType::find($item->question_type_id);
                    $_data[] = strval($_question->code);
                    $_data[] = strval($item->matrix_location);
                    $_data[] = strval($_question_type->code);
                    $_data[] = ($item->rubric_id == 0)?strval($_question_mark->value):'0';
                    $_data[] = ($item->rubric_id == 0)?strval($item->answer_key):'';
                    $_data[] = ($item->rubric_id == 0)?strval($item->examinee_answer):'';
                    $_data[] = $item->score?strval($item->score):'0';
                    $_data[] = strval(($item->answer_type != '1')?$item->answer_type:'');
                }
                $data[] = $_data;
            }
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
		'Mã đề gốc',
            'Mã câu hỏi',
            'Vị trí câu hỏi',
            'Loại câu hỏi',
            'Điểm gốc',
            'Đáp án gốc',
            'Đáp án TS',
            'Điểm TS',
            'Loại đáp án',
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
