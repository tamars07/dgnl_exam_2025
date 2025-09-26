<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Examinee;
use App\Models\ExamineeTestMix;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class AuthController extends BaseController
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Tài khoản hoặc mật khẩu không chính xác'], 401);
            }

            // Get the authenticated user.
            $is_staff = false;
            $user = Auth::user();
            $roles = [];
            $_roles = Auth::user()->roles;
            $role_id = 8;
            $role_name = '';
            foreach($_roles as $role){
                $role_id = $role->id;
                $role_name = $role->name;
                if($role->name == 'ADMIN' 
                || $role->name == 'MODERATOR' 
                || $role->name == 'EDITOR' 
                || $role->name == 'MONITOR'
                || $role->name == 'EXAMINER' 
                || $role->name == 'REVIEWER' 
                || $role->name == 'CHAIRMAN'
                ) {
                    $is_staff = true;
                }
                $roles[] = $role->name;
            }
            //get client ip address
            $ip_address = $request->ip();
            if(!$is_staff){
                //check ip address
                if($user->ip_address && $user->ip_address != $ip_address){
                    return response()->json(['error' => 'Thí sinh đang đăng nhập ở máy tính khác!'], 401);
                }
                //check examinee has submitted or not
                $examinee = Examinee::where('user_id',$user->id)->first();
                if(!$examinee){
                    return response()->json(['error' => 'Không tìm thấy thí sinh!'], 404);
                }
                $examinee_test = ExamineeTestMix::where('examinee_account',$user->email)->first();
                if($examinee_test && $examinee_test->finish_time){
                    return response()->json(['error' => 'Thí sinh đã nộp bài!'], 400);
                }
                  
                //save activity log
                $log_time = time();
                $_council_code = $examinee->council_code;
                $_council_turn_code = $examinee->council_turn_code;
                $_room_code = $examinee->room_code;
                $log_uuid = (string) Uuid::uuid4();
                ActivityLog::create([
                    'log_uuid' => $log_uuid,
                    'username' => $user->email,
                    'council_code' => $_council_code,
                    'council_turn_code' => $_council_turn_code,
                    'room_code' => $_room_code,
                    'role_id' => $role_id,
                    'action' => $role_name . '_LOGIN',
                    'desc' => $ip_address,
                    'log_time' => $log_time,
                ]);
            }else{
                $log_time = time();
                $log_uuid = (string) Uuid::uuid4();
                ActivityLog::create([
                    'log_uuid' => $log_uuid,
                    'username' => $user->email,
                    'council_code' => '',
                    'council_turn_code' => '',
                    'room_code' => '',
                    'role_id' => $role_id,
                    'action' => $role_name . '_LOGIN',
                    'desc' => $ip_address,
                    'log_time' => $log_time,
                ]);
            }
            
            $user->ip_address = $ip_address;
            $user->save();

            // (optional) Attach the role to the token.
            $token = JWTAuth::claims(['role' => $role])->fromUser($user);

            // return response()->json(compact('token'));
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
  
        $success = [
            "serviceToken" => $token,
            "user" => [
                "id" => $user->id,
                "email" => $user->email,
                "name" => $user->name,
                "roles" => $roles
            ],
            // "roles" => $roles
        ];
   
        return $this->sendResponse($success, 'User login successfully.');
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        $roles = [];
        $_roles = Auth::user()->roles;
        foreach($_roles as $role){
            $roles[] = $role->name;
        }
        // $user = Auth::user();
        $success = [
            "id" => $user->id,
            "email" => $user->email,
            "name" => $user->name,
            "roles" => $roles
        ];
        // return response()->json(compact('user'));
   
        return $this->sendResponse($success, 'Refresh token return successfully.');
    }
  
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
  
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $success = $this->respondWithToken(Auth::refresh());
   
        return $this->sendResponse($success, 'Refresh token return successfully.');
    }
  
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL()
        ];
    }
}
