<?php

namespace App\Http\Controllers\API;
// use App\Http\Controllers\Controller as Controller;
use App\Http\Controllers\API\BaseController as BaseController;

use App\Models\Question;
use App\Models\Subject;
use App\Models\TestForm;
use App\Models\TestPart;
use App\Models\TestFormPart;
use App\Models\TestGroup;
use App\Models\TestGroupSubject;
use App\Models\Test;
use App\Models\TestRoot;
use App\Models\TestMix;
use App\Models\CouncilTurnTestMix;
use App\Models\Examinee;
use App\Models\Organization;
use App\Models\Council;
use App\Models\CouncilTurn;
use App\Models\CouncilTurnRoom;
use App\Models\ExamineeTestMix;
use App\Models\ExamineeAnswer;
use App\Models\ActivityLog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Notifications\Action;
use Ramsey\Uuid\Uuid;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use function Termwind\parse;

class TestController extends BaseController
{
    public function index()
    {
        return $this->sendResponse([],'Test API');
    }

    public function checkRoomStatus(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $examinee = Examinee::where('user_id',$user->id)->first();
        if(!$examinee){
            return response()->json(['error' => 'Examinee not found'], 400);
        }
        //lấy thông tin HĐ thi, ca thi, phòng thi của thí sinh
        $council_code = $examinee->council_code;
        $council_turn_code = $examinee->council_turn_code;
        $room_code = $examinee->room_code;
        $council = Council::where('code',$council_code)->first();
        $room = CouncilTurnRoom::where('council_turn_code',$council_turn_code)->where('room_code',$room_code)->first();
        if(!($council && $room)){
            return response()->json(['error' => 'HĐ thi hoặc Phòng thi không tồn tại!'], 400);
        }
        //kiểm tra phòng thi của thí sinh đã được kích hoạt thi hay chưa
        return $this->sendResponse([
            'is_active' => ($room->is_active || $council->is_autostart),
        ], 'Chect test is started');
    }

    public function getRemainingTime(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        $examinee = Examinee::where('user_id',$user->id)->first();
        if(!$examinee){
            return response()->json(['error' => 'Examinee not found'], 400);
        }
        
        $examinee_test_mix = ExamineeTestMix::where('examinee_code',$examinee->code)
                                            ->where('examinee_account',$user->email)
                                            ->first();
        //kiểm tra remaining time của thí sinh
        $remaining_time = $examinee_test_mix->expected_finish_time + $examinee_test_mix->bonus_time - time();
        if($remaining_time > 0){
            $examinee_test_mix->remaining_time = $remaining_time;
            $examinee_test_mix->save();
        }else{
            $remaining_time = 0;
            if($examinee_test_mix->remaining_time){
                $examinee_test_mix->remaining_time = 0;
                $examinee_test_mix->save();
            }
        }
        //kiểm tra phòng thi của thí sinh đã được kích hoạt thi hay chưa
        return $this->sendResponse([
            'remaining_time' => $remaining_time,
        ], 'Check remaining_time');
    }

