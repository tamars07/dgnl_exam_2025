<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;

use App\Models\Organization;
use App\Models\Council;
use App\Models\CouncilTurn;
use App\Models\CouncilTurnRoom;
use App\Models\Room;
use App\Models\User;
use App\Models\Monitor;
use App\Models\Subject;
use App\Models\Examinee;
use App\Models\ExamineeTestMix;
use App\Models\ExamineeAnswer;
use App\Models\Question;
use App\Models\TestMix;
use App\Models\ActivityLog;
use App\Models\AnswerKey;
use App\Models\ExaminerAssignment;
use App\Models\ExaminerRubricDetail;
use App\Models\Rubric;
use App\Models\RubricCriteria;
use App\Models\ExaminerPair;
use App\Models\ExaminerPairDetail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ExamController extends BaseController
{
    public function index()
    {
        return $this->sendResponse([],'Test API');
    }

    public function getMonitor(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $monitor = Monitor::where('user_id',$user->id)->first();
        if(!$monitor){
            return response()->json(['error' => 'Monitor not found'], 400);
        }
        // $monitor_council_room = CouncilTurnRoom::where('monitor_code',$monitor->code)->first();
        // if(!$monitor_council_room){
        //     return response()->json(['error' => 'Monitor room not found'], 400);
        // }
        // $turn = CouncilTurn::where('code',$monitor->council_turn_code)->first();
        // $room = Room::where('code',$monitor->room_code)->first();

        return $this->sendResponse([
            'code' => $monitor->code,
            'name' => $monitor->name
        ], 'Get monitor');
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
        ], 'Check test is started');
    }

    public function activeRoom(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $_roles = $user->roles;
        $role_id = 4;
        $role_name = '';
        foreach($_roles as $role){
            $role_id = $role->id;
            $role_name = $role->name;
        }
        if($request->input('turn') && $request->input('room')){
            $monitor = Monitor::where('user_id',$user->id)->first();
            if($monitor->role_id == 4){
                $room = CouncilTurnRoom::where('council_turn_code',$request->input('turn'))
                                        ->where('monitor_code',$user->email)
                                        ->where('room_code',$request->input('room'))
                                        ->first();
            }
            if($monitor->role_id == 7){
                $room = CouncilTurnRoom::where('council_turn_code',$request->input('turn'))
                                        ->where('room_code',$request->input('room'))
                                        ->first();
            }
            if($room){
                if(!$room->is_active){
                    $room->is_active = true;
                    $room->save();

                    //save activity log
                    $log_uuid = (string) Uuid::uuid4();
                    $log_time = time();
                    $log_message = $request->input('message');
                    $council_turn = CouncilTurn::where('code',$request->input('turn'))->first();
                    $council = Council::where('code',$council_turn->council_code)->first();
                    ActivityLog::create([
                        'log_uuid' => $log_uuid,
                        'username' => $user->email,
                        'council_code' => $council->code,
                        'council_turn_code' => $council_turn->code,
                        'room_code' => $request->input('room'),
                        'role_id' => $role_id,
                        'action' => 'ACTIVE_ROOM',
                        'desc' => 'Room is actived by ' . $role_name,
                        'log_time' => $log_time,
                    ]);

                    return $this->sendResponse([
                        'code' => 0,
                        'turn' => $request->input('turn'),
                        'room' => $request->input('room')
                    ], 'Room is activated!');
                }
                return $this->sendResponse([
                    'code' => 1,
                    'turn' => $request->input('turn'),
                    'room' => $request->input('room')
                ], 'Room is already activated!');
            }
        }
    
        return $this->sendError([
        ], 'Room is not found!');
    }

    public function addBonusTime(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $_roles = $user->roles;
        $role_id = 4;
        $role_name = '';
        foreach($_roles as $role){
            $role_id = $role->id;
            $role_name = $role->name;
        }
        if($request->input('turn') && $request->input('room') && $request->input('account') && $request->input('time')){
            $check = CouncilTurnRoom::where('council_turn_code',$request->input('turn'))
                                    // ->where('monitor_code',$user->email)
                                    ->where('room_code',$request->input('room'))
                                    ->count();
            if($check){
                $examinee_test = ExamineeTestMix::where('examinee_account',$request->input('account'))
                                                ->where('council_turn_code',$request->input('turn'))
                                                ->where('room_code',$request->input('room'))
                                                ->first();
                //
                if(!$examinee_test){
                    return $this->sendError([], 'Examinee is not found!');
                }
                //add bonus time in seconds
                $examinee_test->bonus_time += (intval($request->input('time')) * 60);
                //calc remaining time of examinee
                $remaining_time = $examinee_test->expected_finish_time + $examinee_test->bonus_time - time();
                if($remaining_time > 0){
                    $examinee_test->remaining_time = $remaining_time;
                }else{
                    $remaining_time = 0;
                    if($examinee_test->remaining_time){
                        $examinee_test->remaining_time = 0;
                    }
                }
                $examinee_test->save();

                //save activity log
                $log_uuid = (string) Uuid::uuid4();
                $log_time = time();
                $log_message = $request->input('message');
                $council_turn = CouncilTurn::where('code',$request->input('turn'))->first();
                $council = Council::where('code',$council_turn->council_code)->first();
                ActivityLog::create([
                    'log_uuid' => $log_uuid,
                    'username' => $user->email,
                    'council_code' => $council->code,
                    'council_turn_code' => $council_turn->code,
                    'room_code' => $request->input('room'),
                    'role_id' => $role_id,
                    'action' => 'ADD_TIME',
                    'desc' => json_encode([
                        'examinee_account' => $request->input('account'),
                        'add_time' => $request->input('time') . ' minutes',
                        'monitor_role' => $role_name,
                        'message' => $log_message
                    ]),
                    'log_time' => $log_time,
                ]);

                return $this->sendResponse([], 'Examinee has been added time!');
            }
        }
    
        return $this->sendError([], 'Examinee is not found!');
    }

    public function restoreExaminee(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $_roles = $user->roles;
        $role_id = 4;
        foreach($_roles as $role){
            $role_id = $role->id;
        }
        if($request->input('turn') && $request->input('room') && $request->input('account')){
            $check = CouncilTurnRoom::where('council_turn_code',$request->input('turn'))
                                    // ->where('monitor_code',$user->email)
                                    ->where('room_code',$request->input('room'))
                                    ->count();
            if($check){
                //reset ip address
                $account = User::where('email',$request->input('account'))->first();
                if(!$account){
                    return $this->sendError([], 'Examinee is not found!');
                }
                $examinee = Examinee::where('user_id',$account->id)->first();
                if(!$examinee){
                    return $this->sendError([], 'Examinee is not found!');
                }
                if($account->ip_address){
                    $account->ip_address = null;
                    $account->save();
                }
                $examinee_test = ExamineeTestMix::where('examinee_account',$request->input('account'))
                                                ->where('council_turn_code',$request->input('turn'))
                                                ->where('room_code',$request->input('room'))
                                                ->first();
                if($examinee_test && $examinee_test->finish_time){
                    $examinee_test->finish_time = 0;
                    $examinee_test->save();
                }
                //save activity log
                $log_uuid = (string) Uuid::uuid4();
                $log_time = time();
                $log_message = $request->input('message');
                $council_turn = CouncilTurn::where('code',$request->input('turn'))->first();
                $council = Council::where('code',$council_turn->council_code)->first();
                ActivityLog::create([
                    'log_uuid' => $log_uuid,
                    'username' => $user->email,
                    'council_code' => $council->code,
                    'council_turn_code' => $council_turn->code,
                    'room_code' => $request->input('room'),
                    'role_id' => $role_id,
                    'action' => 'RESTORE',
                    'desc' => json_encode([
                        'examinee_account' => $request->input('account'),
                        'message' => $log_message
                    ]),
                    'log_time' => $log_time,
                ]);

                return $this->sendResponse([], 'Examinee has been restored!');
            }
        }
    
        return $this->sendError([], 'Examinee is not found!');
    }

    public function submitTest(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        // $_roles = $user->roles;
        if($request->input('turn') && $request->input('room') && $request->input('account')){
            $check = CouncilTurnRoom::where('council_turn_code',$request->input('turn'))
                                    // ->where('monitor_code',$user->email)
                                    ->where('room_code',$request->input('room'))
                                    ->count();
            if($check){
                $examinee_test = ExamineeTestMix::where('examinee_account',$request->input('account'))
                                                ->where('council_turn_code',$request->input('turn'))
                                                ->where('room_code',$request->input('room'))
                                                ->first();
                //
                if(!$examinee_test){
                    return $this->sendError([], 'Examinee is not found!');
                }
                
                //submit test by monitor
                $examinee_test->finish_time = time();
                $examinee_test->save();

                //save activity log
                $log_uuid = (string) Uuid::uuid4();
                $log_time = time();
                $log_message = $request->input('message');
                $council_turn = CouncilTurn::where('code',$request->input('turn'))->first();
                $council = Council::where('code',$council_turn->council_code)->first();
                ActivityLog::create([
                    'log_uuid' => $log_uuid,
                    'username' => $user->email,
                    'council_code' => $council->code,
                    'council_turn_code' => $council_turn->code,
                    'room_code' => $request->input('room'),
                    'role_id' => 7,
                    'action' => 'SUBMIT TEST BY MONITOR',
                    'desc' => json_encode([
                        'examinee_account' => $request->input('account'),
                        'message' => $log_message
                    ]),
                    'log_time' => $log_time,
                ]);

                return $this->sendResponse([], 'Examinee has been submitted by monitor!');
            }
        }
        return $this->sendError([], 'Examinee is not found!');
    }

    public function getAvailableCouncilToday(Request $request){
        // DB::connection()->enableQueryLog();
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        // $_roles = $user->roles;
        $monitor = Monitor::where('user_id',$user->id)->first();
        $councils = Council::whereNowOrPast('start_at')
                            ->whereNowOrFuture('finish_at')
                            ->get();
        $council_arr = array();
        foreach($councils as $council){
            if($monitor->role_id == 7){
                //chủ tịch: chỉ lấy các hội đồng mà user làm chủ tịch
                if($council->monitor_id != $monitor->id) continue;
            }
            else if($monitor->role_id == 4){
                //giám thị: chỉ lấy các hội đồng mà user làm có ca gác thi
                $check = CouncilTurnRoom::where('monitor_code',$monitor->code)
                                        ->whereLike('council_turn_code', '%' . $council->code . '%')
                                        ->count();
                if(!$check) continue;
            }
            $council_arr[] = [
                'code' => $council->code,
                'name' => $council->desc,
            ];
        }
        return $this->sendResponse([
            // 'user' => $user,
            'data' => $council_arr,
            // 'query' => end(DB::getQueryLog())
        ], 'Get council turn by monitor');
    }

    public function getCouncilTurnByMonitor(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        // $_roles = $user->roles;
        $turns = array();
        if($request->input('council')){
            $monitor = Monitor::where('user_id',$user->id)->first();
            // monitor role
            if($monitor->role_id == 4){
                $council_turn_rooms = CouncilTurnRoom::where('monitor_code',$user->email)->get();
                foreach($council_turn_rooms as $turn){
                    $council_turn = CouncilTurn::where('code',$turn->council_turn_code)
                                                ->where('council_code',$request->input('council'))
                                                ->whereDate('start_at',date('Y-m-d'))
                                                ->first();
                    if(!$council_turn) continue;
                    $turns[] = [
                        'code' => $council_turn->code,
                        'name' => date('d/m/Y H:i', strtotime($council_turn->start_at))
                    ];
                }
            }
            // chairman role
            if($monitor->role_id == 7){
                $council_turns = CouncilTurn::where('council_code',$request->input('council'))
                                            ->get();
                foreach($council_turns as $turn){
                    $turns[] = [
                        'code' => $turn->code,
                        'name' => date('d/m/Y H:i', strtotime($turn->start_at))
                    ];
                }
            }
        }
    
        return $this->sendResponse([
            // 'user' => $user,
            'data' => $turns,
        ], 'Get council turn by monitor');
    }

    public function getRoomByMonitor(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        // $_roles = $user->roles;
        $rooms = array();
        if($request->input('turn')){
            $monitor = Monitor::where('user_id',$user->id)->first();
            // monitor role
            if($monitor->role_id == 4){
                $council_turn_rooms = CouncilTurnRoom::where('monitor_code',$user->email)->where('council_turn_code',$request->input('turn'))->get();
                foreach($council_turn_rooms as $room){
                    $rooms[] = [
                        'code' => $room->room_code,
                        'name' => $room->room_code,
                    ];
                }
            }
            // chairman role
            if($monitor->role_id == 7){
                $council_turn_rooms = CouncilTurnRoom::where('council_turn_code',$request->input('turn'))->get();
                foreach($council_turn_rooms as $room){
                    $rooms[] = [
                        'code' => $room->room_code,
                        'name' => $room->room_code,
                    ];
                }
            }
        }
    
        return $this->sendResponse([
            // 'user' => $user,
            'data' => $rooms,
            'monitor' => $user->email
        ], 'Get room by monitor');
    }

    public function getExamineeListByMonitorRoom(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        // $_roles = $user->roles;
        $examinee_arr = array();
        $turn = null;
        $is_active = false;
        if($request->input('turn') && $request->input('room')){
            $monitor = Monitor::where('user_id',$user->id)->first();
            // monitor role
            if($monitor->role_id == 4){
                $room = CouncilTurnRoom::where('council_turn_code',$request->input('turn'))
                                        ->where('monitor_code',$user->email)
                                        ->where('room_code',$request->input('room'))
                                        ->first();
            }
            // chairman role
            if($monitor->role_id == 7){
                $room = CouncilTurnRoom::where('council_turn_code',$request->input('turn'))
                                        ->where('room_code',$request->input('room'))
                                        ->first();
            }

            if($room){
                $turn = CouncilTurn::where('code',$request->input('turn'))->first();
                $council = Council::where('code',$turn->council_code)->first();
                $examinees = Examinee::where('council_turn_code',$request->input('turn'))
                                    ->where('room_code',$request->input('room'))
                                    ->get();
                $is_active = ($room->is_active || $council->is_autostart);
                foreach($examinees as $examinee){
                    $account = User::find($examinee->user_id);
                    if(!$account) continue;
                    $subject = Subject::find($examinee->subject_id);
                    $status = "CHƯA ĐĂNG NHẬP";
                    if($account->ip_address){
                        $examinee_test = ExamineeTestMix::where('examinee_account',$account->email)->first();
                        if(!$examinee_test){
                            $status = "CHỜ THI";
                        }else{
                            $status = "ĐANG THI";
                            if($examinee_test->finish_time){
                                $status = "ĐÃ NỘP BÀI";
                            }
                        }
                    }else{
                        $examinee_test = ExamineeTestMix::where('examinee_account',$account->email)->first();
                        if(!$examinee_test){
                            $status = "CHƯA ĐĂNG NHẬP";
                        }else{
                            $status = "ĐANG THI";
                            if($examinee_test->finish_time){
                                $status = "ĐÃ NỘP BÀI";
                            }
                        }
                    }
                    $examinee_arr[] = [
                        'username' => $account->email,
                        'code' => $examinee->code,
                        'id_card_number' => $examinee->id_card_number,
                        'fullname' => $examinee->lastname . ' ' . $examinee->firstname,
                        'birthday' => $examinee->birthday,
                        'subject' => $subject->desc,
                        'seat_number' => $examinee->seat_number,
                        'ip_address' => $account->ip_address,
                        'is_backup' => $examinee->is_backup,
                        'status' => $status
                    ];
                }
            }
        }
        
        return $this->sendResponse([
            'turn' => [
                'date' => date('d/m/Y',strtotime($turn->start_at)),
                'time' => date('H:i',strtotime($turn->start_at)),
                'is_active' => $is_active
            ],
            'examinees' => $examinee_arr,
        ], 'Get examinee list by monitor');
    }

    public function _getExamineeDetail(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $monitor = Monitor::where('user_id',$user->id)->first();
        $examinee_details = array();
        $answer_label = [
            0 => 'A',
            1 => 'B',
            2 => 'C',
            3 => 'D',
            4 => 'E',
            5 => 'F',
            6 => 'G',
            7 => 'H',
        ];
        $context_content = '';
        if($request->input('account')){
            $examinee_account = $request->input('account'); //mã phách

            $examinee_test_mix = ExamineeTestMix::where('examinee_test_code',$examinee_account)->first();
            $test_mix = TestMix::find($examinee_test_mix->test_mix_id);
            $test_mix_content = json_decode($test_mix->content, TRUE);
            foreach($test_mix_content as $part_id => $questions){
                foreach($questions as $location => $question){
                    if($location == 13){
                        foreach($question as $question_id => $option_order){
                            $_question = Question::find($question_id);
                            $context_question = Question::find($_question->cloze_id);
                            $context_content = $context_question->content . '<br>' . $context_question->post_content;
                        }
                        break;
                    }
                }
            }

            // $examinee_assigment = ExaminerAssignment::where('examinee_test_code',$examinee_account)->first();
            $examinee_answers = AnswerKey::where('examinee_test_code',$examinee_account)->orderBy('matrix_location','asc')->get();
                    foreach($examinee_answers as $answer){
                        $question = Question::find($answer->question_id);
                        // $answer = ExamineeAnswer::where('question_id',$question_id)
                        //                         ->where('examinee_account',$examinee_account)
                        //                         ->first();
                        $detail = [
                            'examinee_test_code' => '',
                            'examinee_test_uuid' => '',
                            'examinee_answer_uuid' => '',
                            'subject_id' => '',
                            'question_id' => '',
                            'question_content' => '',
                            'question_type_id' => '',
                            'rubric_id' => '',
                            // 'context' => ($answer->matrix_location==21)?$context_content:'',
                            'number' => $answer->matrix_location,
                            'answer' => 'Chưa làm',
                            'type' => 0,
                            'submitting_time' => '',
                            'total_score' => 0,
                            'rubric' => [],
                            // 'question' => null
                        ];
                        if($answer){
                            // $count++;
                            $detail['examinee_answer_uuid'] = $answer->examinee_answer_uuid;
                            $detail['examinee_test_uuid'] = $answer->examinee_test_uuid;
                            $detail['examinee_test_code'] = $examinee_account;
                            $detail['submitting_time'] = date('d/m/Y H:i:s', $answer->submitting_time);
                            $detail['type'] = $answer->question_type_id;
                            switch($answer->question_type_id){
                                // case 1:
                                //     $detail['answer'] = $answer_label[array_search($answer->answer_detail, $option_order)];
                                //     break;
                                // case 2:
                                //     $tmp = explode(',',$answer->answer_detail);
                                //     $_tmp = array();
                                //     foreach($tmp as $item){
                                //         $_tmp[] = $answer_label[array_search($item, $option_order)];
                                //     }
                                //     $detail['answer'] = implode(',',$_tmp);
                                //     break;
                                // case 4:
                                //     // $detail['answer'] = mb_substr($answer->answer_detail, 0, 10, 'utf8');
                                //     $detail['answer'] = $answer->answer_detail;
                                //     break;
                                case 5:
                                    // if(mb_strlen($answer->answer_detail, 'utf8') < 50){
                                    //     $detail['answer'] = mb_substr($answer->examinee_answer, 0, 20, 'utf8') . ' ... ';
                                    // }else{
                                    //     $detail['answer'] = mb_substr($answer->examinee_answer, 0, 20, 'utf8') . ' ... ' . mb_substr($answer->answer_detail, mb_strlen($answer->answer_detail, 'utf8') - 20, null , 'utf8');
                                    // }
                                    $detail['answer'] = $answer->examinee_answer;
                                    $detail['subject_id'] = $answer->subject_id;
                                    $detail['question_id'] = $question?$question->id:0;
                                    $detail['question_content'] = $question?$question->content:'';
                                    $detail['question_type_id'] = $answer->question_type_id;
                                    $detail['rubric_id'] = $answer->rubric_id;
                                    // $rubric = Rubric::find($answer->rubric_id);
                                    $rubric_criterias = RubricCriteria::where('rubric_id',$answer->rubric_id)->get();
                                    $_total_score = 0;
                                    foreach($rubric_criterias as $rubric){
                                        $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$monitor->id)->where('examinee_test_code',$examinee_account)->orderBy('id','desc')->first();
                                        $_total_score += floatval($_detail?$_detail->score:0);
                                        $detail['rubric'][] = [
                                            'code' => $rubric->code,
                                            'name' => $rubric->name,
                                            'max_score' => $rubric->max_score,
                                            'scores' => explode(',',$rubric->scores),
                                            'score' => $_detail?$_detail->score:'',
                                        ];
                                    }
                                    $detail['total_score'] = $_total_score;
                                    $examinee_details[] = $detail;
                                    break;
                            }
                        }
                        // $examinee_details[] = $detail;
                    }
        //         }
        //     }
        }
        return $this->sendResponse([
            // 'count' => $count,
            // 'total' => $question_number,
            // 'code' => $examinee_test_mix->examinee_code,
            'account' => $examinee_account,
            // 'start_at' => date('d/m/Y H:i:s', $examinee_test_mix->start_time),
            // 'expected_finish_at' => date('d/m/Y H:i:s', $examinee_test_mix->expected_finish_time + $examinee_test_mix->bonus_time),
            // 'bonus_time' => ($examinee_test_mix->bonus_time / 60),
            // 'finish_at' => $examinee_test_mix->finish_time?date('d/m/Y H:i:s', $examinee_test_mix->finish_time):'',
            'details' => $examinee_details,
            'context' => $context_content
        ], 'Get examinee answer detail');
    }

    public function getExamineeDetail(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $examinee_details = array();
        $answer_label = [
            0 => 'A',
            1 => 'B',
            2 => 'C',
            3 => 'D',
            4 => 'E',
            5 => 'F',
            6 => 'G',
            7 => 'H',
        ];
        if($request->input('account')){
            $examinee_account = $request->input('account');
            $examinee_test_mix = ExamineeTestMix::where('examinee_account',$examinee_account)->first();
            // $examinee_answers = ExamineeAnswer::where('examinee_code',$examinee_code)->get();
            $test_mix = TestMix::find($examinee_test_mix->test_mix_id);
            $test_mix_content = json_decode($test_mix->content, TRUE);
            $question_number = 0;
            $count = 0;
            foreach($test_mix_content as $part_id => $questions){
                foreach($questions as $location => $question){
                    $question_number++;
                    foreach($question as $question_id => $option_order){
                        $question = Question::find($question_id);
                        $answer = ExamineeAnswer::where('question_id',$question_id)
                                                ->where('examinee_account',$examinee_account)
                                                ->first();

                        $detail = [
                            'number' => $question_number,
                            'answer' => 'Chưa làm',
                            'type' => 0,
                            'submitting_time' => ''
                        ];
                        if($answer){
                            $count++;
                            $detail['submitting_time'] = date('d/m/Y H:i:s', $answer->submitting_time);
                        $detail['type'] = $question->question_type_id;
                        switch($question->question_type_id){
                            case 1:
                                $detail['answer'] = $answer_label[array_search($answer->answer_detail, $option_order)];
                                break;
                            case 2:
                                $tmp = explode(',',$answer->answer_detail);
                                $_tmp = array();
                                foreach($tmp as $item){
                                    $_tmp[] = $answer_label[array_search($item, $option_order)];
                                }
                                $detail['answer'] = implode(',',$_tmp);
                                break;
                            case 4:
                                // $detail['answer'] = mb_substr($answer->answer_detail, 0, 10, 'utf8');
                                $detail['answer'] = $answer->answer_detail;
                                break;
                            case 5:
                                if(mb_strlen($answer->answer_detail, 'utf8') < 50){
                                    $detail['answer'] = mb_substr($answer->answer_detail, 0, 20, 'utf8') . ' ... ';
                                }else{
                                    $detail['answer'] = mb_substr($answer->answer_detail, 0, 20, 'utf8') . ' ... ' . mb_substr($answer->answer_detail, mb_strlen($answer->answer_detail, 'utf8') - 20, null , 'utf8');
                                }
                                break;
                            }
                        }
                        
                        $examinee_details[] = $detail;
                    }
                }
            }
        }
        return $this->sendResponse([
            'count' => $count,
            'total' => $question_number,
            'code' => $examinee_test_mix->examinee_code,
            'account' => $examinee_account,
            'start_at' => date('d/m/Y H:i:s', $examinee_test_mix->start_time),
            'expected_finish_at' => date('d/m/Y H:i:s', $examinee_test_mix->expected_finish_time + $examinee_test_mix->bonus_time),
            'bonus_time' => ($examinee_test_mix->bonus_time / 60),
            'finish_at' => $examinee_test_mix->finish_time?date('d/m/Y H:i:s', $examinee_test_mix->finish_time):'',
            'details' => $examinee_details,
        ], 'Get examinee answer detail');
    }

    public function getAllExaminees(Request $request){
        try {
            // Kiểm tra tính hợp lệ của token người dùng
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        $monitor = Monitor::where('user_id',$user->id)->first();
        // Mảng để chứa kết quả
        $examinee_arr = array();

        // Lấy tất cả examinees đã được gán cho giám thị
        $examinees = ExaminerAssignment::where('examiner_id',$monitor->id)->orderBy('is_done','asc')->get();

        // Duyệt qua tất cả examinees và lấy thông tin cần thiết
        foreach ($examinees as $examinee) {
            // Lấy thông tin tài khoản của examinee
            // $account = User::find($examinee->user_id);
            // if (!$account) continue;

            //kiem tra các bài đã chấm
            $total_count_rr = 0;
            $rubrics = Rubric::where('subject_id',$examinee->subject_id)->where('status',1)->get();
            foreach($rubrics as $rr){
                $total_count_rr += RubricCriteria::where('rubric_id',$rr->id)->where('status',1)->count();
            }
            $count = DB::table('examiner_rubric_details')->where('examinee_test_code',$examinee->examinee_test_code)->where('examiner_id',$monitor->id)->count(DB::raw('DISTINCT rubric_criteria_id'));
            // $count = ExaminerAssignment::select('rubric_criteria_id')->distinct()->where('examinee_test_code',$request->input('examinee_test_code'))->where('examiner_id',$monitor->id)->count();
            if(!$examinee->is_done && $count == $total_count_rr){
                // ExaminerAssignment::where('examinee_test_code',$request->input('examinee_test_code'))->where('examiner_id',$monitor->id)->update([
                //     'is_done' => true
                // ]);
                $examinee->is_done = 1;
                $examinee->save();
            }
            // Lưu thông tin vào mảng kết quả
            $examinee_arr[] = [
                'code' => $examinee->examinee_test_code,
                'is_done' => $examinee->is_done,
                'subject_id' => $examinee->subject_id
            ];
        }

        // Trả về kết quả
        return $this->sendResponse([
            'examinees' => $examinee_arr,
        ], 'Get all examinees');
    }

    public function postRubricScore(Request $request){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        $monitor = Monitor::where('user_id',$user->id)->first();
        if($request->input('code')){
            $rubric_criteria = RubricCriteria::where('code',$request->input('code'))->first();
            $examiner_rubric_detail = ExaminerRubricDetail::where('examiner_id',$monitor->id)
                                                        // ->where('examinee_test_uuid',$request->input('examinee_test_uuid'))
                                                        // ->where('examinee_answer_uuid',$request->input('examinee_answer_uuid'))
                                                        ->where('examinee_test_code',$request->input('examinee_test_code'))
                                                        ->where('subject_id',$request->input('subject_id'))
                                                        ->where('matrix_location',$request->input('matrix_location'))
                                                        ->where('rubric_criteria_id',$request->input('rubric_criteria_id'))
                                                        ->first();
            if(!$examiner_rubric_detail){
                $examiner_rubric_detail = ExaminerRubricDetail::create([
                    'examiner_id' => $monitor->id,
                    'examinee_test_uuid' => $request->input('examinee_test_uuid'),
                    'examinee_answer_uuid' => $request->input('examinee_answer_uuid'),
                    'examinee_test_code' => $request->input('examinee_test_code'),
                    'subject_id' => $request->input('subject_id'),
                    'question_id' => $request->input('question_id'),
                    'matrix_location' => $request->input('matrix_location'),
                    'question_type_id' => $request->input('question_type_id'),
                    'rubric_id' => $request->input('rubric_id'),
                    'rubric_criteria_id' => $rubric_criteria->id,
                    'score' => $request->input('score'),
                ]);
            }else{
                $examiner_rubric_detail->score = $request->input('score');
                $examiner_rubric_detail->save();
            }
            $total_count_rr = 0;
            $rubrics = Rubric::where('subject_id',$request->input('subject_id'))->where('status',1)->get();
            foreach($rubrics as $rr){
                $total_count_rr += RubricCriteria::where('rubric_id',$rr->id)->where('status',1)->count();
            }
            $count = DB::table('examiner_rubric_details')->where('examinee_test_code',$request->input('examinee_test_code'))->where('examiner_id',$monitor->id)->count(DB::raw('DISTINCT rubric_criteria_id'));
            // $count = ExaminerAssignment::select('rubric_criteria_id')->distinct()->where('examinee_test_code',$request->input('examinee_test_code'))->where('examiner_id',$monitor->id)->count();
            if($count == $total_count_rr){
                ExaminerAssignment::where('examinee_test_code',$request->input('examinee_test_code'))->where('examiner_id',$monitor->id)->update([
                    'is_done' => true
                ]);
            }
            
        }
        return $this->sendResponse([
            'examiner_rubric_detail' => $examiner_rubric_detail,
            'examinee_test_code' => $request->input('examinee_test_code'),
            'total_rr' => $total_count_rr,
            'total_score' => $count
        ], 'Done!');
    }
}
