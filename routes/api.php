<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\ExamController;

use App\Http\Middleware\JwtMiddleware;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::group([
//     'prefix' => 'auth'
// ], function () {
//     Route::post('/login', [AuthController::class, 'login']);
    // Route::post('/logout', [AuthController::class, 'logout'])->middleware([JwtMiddleware::class]);
    // Route::post('/refresh', [AuthController::class, 'refresh'])->middleware([JwtMiddleware::class]);
    // Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');
    // Route::get('/me', [AuthController::class, 'profile'])->middleware([JwtMiddleware::class]);
// });

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'profile'])->middleware([JwtMiddleware::class]);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware([JwtMiddleware::class]);
});

Route::prefix('examinee')->group(function () {
    Route::get('/', [TestController::class, 'getExaminee'])->middleware([JwtMiddleware::class]);
});

Route::prefix('test')->group(function () {
    Route::get('/', [TestController::class, 'getTestById'])->middleware([JwtMiddleware::class]);
    Route::get('/remaining-time', [TestController::class, 'getRemainingTime'])->middleware([JwtMiddleware::class]);
    Route::get('/answer-logs', [TestController::class, 'getAnswerLogs'])->middleware([JwtMiddleware::class]);
    Route::post('/answer', [TestController::class, 'postQuestionAnswer'])->middleware([JwtMiddleware::class]);
    Route::post('/save-answer-logs', [TestController::class, 'saveAnswerLogs'])->middleware([JwtMiddleware::class]);
    Route::post('/submit', [TestController::class, 'submitTest'])->middleware([JwtMiddleware::class]);
});

Route::prefix('exam')->group(function () {
    Route::get('/check-room-status', [ExamController::class, 'checkRoomStatus'])->middleware([JwtMiddleware::class]);
    Route::put('/active-room', [ExamController::class, 'activeRoom'])->middleware([JwtMiddleware::class]);
});

Route::prefix('monitor')->group(function () {
    Route::get('/', [ExamController::class, 'getMonitor'])->middleware([JwtMiddleware::class]);
    Route::get('/councils', [ExamController::class, 'getAvailableCouncilToday'])->middleware([JwtMiddleware::class]);
    Route::get('/council-turn', [ExamController::class, 'getCouncilTurnByMonitor'])->middleware([JwtMiddleware::class]);
    Route::get('/council-turn-room', [ExamController::class, 'getRoomByMonitor'])->middleware([JwtMiddleware::class]);
    Route::get('/examinees', [ExamController::class, 'getExamineeListByMonitorRoom'])->middleware([JwtMiddleware::class]);
    // Route::get('/chamthi/get-examinee-detail', [ExamController::class, '_getExamineeDetail'])->middleware([JwtMiddleware::class]); //cham thi
    Route::get('/get-examinee-detail', [ExamController::class, 'getExamineeDetail'])->middleware([JwtMiddleware::class]); //to chuc thi
    Route::post('/add-time', [ExamController::class, 'addBonusTime'])->middleware([JwtMiddleware::class]);
    Route::post('/restore-examinee', [ExamController::class, 'restoreExaminee'])->middleware([JwtMiddleware::class]);
    Route::get('/all-examinees', [ExamController::class, 'getAllExaminees'])->middleware([JwtMiddleware::class]);
    Route::post('/submit-test-by-monitor', [ExamController::class, 'submitTest'])->middleware([JwtMiddleware::class]);

    Route::post('/post-rubric-score', [ExamController::class, 'postRubricScore'])->middleware([JwtMiddleware::class]);
});

Route::prefix('chamthi')->group(function () {
    Route::get('/get-examinee-detail', [ExamController::class, '_getExamineeDetail'])->middleware([JwtMiddleware::class]); //cham thi
    Route::post('/post-rubric-score', [ExamController::class, 'postRubricScore'])->middleware([JwtMiddleware::class]);
});

Route::get('/ping', function () {
    return response()->json([], 200);
});