    public function getTestById(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $examinee = Examinee::where('user_id',$user->id)->first();
        if(!$examinee){
            return response()->json(['error' => 'Examinee not found'], 400);
        }

        //lấy thông tin HĐ thi, ca thi, phòng thi của thí sinh
        $council_code = $examinee->council_code;
        $council_turn_code = $examinee->council_turn_code;
        $room_code = $examinee->room_code;
        $test_mix = null;
        $test_form = null;
        $council = Council::where('code',$council_code)->first();
        // $council_turn = CouncilTurn::where('code',$council_turn_code)->first();
        $room = CouncilTurnRoom::where('council_turn_code',$council_turn_code)->where('room_code',$room_code)->first();
        if(!($council && $room)){
            return response()->json(['error' => 'HĐ thi hoặc Phòng thi không tồn tại!'], 400);
        }
        //kiểm tra phòng thi của thí sinh đã được kích hoạt thi hay chưa
        if(!($room->is_active || $council->is_autostart)){
            return response()->json(['error' => 'Phòng thi chưa được kích hoạt thi!'], 400);
        }

        // $examinee_test_mix = ExamineeTestMix::where('examinee_code',$examinee->code)
        $examinee_test_mix = ExamineeTestMix::where('examinee_account',$user->email)
                                            ->where('council_code',$council_code)
                                            ->where('council_turn_code',$council_turn_code)
                                            ->where('room_code',$room_code)
                                            ->first();
        if(!$examinee_test_mix || !$examinee_test_mix->test_mix_id){
            //try to find a test mix which is available
            $_council_turn_test_mix = CouncilTurnTestMix::where('is_used',0)
                                ->where('subject_id',$examinee->subject_id)
                                ->where('council_code',$council_code)
                                ->where('council_turn_code',$council_turn_code)
                                ->inRandomOrder()
                                ->first();
            // if(!$_test_mix){
            if(!$_council_turn_test_mix){
                return response()->json(['error' => 'Test not found'], 400);
            }
            $test_mix = TestMix::find($_council_turn_test_mix->test_mix_id);
            if(!$test_mix){
                return response()->json(['error' => 'Test not found'], 400);
            }
            $test_form = TestForm::find($test_mix->test_form_id);
            $start_time = time();
            $examinee_test_mix = ExamineeTestMix::create([
                'examinee_test_uuid' => (string) Uuid::uuid4(),
                'examinee_code' => $examinee->code,
                'examinee_account' => $user->email,
                'test_mix_id' => $test_mix->id,
                'subject_id' => $examinee->subject_id,
                'council_code' => $council_code,
                'council_turn_code' => $council_turn_code,
                'room_code' => $room_code,
                'start_time' => $start_time,
                'expected_finish_time' => $start_time + ($test_form->time * 60),
                'finish_time' => 0,
                'remaining_time' => $start_time + ($test_form->time * 60),
                'answer_logs' => null,
            ]);
            $test_mix->is_used = 1;
            $test_mix->used_time = $start_time;
            $test_mix->save();
            $_council_turn_test_mix->is_used = 1;
            $_council_turn_test_mix->used_time = $start_time;
            $_council_turn_test_mix->save();

            //save activity log
            $log_time = time();
            $_council_code = $examinee_test_mix->council_code;
            $_council_turn_code = $examinee_test_mix->council_turn_code;
            $_room_code = $examinee_test_mix->room_code;
            $activity_log = ActivityLog::where('username',$user->email)
                                ->where('council_code',$_council_code)
                                ->where('council_turn_code',$_council_turn_code)
                                ->where('room_code',$_room_code)
                                ->where('action','START_TEST')
                                ->where('desc','test_mix_id: ' . $_council_turn_test_mix->test_mix_id)
                                ->first();
            if(!$activity_log){
                $log_uuid = (string) Uuid::uuid4();
                $activity_log = ActivityLog::create([
                    'log_uuid' => $log_uuid,
                    'username' => $user->email,
                    'council_code' => $_council_code,
                    'council_turn_code' => $_council_turn_code,
                    'room_code' => $_room_code,
                    'role_id' => 8,
                    'action' => 'START_TEST',
                    'desc' => 'test_mix_id: ' . $_council_turn_test_mix->test_mix_id,
                    'log_time' => $log_time,
                ]);
            }
        }

        $test_mix_id = $examinee_test_mix->test_mix_id;
        
        // $test_mix_id = 12; //van
        $jsonResponse = array();
        $_tmp = array(
            "question_id" => 0,
            "question_number" => 0,
            "question_type" => 0,
            "subject_id" => 0,
            "pre_content" => "",
            "context" => "",
            "content" => "",
            "post_content" => "",
            "max_words" => 0,
            "answer_type" => 0,
            "no_options" => 0,
            "options" => [],
            "option_order" => []
        );

        $test_mix = TestMix::find($test_mix_id);
        $test_form = TestForm::find($test_mix->test_form_id);
        $test_subject = Subject::find($test_mix->subject_id);
		$test_mix_content = json_decode($test_mix->content, TRUE);
        $question_number = 0;
        foreach($test_mix_content as $part_id => $questions){
            // $form_part = TestFormPart::find($part_id);
            // $part = TestPart::find($form_part->test_part_id);
            $part = TestPart::find($part_id);
            $part_title = '';
            $part_caltype = $part->caltype;
            foreach($questions as $location => $question){
                $question_number++;
                foreach($question as $question_id => $option_order){
                    $_question = $_tmp;
                    if($part_title == '') {
                        $part_title = $part->part_title;
                        $_question['pre_content'] = $part_title;
                    }
                    $question = Question::find($question_id);
                    $_question['question_id'] = $question_id;
                    $_question['question_number'] = $question_number;
                    $_question['question_type'] = $question->question_type_id;
                    $_question['subject_id'] = $question->subject_id;
                    if(strpos($question->content,'<mfenced>')){
                        $question->content = str_replace('<mfenced>','<mo>(</mo>',$question->content);
                        $question->content = str_replace('</mfenced>','<mo>)</mo>',$question->content);
                    }
                    if(strpos($question->content,'<mfenced close="" open="{">')){
                        $question->content = str_replace('<mfenced close="" open="{">','<mo>(</mo>',$question->content);
                        // $question->content = str_replace('</mfenced>','<mo>)</mo>',$question->content);
                    }
                    $_question['content'] = $question->content;
                    $_question['max_words'] = $question->max_words;
                    $_question['answer_type'] = $question->answer_type;
                    $_question['no_options'] = $question->no_options;
                    $_question['option_order'] = $option_order;
                    
                    foreach($option_order as $order){
                        if(strpos($question['option_' . $order],'<mfenced>')){
                            $question['option_' . $order] = str_replace('<mfenced>','<mo>(</mo>',$question['option_' . $order]);
                            $question['option_' . $order] = str_replace('</mfenced>','<mo>)</mo>',$question['option_' . $order]);
                        }
                        if(strpos($question['option_' . $order],'<mfenced close="" open="{">')){
                            $question['option_' . $order] = str_replace('<mfenced close="" open="{">','<mo>{</mo>',$question['option_' . $order]);
                            // $question->content = str_replace('</mfenced>','<mo>)</mo>',$question->content);
                        }
                        $_question['options'][] = $question['option_' . $order];
                    }
                    if ($part_caltype == 'auto_context'){
                        if($question->context_order == 1){
                            $context_question = Question::find($question->cloze_id);
                            if(strpos($context_question->pre_content,'<mfenced>')){
                                $context_question->pre_content = str_replace('<mfenced>','<mo>(</mo>',$context_question->pre_content);
                                $context_question->pre_content = str_replace('</mfenced>','<mo>)</mo>',$context_question->pre_content);
                            }
                            if(strpos($context_question->content,'<mfenced>')){
                                $context_question->content = str_replace('<mfenced>','<mo>(</mo>',$context_question->content);
                                $context_question->content = str_replace('</mfenced>','<mo>)</mo>',$context_question->content);
                            }
                            if(strpos($context_question->post_content,'<mfenced>')){
                                $context_question->post_content = str_replace('<mfenced>','<mo>(</mo>',$context_question->post_content);
                                $context_question->post_content = str_replace('</mfenced>','<mo>)</mo>',$context_question->post_content);
                            }
                            if(strpos($context_question->pre_content,'<mfenced close="" open="{">')){
                                $context_question->pre_content = str_replace('<mfenced close="" open="{">','<mo>(</mo>',$context_question->pre_content);
                            }
                            if(strpos($context_question->content,'<mfenced close="" open="{">')){
                                $context_question->content = str_replace('<mfenced close="" open="{">','<mo>(</mo>',$context_question->content);
                            }
                            if(strpos($context_question->post_content,'<mfenced close="" open="{">')){
                                $context_question->post_content = str_replace('<mfenced close="" open="{">','<mo>(</mo>',$context_question->post_content);
                            }
                            
                            $_question['pre_content'] = $context_question->pre_content;
                            $_question['context'] = $context_question->content . '<br>' . $context_question->post_content;
                        }
                    }
                    $jsonResponse[] = $_question;
                }
            }
        }

        //kiểm tra remaining time của thí sinh
        $remaining_time = $examinee_test_mix->expected_finish_time + $examinee_test_mix->bonus_time - time();
        if($remaining_time > 0){
            $examinee_test_mix->remaining_time = $remaining_time;
            $examinee_test_mix->save();
        }else{
            $remaining_time = 0;
            if($examinee_test_mix->remaining_time){
                $examinee_test_mix->remaining_time = 0;
                $examinee_test_mix->save();
            }
        }

        return $this->sendResponse([
            'test' => [
                'test_id' => $test_mix->id,
                'duration' => $test_form->time,
                'subject' => $test_subject->desc,
                'subject_id' => $test_mix->subject_id
            ],
            'examinee_test' => $examinee_test_mix,
            'questions' => $jsonResponse
        ], 'Get test content by id');
    }

    public function getExaminee(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $examinee = Examinee::where('user_id',$user->id)->first();
        if(!$examinee){
            return response()->json(['error' => 'Examinee not found'], 400);
        }
        $council = Council::where('code',$examinee->council_code)->first();
        $organization = Organization::where('code',$council->organization_code)->first();
        $turn = CouncilTurn::where('code',$examinee->council_turn_code)->first();
        $subject = Subject::find($examinee->subject_id);
        $duration = 90;

        return $this->sendResponse([
            'code' => $examinee->code,
            'id_card_number' => $examinee->id_card_number,
            'fullname' => $examinee->lastname . ' ' . $examinee->firstname,
            // 'birthday' => date('d/m/Y',strtotime($examinee->birthday)),
            'birthday' => $examinee->birthday,
            'council' => $council->desc,
            'location' => $organization->name,
            'turn_date' => date('d/m/Y',strtotime($turn->start_at)),
            'turn_time' => date('H:i',strtotime($turn->start_at)),
            'room' => $examinee->room_code,
            'subject' => $subject->desc,
            'duration' => $duration,
            'ip' => $request->ip(),
            'user_id' => $examinee->user_id,
        ], 'Get examinee');
    }

    public function postQuestionAnswer(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $examinee = Examinee::where('user_id',$user->id)->first();
        if(!$examinee){
            return $this->sendError([], 'Examinee is not found!');
        }
        $examinee_test_mix = ExamineeTestMix::where('examinee_code',$examinee->code)->where('examinee_account',$user->email)->first();
        if(!$examinee_test_mix || $examinee_test_mix->examinee_code != $request->input('examinee_code') || $examinee_test_mix->id != $request->input('examinee_test_id') || $examinee_test_mix->test_mix_id != $request->input('test_id')){
            return $this->sendError([], 'Examinee is not found!');
        }

        // $answer_id = $request->input('answer_id');
        $examinee_test_id = $request->input('examinee_test_id');
        $examinee_code = $request->input('examinee_code');
        $examinee_account = $user->email;
        $test_mix_id = $request->input('test_id');
        $subject_id = $request->input('subject_id');
        $question_id = $request->input('question_id');
        $question_type_id = $request->input('question_type_id');
        $question_number = $request->input('question_number');
        $answer_detail = $request->input('options');
        $logs = $request->input('logs');
        
        $submitting_time = time();
        //kiểm tra remaining time của thí sinh
        $remaining_time = $examinee_test_mix->expected_finish_time + $examinee_test_mix->bonus_time - time();
        if($remaining_time > 0){
            $examinee_test_mix->remaining_time = $remaining_time;
        }else{
            $remaining_time = 0;
            if($examinee_test_mix->remaining_time){
                $examinee_test_mix->remaining_time = 0;
            }
        }
        $examinee_test_mix->save();
        
        // $examinee_answer = ExamineeAnswer::find($answer_id);
        $examinee_answer = ExamineeAnswer::where('examinee_code',$examinee_code)
                                        ->where('examinee_account',$examinee_account)
                                        ->where('question_id',$question_id)
                                        ->where('question_type_id',$question_type_id)
                                        ->where('test_mix_id',$test_mix_id)
                                        ->where('subject_id',$subject_id)
                                        ->first();
        if(!$examinee_answer) {
            $examinee_answer_uuid = (string) Uuid::uuid4();
            $examinee_test = ExamineeTestMix::find($examinee_test_id);
            $examinee_answer = ExamineeAnswer::create([
                'examinee_test_uuid' => $examinee_test->examinee_test_uuid,
                'examinee_answer_uuid' => $examinee_answer_uuid,
                'examinee_code' => $examinee_code,
                'examinee_account' => $examinee_account,
                'test_mix_id' => $test_mix_id,
                'subject_id' => $subject_id,
                'question_id' => $question_id,
                'question_type_id' => $question_type_id,
                'question_number' => $question_number,
                'answer_detail' => $answer_detail,
                'submitting_time' => $submitting_time,
                'remaining_time' => $remaining_time,
            ]);
        }else{
            if(!$examinee_answer->examinee_test_uuid){
                $examinee_answer->examinee_test_uuid = $examinee_test_mix->examinee_test_uuid;
            }
            $examinee_answer->answer_detail = $answer_detail;
            $examinee_answer->submitting_time = $submitting_time;
            $examinee_answer->remaining_time = $remaining_time;
            $examinee_answer->save();
        }

        $examinee_test_mix->remaining_time = $remaining_time;
        $examinee_test_mix->answer_logs = $logs;
        $examinee_test_mix->save();
        
        return $this->sendResponse([
            'answer_id' => $examinee_answer->id,
            'examinee_code' => $examinee_code,
            'test_id' => $test_mix_id,
            'question_id' => $question_id,
            'question_type_id' => $question_type_id,
            'subject_id' => $subject_id,
            'options' => $answer_detail,
            'question_number' => $question_number,
            'remaining_time' => $remaining_time,
        ], 'Post question answer');
    }

    public function saveAnswerLogs(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $examinee = Examinee::where('user_id',$user->id)->first();
        if(!$examinee){
            return $this->sendError([], 'Examinee is not found!');
        }
        $examinee_test_mix = ExamineeTestMix::where('examinee_code',$examinee->code)->where('examinee_account',$user->email)->where('subject_id',$request->input('subject_id'))->first();
        if(!$examinee_test_mix || $examinee_test_mix->examinee_code != $request->input('examinee_code') || $examinee_test_mix->id != $request->input('examinee_test_id') || $examinee_test_mix->test_mix_id != $request->input('test_id')){
            return $this->sendError([], 'Examinee is not found!');
        }

        // $examinee_test_id = $request->input('examinee_test_id');
        $examinee_code = $request->input('examinee_code');
        $examinee_account = $user->email;
        $test_mix_id = $request->input('test_id');
        $subject_id = $request->input('subject_id');
        $logs = $request->input('logs');
        
        $submitting_time = time();

        //lưu dữ liệu nộp bài
        // $examinee_test_mix->finish_time = $submitting_time;
        //kiểm tra remaining time của thí sinh
        $remaining_time = $examinee_test_mix->expected_finish_time + $examinee_test_mix->bonus_time - time();
        if($remaining_time > 0){
            $examinee_test_mix->remaining_time = $remaining_time;
        }else{
            $remaining_time = 0;
            if($examinee_test_mix->remaining_time){
                $examinee_test_mix->remaining_time = 0;
            }
        }
        $examinee_test_mix->answer_logs = $logs;

        $examinee_test_mix->save();

        //duyệt lại các câu trả lời đã lưu và so sánh với số câu trả lời trong logs
        //nếu lưu thiếu hoặc có sự khác biệt trong câu trả lời thì lấy phiên bản trong logs từ local (đồng bộ)
        $answer_logs = array();
        if($logs) $answer_logs = json_decode($logs,TRUE);
        // $answer_logs = $logs;
        if(!isset($answer_logs)) $answer_logs = array();
        foreach($answer_logs as $log){
            // $answer_id = $log['answer_id'];
            if(!$log) continue;
            $answer = ExamineeAnswer::where('examinee_code',$log['examinee_code'])
                                    ->where('examinee_account',$examinee_account)
                                    ->where('question_id',$log['question_id'])
                                    ->first();
            if(!$answer) {
                $answer_uuid = (string) Uuid::uuid4();
                $answer = ExamineeAnswer::create([
                    'examinee_test_uuid' => $examinee_test_mix->examinee_test_uuid,
                    'examinee_answer_uuid' => $answer_uuid,
                    'examinee_code' => $log['examinee_code'],
                    'examinee_account' => $examinee_account,
                    'test_mix_id' => $log['test_id'],
                    'subject_id' => $log['subject_id'],
                    'question_id' => $log['question_id'],
                    'question_type_id' => $log['question_type_id'],
                    'question_number' => $log['question_number'],
                    'answer_detail' => $log['options'],
                    'submitting_time' => $submitting_time,
                    'remaining_time' => $remaining_time,
                ]);
            }else{
                if(!$answer->examinee_test_uuid || $answer->answer_detail != $log['options']){
                    $answer['examinee_test_uuid'] = $examinee_test_mix->examinee_test_uuid;
                    $answer->answer_detail = $log['options'];
                    $answer->submitting_time = $submitting_time;
                    $answer->remaining_time = $remaining_time;
                    $answer->save();
                }
            }
        }
        //save activity log
        $log_time = time();
        $council_code = $examinee_test_mix->council_code;
        $council_turn_code = $examinee_test_mix->council_turn_code;
        $room_code = $examinee_test_mix->room_code;
        $activity_log = ActivityLog::where('username',$user->email)
                            ->where('council_code',$council_code)
                            ->where('council_turn_code',$council_turn_code)
                            ->where('room_code',$room_code)
                            ->where('action','ANSWER_LOGS')
                            ->where('desc',$logs)
                            ->first();
        if(!$activity_log){
            $log_uuid = (string) Uuid::uuid4();
            $activity_log = ActivityLog::create([
                'log_uuid' => $log_uuid,
                'username' => $user->email,
                'council_code' => $council_code,
                'council_turn_code' => $council_turn_code,
                'room_code' => $room_code,
                'role_id' => 8,
                'action' => 'ANSWER_LOGS',
                'desc' => $logs,
                'log_time' => $log_time,
            ]);
        }else{
            if($activity_log->desc != $logs){
                $activity_log->desc = $logs;
                $activity_log->log_time = $log_time;
                $activity_log->save();
            }
        }
        
        return $this->sendResponse([
            'examinee_test_id' => $examinee_test_mix->id,
            'examinee_code' => $examinee_code,
            'test_id' => $test_mix_id,
            'subject_id' => $subject_id,
            'remaining_time' => $remaining_time,
            'bonus_time' => $examinee_test_mix->bonus_time,
            'logs' => $logs,
            'count_success' => count($answer_logs)
        ], 'Lưu log thí sinh!');
    }

    public function submitTest(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $examinee = Examinee::where('user_id',$user->id)->first();
        if(!$examinee){
            return $this->sendError([], 'Examinee is not found!');
        }
        $examinee_test_mix = ExamineeTestMix::where('examinee_code',$examinee->code)->where('examinee_account',$user->email)->where('subject_id',$request->input('subject_id'))->first();
        if(!$examinee_test_mix || $examinee_test_mix->examinee_code != $request->input('examinee_code') || $examinee_test_mix->id != $request->input('examinee_test_id') || $examinee_test_mix->test_mix_id != $request->input('test_id')){
            return $this->sendError([], 'Examinee is not found!');
        }

        // $examinee_test_id = $request->input('examinee_test_id');
        $examinee_code = $request->input('examinee_code');
        $examinee_account= $user->email;
        $test_mix_id = $request->input('test_id');
        $subject_id = $request->input('subject_id');
        $logs = $request->input('logs');
        
        $submitting_time = time();

        //lưu dữ liệu nộp bài
        $examinee_test_mix->finish_time = $submitting_time;
        //kiểm tra remaining time của thí sinh
        $remaining_time = $examinee_test_mix->expected_finish_time + $examinee_test_mix->bonus_time - time();
        if($remaining_time > 0){
            $examinee_test_mix->remaining_time = $remaining_time;
        }else{
            $remaining_time = 0;
            if($examinee_test_mix->remaining_time){
                $examinee_test_mix->remaining_time = 0;
            }
        }
        $examinee_test_mix->answer_logs = $logs;

        $examinee_test_mix->save();

        //duyệt lại các câu trả lời đã lưu và so sánh với số câu trả lời trong logs
        //nếu lưu thiếu hoặc có sự khác biệt trong câu trả lời thì lấy phiên bản trong logs từ local (đồng bộ)
        $answer_logs = array();
        if($logs) $answer_logs = json_decode($logs,TRUE);
        if(!isset($answer_logs)) $answer_logs = array();
        // $answer_logs = $logs;
        foreach($answer_logs as $log){
            $answer_id = $log['answer_id'];
            $answer = ExamineeAnswer::where('id',$answer_id)
                                    ->where('examinee_code',$log['examinee_code'])
                                    ->where('examinee_account',$examinee_account)
                                    ->where('question_id',$log['question_id'])
                                    ->first();
            if(!$answer) {
                $answer_uuid = (string) Uuid::uuid4();
                $answer = ExamineeAnswer::create([
                    'examinee_test_uuid' => $examinee_test_mix->examinee_test_uuid,
                    'examinee_answer_uuid' => $answer_uuid,
                    'examinee_code' => $log['examinee_code'],
                    'examinee_account' => $examinee_account,
                    'test_mix_id' => $log['test_id'],
                    'subject_id' => $log['subject_id'],
                    'question_id' => $log['question_id'],
                    'question_type_id' => $log['question_type_id'],
                    'question_number' => $log['question_number'],
                    'answer_detail' => $log['options'],
                    'submitting_time' => $submitting_time,
                    'remaining_time' => $remaining_time,
                ]);
            }else{
                if(!$answer->examinee_test_uuid || $answer->answer_detail != $log['options']){
                    $answer['examinee_test_uuid'] = $examinee_test_mix->examinee_test_uuid;
                    $answer->answer_detail = $log['options'];
                    $answer->submitting_time = $submitting_time;
                    $answer->remaining_time = $remaining_time;
                    $answer->save();
                }
            }
        }

        //save activity log
        $log_time = time();
        $_council_code = $examinee_test_mix->council_code;
        $_council_turn_code = $examinee_test_mix->council_turn_code;
        $_room_code = $examinee_test_mix->room_code;
        $activity_log = ActivityLog::where('username',$user->email)
                            ->where('council_code',$_council_code)
                            ->where('council_turn_code',$_council_turn_code)
                            ->where('room_code',$_room_code)
                            ->where('action','SUBMIT_TEST')
                            ->first();
        if(!$activity_log){
            $log_uuid = (string) Uuid::uuid4();
            $activity_log = ActivityLog::create([
                'log_uuid' => $log_uuid,
                'username' => $user->email,
                'council_code' => $_council_code,
                'council_turn_code' => $_council_turn_code,
                'room_code' => $_room_code,
                'role_id' => 8,
                'action' => 'SUBMIT_TEST',
                'desc' => $logs,
                'log_time' => $log_time,
            ]);
        }
        
        return $this->sendResponse([
            'examinee_test_id' => $examinee_test_mix->id,
            'examinee_code' => $examinee_code,
            'test_id' => $test_mix_id,
            'subject_id' => $subject_id,
            'finish_time' => $submitting_time,
            'remaining_time' => $remaining_time,
            'logs' => $logs,
            'count_success' => count($answer_logs)
        ], 'Nộp bài thành công!');
    }

    public function getAnswerLogs(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $examinee = Examinee::where('user_id',$user->id)->first();
        if(!$examinee){
            return response()->json(['error' => 'Examinee not found'], 400);
        }

        //lấy thông tin HĐ thi, ca thi, phòng thi của thí sinh
        $council_code = $examinee->council_code;
        $council_turn_code = $examinee->council_turn_code;
        $room_code = $examinee->room_code;
        $council = Council::where('code',$council_code)->first();
        $room = CouncilTurnRoom::where('council_turn_code',$council_turn_code)->where('room_code',$room_code)->first();
        if(!($council && $room)){
            return response()->json(['error' => 'HĐ thi hoặc Phòng thi không tồn tại!'], 400);
        }

        $examinee_test_mix = ExamineeTestMix::where('examinee_code',$examinee->code)
                                            ->where('examinee_account',$user->email)
                                            ->where('council_code',$council_code)
                                            ->where('council_turn_code',$council_turn_code)
                                            ->where('room_code',$room_code)
                                            ->first();
        if($examinee_test_mix){
            if($examinee_test_mix->answer_logs){
                return $this->sendResponse([
                    'answer_logs' => json_decode($examinee_test_mix->answer_logs,TRUE)
                ], 'Get Answer logs!');
            }
            $activity_log = ActivityLog::where('username',$user->email)->where('action','ANSWER_LOGS')->orderBy('log_time','desc')->first();
            if($activity_log){
                if($examinee_test_mix->answer_logs){
                    return $this->sendResponse([
                        'answer_logs' => json_decode($activity_log->desc,TRUE)
                    ], 'Get Answer logs!');
                }
            }
        }
        return $this->sendResponse([
            'answer_logs' => null
        ], 'Get Answer logs!');
    }
}