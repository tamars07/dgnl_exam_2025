<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\CouncilController;
use App\Http\Controllers\EvaluationController;

use Illuminate\Support\Facades\Route;

// middleware
use App\Http\Middleware\AuthenticateMiddleware;
use App\Http\Middleware\EnsureUserHasRoleMiddleware;

//service
use App\Services\PhpWordService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//authentication
Route::get('/', [AuthController::class, 'index'])->name('login');
Route::get('login', [AuthController::class, 'index'])->name('login');
Route::post('post-login', [AuthController::class, 'postLogin'])->name('login.post'); 
// Route::get('registration', [AuthController::class, 'registration'])->name('register');
// Route::post('post-registration', [AuthController::class, 'postRegistration'])->name('register.post'); 
// Route::get('dashboard', [ManagementController::class, 'index']); 
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

// Make sure that we have get and post methods for Grocery CRUD to work as expected
Route::prefix('management')
    ->middleware([AuthenticateMiddleware::class, EnsureUserHasRoleMiddleware::class.':moderator'])
    ->group(function () {
    Route::get('/', [ManagementController::class, 'index'])->name('dashboard');
    // difficult
    Route::get('/difficult', [ManagementController::class, 'difficult']);
    Route::get('/difficult/{operation}', [ManagementController::class, 'difficult']);
    Route::get('/difficult/{operation}/{id}', [ManagementController::class, 'difficult']);
    Route::post('/difficult', [ManagementController::class, 'difficult']);
    Route::post('/difficult/{operation}', [ManagementController::class, 'difficult']);
    Route::post('/difficult/{operation}/{id}', [ManagementController::class, 'difficult']);

    // question type
    Route::get('/qtype', [ManagementController::class, 'questionType']);
    Route::get('/qtype/{operation}', [ManagementController::class, 'questionType']);
    Route::get('/qtype/{operation}/{id}', [ManagementController::class, 'questionType']);
    Route::post('/qtype', [ManagementController::class, 'questionType']);
    Route::post('/qtype/{operation}', [ManagementController::class, 'questionType']);
    Route::post('/qtype/{operation}/{id}', [ManagementController::class, 'questionType']);

    // subject
    Route::get('/subject', [ManagementController::class, 'subject']);
    Route::get('/subject/{operation}', [ManagementController::class, 'subject']);
    Route::get('/subject/{operation}/{id}', [ManagementController::class, 'subject']);
    Route::post('/subject', [ManagementController::class, 'subject']);
    Route::post('/subject/{operation}', [ManagementController::class, 'subject']);
    Route::post('/subject/{operation}/{id}', [ManagementController::class, 'subject']);

    // question mark
    Route::get('/question-mark', [ManagementController::class, 'questionMark']);
    Route::get('/question-mark/{operation}', [ManagementController::class, 'questionMark']);
    Route::get('/question-mark/{operation}/{id}', [ManagementController::class, 'questionMark']);
    Route::post('/question-mark', [ManagementController::class, 'questionMark']);
    Route::post('/question-mark/{operation}', [ManagementController::class, 'questionMark']);
    Route::post('/question-mark/{operation}/{id}', [ManagementController::class, 'questionMark']);

    // question store
    Route::get('/question-store', [ManagementController::class, 'questionStore']);
    Route::get('/question-store/{operation}', [ManagementController::class, 'questionStore']);
    Route::get('/question-store/{operation}/{id}', [ManagementController::class, 'questionStore']);
    Route::post('/question-store', [ManagementController::class, 'questionStore']);
    Route::post('/question-store/{operation}', [ManagementController::class, 'questionStore']);
    Route::post('/question-store/{operation}/{id}', [ManagementController::class, 'questionStore']);

    // grade
    Route::get('/grade', [ManagementController::class, 'grade']);
    Route::get('/grade/{operation}', [ManagementController::class, 'grade']);
    Route::get('/grade/{operation}/{id}', [ManagementController::class, 'grade']);
    Route::post('/grade', [ManagementController::class, 'grade']);
    Route::post('/grade/{operation}', [ManagementController::class, 'grade']);
    Route::post('/grade/{operation}/{id}', [ManagementController::class, 'grade']);

    // competency
    Route::get('/competency', [ManagementController::class, 'competency']);
    Route::get('/competency/{operation}', [ManagementController::class, 'competency']);
    Route::get('/competency/{operation}/{id}', [ManagementController::class, 'competency']);
    Route::post('/competency', [ManagementController::class, 'competency']);
    Route::post('/competency/{operation}', [ManagementController::class, 'competency']);
    Route::post('/competency/{operation}/{id}', [ManagementController::class, 'competency']);

    // taxonomy
    Route::get('/taxonomy', [ManagementController::class, 'taxonomy']);
    Route::get('/taxonomy/{operation}', [ManagementController::class, 'taxonomy']);
    Route::get('/taxonomy/{operation}/{id}', [ManagementController::class, 'taxonomy']);
    Route::post('/taxonomy', [ManagementController::class, 'taxonomy']);
    Route::post('/taxonomy/{operation}', [ManagementController::class, 'taxonomy']);
    Route::post('/taxonomy/{operation}/{id}', [ManagementController::class, 'taxonomy']);

    // topic
    Route::get('/topic', [ManagementController::class, 'topic']);
    Route::get('/topic/{operation}', [ManagementController::class, 'topic']);
    Route::get('/topic/{operation}/{id}', [ManagementController::class, 'topic']);
    Route::post('/topic', [ManagementController::class, 'topic']);
    Route::post('/topic/{operation}', [ManagementController::class, 'topic']);
    Route::post('/topic/{operation}/{id}', [ManagementController::class, 'topic']);

    // organization
    Route::get('/organization', [ManagementController::class, 'organization']);
    Route::get('/organization/{operation}', [ManagementController::class, 'organization']);
    Route::get('/organization/{operation}/{id}', [ManagementController::class, 'organization']);
    Route::post('/organization', [ManagementController::class, 'organization']);
    Route::post('/organization/{operation}', [ManagementController::class, 'organization']);
    Route::post('/organization/{operation}/{id}', [ManagementController::class, 'organization']);

    // room
    Route::get('/room', [ManagementController::class, 'room']);
    Route::get('/room/{operation}', [ManagementController::class, 'room']);
    Route::get('/room/{operation}/{id}', [ManagementController::class, 'room']);
    Route::post('/room', [ManagementController::class, 'room']);
    Route::post('/room/{operation}', [ManagementController::class, 'room']);
    Route::post('/room/{operation}/{id}', [ManagementController::class, 'room']);
});

Route::prefix('qbank')
    ->middleware([AuthenticateMiddleware::class, EnsureUserHasRoleMiddleware::class.':editor'])
    ->group(function () {
    Route::get('/', [ManagementController::class, 'index'])->name('dashboard');
    // questions TN1
    Route::get('/question/TN1', [QuestionBankController::class, 'questionTN1']);
    Route::get('/question/TN1/{operation}', [QuestionBankController::class, 'questionTN1']);
    Route::get('/question/TN1/{operation}/{id}', [QuestionBankController::class, 'questionTN1']);
    Route::post('/question/TN1', [QuestionBankController::class, 'questionTN1']);
    Route::post('/question/TN1/{operation}', [QuestionBankController::class, 'questionTN1']);
    Route::post('/question/TN1/{operation}/{id}', [QuestionBankController::class, 'questionTN1']);

    // questions TNN
    Route::get('/question/TNN', [QuestionBankController::class, 'questionTNN']);
    Route::get('/question/TNN/{operation}', [QuestionBankController::class, 'questionTNN']);
    Route::get('/question/TNN/{operation}/{id}', [QuestionBankController::class, 'questionTNN']);
    Route::post('/question/TNN', [QuestionBankController::class, 'questionTNN']);
    Route::post('/question/TNN/{operation}', [QuestionBankController::class, 'questionTNN']);
    Route::post('/question/TNN/{operation}/{id}', [QuestionBankController::class, 'questionTNN']);

    // questions TNC
    Route::get('/question/TNC', [QuestionBankController::class, 'questionTNC']);
    Route::get('/question/TNC/{operation}', [QuestionBankController::class, 'questionTNC']);
    Route::get('/question/TNC/{operation}/{id}', [QuestionBankController::class, 'questionTNC']);
    Route::post('/question/TNC', [QuestionBankController::class, 'questionTNC']);
    Route::post('/question/TNC/{operation}', [QuestionBankController::class, 'questionTNC']);
    Route::post('/question/TNC/{operation}/{id}', [QuestionBankController::class, 'questionTNC']);

    Route::get('/question/TNC-detail/{cloze_id?}', [QuestionBankController::class, 'questionTNCDetails']);
    Route::get('/question/TNC-detail/{cloze_id?}/{operation}', [QuestionBankController::class, 'questionTNCDetails']);
    Route::get('/question/TNC-detail/{cloze_id?}/{operation}/{id}', [QuestionBankController::class, 'questionTNCDetails']);
    Route::post('/question/TNC-detail/{cloze_id?}', [QuestionBankController::class, 'questionTNCDetails']);
    Route::post('/question/TNC-detail/{cloze_id?}/{operation}', [QuestionBankController::class, 'questionTNCDetails']);
    Route::post('/question/TNC-detail/{cloze_id?}/{operation}/{id}', [QuestionBankController::class, 'questionTNCDetails']);

    // questions TLN
    Route::get('/question/TLN', [QuestionBankController::class, 'questionTLN']);
    Route::get('/question/TLN/{operation}', [QuestionBankController::class, 'questionTLN']);
    Route::get('/question/TLN/{operation}/{id}', [QuestionBankController::class, 'questionTLN']);
    Route::post('/question/TLN', [QuestionBankController::class, 'questionTLN']);
    Route::post('/question/TLN/{operation}', [QuestionBankController::class, 'questionTLN']);
    Route::post('/question/TLN/{operation}/{id}', [QuestionBankController::class, 'questionTLN']);

    // questions LNN
    Route::get('/question/LNN', [QuestionBankController::class, 'questionLNN']);
    Route::get('/question/LNN/{operation}', [QuestionBankController::class, 'questionLNN']);
    Route::get('/question/LNN/{operation}/{id}', [QuestionBankController::class, 'questionLNN']);
    Route::post('/question/LNN', [QuestionBankController::class, 'questionLNN']);
    Route::post('/question/LNN/{operation}', [QuestionBankController::class, 'questionLNN']);
    Route::post('/question/LNN/{operation}/{id}', [QuestionBankController::class, 'questionLNN']);

    //question preview
    Route::get('/preview/{question_id?}', [QuestionBankController::class, 'questionPreview']);
});

Route::prefix('test')
    ->middleware([AuthenticateMiddleware::class, EnsureUserHasRoleMiddleware::class.':moderator'])
    ->group(function () {
    Route::get('/', [ManagementController::class, 'index'])->name('dashboard');
    // test part
    Route::get('/part', [TestController::class, 'testPart']);
    Route::get('/part/{operation}', [TestController::class, 'testPart']);
    Route::get('/part/{operation}/{id}', [TestController::class, 'testPart']);
    Route::post('/part', [TestController::class, 'testPart']);
    Route::post('/part/{operation}', [TestController::class, 'testPart']);
    Route::post('/part/{operation}/{id}', [TestController::class, 'testPart']);

    // test form
    Route::get('/form', [TestController::class, 'testForm']);
    Route::get('/form/{operation}', [TestController::class, 'testForm']);
    Route::get('/form/{operation}/{id}', [TestController::class, 'testForm']);
    Route::post('/form', [TestController::class, 'testForm']);
    Route::post('/form/{operation}', [TestController::class, 'testForm']);
    Route::post('/form/{operation}/{id}', [TestController::class, 'testForm']);

    // test form part
    Route::get('/formpart/{form_id?}', [TestController::class, 'testFormPart']);
    Route::get('/formpart/{form_id?}/{operation}', [TestController::class, 'testFormPart']);
    Route::get('/formpart/{form_id?}/{operation}/{id}', [TestController::class, 'testFormPart']);
    Route::post('/formpart/{form_id?}', [TestController::class, 'testFormPart']);
    Route::post('/formpart/{form_id?}/{operation}', [TestController::class, 'testFormPart']);
    Route::post('/formpart/{form_id?}/{operation}/{id}', [TestController::class, 'testFormPart']);

    // test group
    Route::get('/tgroup', [TestController::class, 'testGroup']);
    Route::get('/tgroup/{operation}', [TestController::class, 'testGroup']);
    Route::get('/tgroup/{operation}/{id}', [TestController::class, 'testGroup']);
    Route::post('/tgroup', [TestController::class, 'testGroup']);
    Route::post('/tgroup/{operation}', [TestController::class, 'testGroup']);
    Route::post('/tgroup/{operation}/{id}', [TestController::class, 'testGroup']);

    // test group detail
    Route::get('/tgroup-detail/{group_id?}', [TestController::class, 'testGroupDetail']);
    Route::get('/tgroup-detail/{group_id?}/{operation}', [TestController::class, 'testGroupDetail']);
    Route::get('/tgroup-detail/{group_id?}/{operation}/{id}', [TestController::class, 'testGroupDetail']);
    Route::post('/tgroup-detail/{group_id?}', [TestController::class, 'testGroupDetail']);
    Route::post('/tgroup-detail/{group_id?}/{operation}', [TestController::class, 'testGroupDetail']);
    Route::post('/tgroup-detail/{group_id?}/{operation}/{id}', [TestController::class, 'testGroupDetail']);

    // test root
    Route::get('/troot/{test_id?}', [TestController::class, 'testRoot']);
    Route::get('/troot/{test_id?}/{operation}', [TestController::class, 'testRoot']);
    Route::get('/troot/{test_id?}/{operation}/{id}', [TestController::class, 'testRoot']);
    Route::post('/troot/{test_id?}', [TestController::class, 'testRoot']);
    Route::post('/troot/{test_id?}/{operation}', [TestController::class, 'testRoot']);
    Route::post('/troot/{test_id?}/{operation}/{id}', [TestController::class, 'testRoot']);

    // test mix
    Route::get('/tmix/{test_root_id?}', [TestController::class, 'testMix']);
    Route::get('/tmix/{test_root_id?}/{operation}', [TestController::class, 'testMix']);
    Route::get('/tmix/{test_root_id?}/{operation}/{id}', [TestController::class, 'testMix']);
    Route::post('/tmix/{test_root_id?}', [TestController::class, 'testMix']);
    Route::post('/tmix/{test_root_id?}/{operation}', [TestController::class, 'testMix']);
    Route::post('/tmix/{test_root_id?}/{operation}/{id}', [TestController::class, 'testMix']);

    // create test root
    Route::get('/create-test-root/{test_id?}', [TestController::class, 'createTestRoot']);

    // create test preview
    Route::get('/test-root-preview/{test_id?}', [TestController::class, 'testRootPreview']);
    Route::get('/test-mix-preview/{test_id?}', [TestController::class, 'testMixPreview']);

    //download test group
    Route::get('/download/{id}', [TestController::class, 'downloadTestGroup']);
    // import test data to council turn
    Route::post('/import-testdata/{code?}', [TestController::class, 'importTestGroup']);
});

Route::prefix('exam')
    ->middleware([AuthenticateMiddleware::class, EnsureUserHasRoleMiddleware::class.':moderator'])
    ->group(function () {
    Route::get('/', [CouncilController::class, 'index'])->name('dashboard');
    // organization
    Route::get('/organization', [CouncilController::class, 'organization']);
    Route::get('/organization/{operation}', [CouncilController::class, 'organization']);
    Route::get('/organization/{operation}/{id}', [CouncilController::class, 'organization']);
    Route::post('/organization', [CouncilController::class, 'organization']);
    Route::post('/organization/{operation}', [CouncilController::class, 'organization']);
    Route::post('/organization/{operation}/{id}', [CouncilController::class, 'organization']);

    // room
    Route::get('/room', [CouncilController::class, 'room']);
    Route::get('/room/{operation}', [CouncilController::class, 'room']);
    Route::get('/room/{operation}/{id}', [CouncilController::class, 'room']);
    Route::post('/room', [CouncilController::class, 'room']);
    Route::post('/room/{operation}', [CouncilController::class, 'room']);
    Route::post('/room/{operation}/{id}', [CouncilController::class, 'room']);

    // monitor
    Route::get('/monitor', [CouncilController::class, 'monitor']);
    Route::get('/monitor/{operation}', [CouncilController::class, 'monitor']);
    Route::get('/monitor/{operation}/{id}', [CouncilController::class, 'monitor']);
    Route::post('/monitor', [CouncilController::class, 'monitor']);
    Route::post('/monitor/{operation}', [CouncilController::class, 'monitor']);
    Route::post('/monitor/{operation}/{id}', [CouncilController::class, 'monitor']);

    // council
    Route::get('/council', [CouncilController::class, 'council']);
    Route::get('/council/{operation}', [CouncilController::class, 'council']);
    Route::get('/council/{operation}/{id}', [CouncilController::class, 'council']);
    Route::post('/council', [CouncilController::class, 'council']);
    Route::post('/council/{operation}', [CouncilController::class, 'council']);
    Route::post('/council/{operation}/{id}', [CouncilController::class, 'council']);

    // council turns
    Route::get('/council-turns/{council_code?}', [CouncilController::class, 'councilTurn']);
    Route::get('/council-turns/{council_code?}/{operation}', [CouncilController::class, 'councilTurn']);
    Route::get('/council-turns/{council_code?}/{operation}/{id}', [CouncilController::class, 'councilTurn']);
    Route::post('/council-turns/{council_code?}', [CouncilController::class, 'councilTurn']);
    Route::post('/council-turns/{council_code?}/{operation}', [CouncilController::class, 'councilTurn']);
    Route::post('/council-turns/{council_code?}/{operation}/{id}', [CouncilController::class, 'councilTurn']);

    // council turn rooms
    Route::get('/council-turn-rooms/{code?}', [CouncilController::class, 'councilTurnRoom']);
    Route::post('/council-turn-rooms/{code?}', [CouncilController::class, 'councilTurnRoom']);

    // council turn test mixes
    Route::get('/council-turn-test-mixes/{code?}', [CouncilController::class, 'councilTurnTestMixes']);
    Route::post('/council-turn-test-mixes/{code?}', [CouncilController::class, 'councilTurnTestMixes']);
    Route::get('/active-rooms', [CouncilController::class, 'activeRooms']);
    Route::get('/deactive-rooms', [CouncilController::class, 'deactiveRooms']);

    // examinee
    Route::get('/examinee', [CouncilController::class, 'examinee']);
    Route::get('/examinee/{operation}', [CouncilController::class, 'examinee']);
    Route::get('/examinee/{operation}/{id}', [CouncilController::class, 'examinee']);
    Route::post('/examinee', [CouncilController::class, 'examinee']);
    Route::post('/examinee/{operation}', [CouncilController::class, 'examinee']);
    Route::post('/examinee/{operation}/{id}', [CouncilController::class, 'examinee']);

    // examinee
    Route::get('/examinee-import', [CouncilController::class, 'examineeImport']);
    Route::get('/examinee-import/{operation}', [CouncilController::class, 'examineeImport']);
    Route::get('/examinee-import/{operation}/{id}', [CouncilController::class, 'examineeImport']);
    Route::post('/examinee-import', [CouncilController::class, 'examineeImport']);
    Route::post('/examinee-import/{operation}', [CouncilController::class, 'examineeImport']);
    Route::post('/examinee-import/{operation}/{id}', [CouncilController::class, 'examineeImport']);

    //import examinee list
    Route::post('/import-examinee', [CouncilController::class, 'importExaminee']);
    //import room list
    Route::post('/import-room', [CouncilController::class, 'importRoom']);
    //export council
    Route::get('/export-council/{id}', [CouncilController::class, 'exportCouncil']);
    //import council
    Route::post('/import-council', [CouncilController::class, 'importCouncil']);
    //export council turn
    Route::get('/export-council-turn/{code}', [CouncilController::class, 'exportCouncilTurn']);
});

Route::get('users', [UserController::class, 'index']);
Route::get('users-export', [UserController::class, 'export'])->name('users.export');
Route::post('users-import', [UserController::class, 'import'])->name('users.import');

Route::prefix('evaluation')
    ->middleware([AuthenticateMiddleware::class, EnsureUserHasRoleMiddleware::class.':moderator'])
    ->group(function () {
    Route::get('/', [EvaluationController::class, 'index'])->name('dashboard');

    // rubric
    Route::get('/rubric', [EvaluationController::class, 'rubric']);
    Route::get('/rubric/{operation}', [EvaluationController::class, 'rubric']);
    Route::get('/rubric/{operation}/{id}', [EvaluationController::class, 'rubric']);
    Route::post('/rubric', [EvaluationController::class, 'rubric']);
    Route::post('/rubric/{operation}', [EvaluationController::class, 'rubric']);
    Route::post('/rubric/{operation}/{id}', [EvaluationController::class, 'rubric']);

    // rubric criteria
    Route::get('/rubric-criteria/{rubric_id?}', [EvaluationController::class, 'rubricCriteria']);
    Route::get('/rubric-criteria/{rubric_id?}/{operation}', [EvaluationController::class, 'rubricCriteria']);
    Route::get('/rubric-criteria/{rubric_id?}/{operation}/{id}', [EvaluationController::class, 'rubricCriteria']);
    Route::post('/rubric-criteria/{rubric_id?}', [EvaluationController::class, 'rubricCriteria']);
    Route::post('/rubric-criteria/{rubric_id?}/{operation}', [EvaluationController::class, 'rubricCriteria']);
    Route::post('/rubri-criteria/{rubric_id?}/{operation}/{id}', [EvaluationController::class, 'rubricCriteria']);

    // council-exam data
    Route::get('/council-exam-data', [EvaluationController::class, 'councilExamData']);
    Route::get('/council-exam-data/{operation}', [EvaluationController::class, 'councilExamData']);
    Route::get('/council-exam-data/{operation}', [EvaluationController::class, 'councilExamData']);
    Route::post('/council-exam-data', [EvaluationController::class, 'councilExamData']);
    Route::post('/council-exam-data/{operation}', [EvaluationController::class, 'councilExamData']);
    Route::post('/council-exam-data/{operation}', [EvaluationController::class, 'councilExamData']);
    // sync-exam data
    Route::get('/sync-exam-data/{code?}', [EvaluationController::class, 'syncExamData']);
    Route::get('/sync-exam-data/{code?}/{operation}', [EvaluationController::class, 'syncExamData']);
    Route::get('/sync-exam-data/{code?}/{operation}', [EvaluationController::class, 'syncExamData']);
    Route::post('/sync-exam-data/{code?}', [EvaluationController::class, 'syncExamData']);
    Route::post('/sync-exam-data/{code?}/{operation}', [EvaluationController::class, 'syncExamData']);
    Route::post('/sync-exam-data/{code?}/{operation}', [EvaluationController::class, 'syncExamData']);

    //import exam data
    Route::post('/import-exam-data', [EvaluationController::class, 'importExamData']);

    // examinee-answers
    Route::get('/examinee-answers', [EvaluationController::class, 'examineeAnswer']);
    Route::get('/examinee-answers/{operation}', [EvaluationController::class, 'examineeAnswer']);
    Route::get('/examinee-answers/{operation}', [EvaluationController::class, 'examineeAnswer']);
    Route::post('/examinee-answers', [EvaluationController::class, 'examineeAnswer']);
    Route::post('/examinee-answers/{operation}', [EvaluationController::class, 'examineeAnswer']);
    Route::post('/examinee-answers/{operation}', [EvaluationController::class, 'examineeAnswer']);
    
    // import-answer-key
    Route::get('/manage-answer-key', [EvaluationController::class, 'manageAnswerKey']);
    Route::get('/manage-answer-key/{operation}', [EvaluationController::class, 'manageAnswerKey']);
    Route::get('/manage-answer-key/{operation}', [EvaluationController::class, 'manageAnswerKey']);
    Route::post('/manage-answer-key', [EvaluationController::class, 'manageAnswerKey']);
    Route::post('/manage-answer-key/{operation}', [EvaluationController::class, 'manageAnswerKey']);
    Route::post('/manage-answer-key/{operation}', [EvaluationController::class, 'manageAnswerKey']);

    //import answer-key
    Route::post('/import-answer-key', [EvaluationController::class, 'importAnswerKey']);

    // summary-sync-data
    Route::get('/summary-sync-data', [EvaluationController::class, 'summarySyncData']);
    Route::get('/summary-sync-data/{operation}', [EvaluationController::class, 'summarySyncData']);
    Route::get('/summary-sync-datar/{operation}', [EvaluationController::class, 'summarySyncData']);
    Route::post('/summary-sync-data', [EvaluationController::class, 'summarySyncData']);
    Route::post('/summary-sync-data/{operation}', [EvaluationController::class, 'summarySyncData']);
    Route::post('/summary-sync-data/{operation}', [EvaluationController::class, 'summarySyncData']);

    // summary-examiner-pair
    Route::get('/summary-examiner-pair', [EvaluationController::class, 'summaryExaminerPair']);
    Route::get('/summary-examiner-pair/{operation}', [EvaluationController::class, 'summaryExaminerPair']);
    Route::get('/summary-examiner-pair/{operation}', [EvaluationController::class, 'summaryExaminerPair']);
    Route::post('/summary-examiner-pair', [EvaluationController::class, 'summaryExaminerPair']);
    Route::post('/summary-examiner-pair/{operation}', [EvaluationController::class, 'summaryExaminerPair']);
    Route::post('/summary-examiner-pair/{operation}', [EvaluationController::class, 'summaryExaminerPair']);

    // assign-examiner
    Route::get('/assign-examiner', [EvaluationController::class, 'assignExaminer']);
    Route::get('/assign-examiner/{operation}', [EvaluationController::class, 'assignExaminer']);
    Route::get('/assign-examiner/{operation}', [EvaluationController::class, 'assignExaminer']);
    Route::post('/assign-examiner', [EvaluationController::class, 'assignExaminer']);
    Route::post('/assign-examiner/{operation}', [EvaluationController::class, 'assignExaminer']);
    Route::post('/assign-examiner/{operation}', [EvaluationController::class, 'assignExaminer']);

    // detail-assign-examiner
    Route::get('/detail-assign-examiner/{code?}', [EvaluationController::class, 'detailAssignExaminer']);
    Route::get('/detail-assign-examiner/{code?}/{operation}', [EvaluationController::class, 'detailAssignExaminer']);
    Route::get('/detail-assign-examiner/{code?}/{operation}', [EvaluationController::class, 'detailAssignExaminer']);
    Route::post('/detail-assign-examiner/{code?}', [EvaluationController::class, 'detailAssignExaminer']);
    Route::post('/detail-assign-examiner/{code?}/{operation}', [EvaluationController::class, 'detailAssignExaminer']);
    Route::post('/detail-assign-examiner/{code?}/{operation}', [EvaluationController::class, 'detailAssignExaminer']);

    // view-detail-assign-examiner
    Route::get('/view-detail-assign-examiner/{pairId?}/{examinerId?}', [EvaluationController::class, 'viewDetailAssignExaminer']);
    Route::get('/view-detail-assign-examiner/{pairId?}/{examinerId?}/{code?}/{operation}', [EvaluationController::class, 'viewDetailAssignExaminer']);
    Route::get('/view-detail-assign-examiner/{pairId?}/{examinerId?}/{code?}/{operation}', [EvaluationController::class, 'viewDetailAssignExaminer']);
    Route::post('/view-detail-assign-examiner/{pairId?}/{examinerId?}/{code?}', [EvaluationController::class, 'viewDetailAssignExaminer']);
    Route::post('/view-detail-assign-examiner/{pairId?}/{examinerId?}/{code?}/{operation}', [EvaluationController::class, 'viewDetailAssignExaminer']);
    Route::post('/view-detail-assign-examiner/{pairId?}/{examinerId?}/{code?}/{operation}', [EvaluationController::class, 'viewDetailAssignExaminer']);

    // assign-reviewer
    Route::get('/assign-reviewer', [EvaluationController::class, 'assignReviewer']);
    Route::get('/assign-reviewer/{operation}', [EvaluationController::class, 'assignReviewer']);
    Route::get('/assign-reviewer/{operation}', [EvaluationController::class, 'assignReviewer']);
    Route::post('/assign-reviewer', [EvaluationController::class, 'assignReviewer']);
    Route::post('/assign-reviewer/{operation}', [EvaluationController::class, 'assignReviewer']);
    Route::post('/assign-reviewer/{operation}', [EvaluationController::class, 'assignReviewer']);

    // detail-assign-examiner
    Route::get('/detail-assign-reviewer/{code?}', [EvaluationController::class, 'detailAssignReviewer']);
    Route::get('/detail-assign-reviewer/{code?}/{operation}', [EvaluationController::class, 'detailAssignReviewer']);
    Route::get('/detail-assign-reviewer/{code?}/{operation}', [EvaluationController::class, 'detailAssignReviewer']);
    Route::post('/detail-assign-reviewer/{code?}', [EvaluationController::class, 'detailAssignReviewer']);
    Route::post('/detail-assign-reviewer/{code?}/{operation}', [EvaluationController::class, 'detailAssignReviewer']);
    Route::post('/detail-assign-reviewer/{code?}/{operation}', [EvaluationController::class, 'detailAssignReviewer']);

    // view-detail-assign-examiner
    Route::get('/view-detail-assign-reviewer/{pairId?}/{examinerId?}', [EvaluationController::class, 'viewDetailAssignReviewer']);
    Route::get('/view-detail-assign-reviewer/{pairId?}/{examinerId?}/{code?}/{operation}', [EvaluationController::class, 'viewDetailAssignReviewer']);
    Route::get('/view-detail-assign-reviewer/{pairId?}/{examinerId?}/{code?}/{operation}', [EvaluationController::class, 'viewDetailAssignReviewer']);
    Route::post('/view-detail-assign-reviewer/{pairId?}/{examinerId?}/{code?}', [EvaluationController::class, 'viewDetailAssignReviewer']);
    Route::post('/view-detail-assign-reviewer/{pairId?}/{examinerId?}/{code?}/{operation}', [EvaluationController::class, 'viewDetailAssignReviewer']);
    Route::post('/view-detail-assign-reviewer/{pairId?}/{examinerId?}/{code?}/{operation}', [EvaluationController::class, 'viewDetailAssignReviewer']);

    // council-auto-marking
    Route::get('/council-auto-marking', [EvaluationController::class, 'councilAutoMarking']);
    Route::get('/council-auto-marking/{operation}', [EvaluationController::class, 'councilAutoMarking']);
    Route::get('/council-auto-marking/{operation}', [EvaluationController::class, 'councilAutoMarking']);
    Route::post('/council-auto-marking', [EvaluationController::class, 'councilAutoMarking']);
    Route::post('/council-auto-marking/{operation}', [EvaluationController::class, 'councilAutoMarking']);
    Route::post('/council-auto-marking/{operation}', [EvaluationController::class, 'councilAutoMarking']);

    // examinee-auto-marking
    Route::get('/examinee-auto-marking', [EvaluationController::class, 'examineeAutoMarking']);
    Route::get('/examinee-auto-marking/{operation}', [EvaluationController::class, 'examineeAutoMarking']);
    Route::get('/examinee-auto-marking/{operation}', [EvaluationController::class, 'examineeAutoMarking']);
    Route::post('/examinee-auto-marking', [EvaluationController::class, 'examineeAutoMarking']);
    Route::post('/examinee-auto-marking/{operation}', [EvaluationController::class, 'examineeAutoMarking']);
    Route::post('/examinee-auto-marking/{operation}', [EvaluationController::class, 'examineeAutoMarking']);
    
    // examinee-tests
    Route::get('/examinee-tests/{code?}', [EvaluationController::class, 'examineeTests']);
    Route::get('/examinee-tests/{code?}/{operation}', [EvaluationController::class, 'examineeTests']);
    Route::get('/examinee-tests/{code?}/{operation}', [EvaluationController::class, 'examineeTests']);
    Route::post('/examinee-tests/{code?}', [EvaluationController::class, 'examineeTests']);
    Route::post('/examinee-tests/{code?}/{operation}', [EvaluationController::class, 'examineeTests']);
    Route::post('/examinee-tests/{code?}/{operation}', [EvaluationController::class, 'examineeTests']);

    Route::get('/auto-making-examinee-answers', [EvaluationController::class, 'autoMarkingByExamineeTests']);
    Route::get('/auto-making-examinee-answer/{id?}', [EvaluationController::class, 'autoMarkingByExamineeTest']);
    Route::get('/auto-marking-all/{code?}', [EvaluationController::class, 'autoMarkingByCouncil']);
    Route::get('/check-correct-examinee-answer/{answerId?}', [EvaluationController::class, 'checkCorrectByExamineeAnswer']);
    Route::get('/check-incorrect-examinee-answer/{answerId?}', [EvaluationController::class, 'checkIncorrectByExamineeAnswer']);
    // Route::post('/auto-making-examinee-answer/{id?}', [EvaluationController::class, 'autoMarkingByExamineeTest']);

    // detail-examinee-answers
    Route::get('/detail-examinee-answers/{code?}', [EvaluationController::class, 'detailExamineeAnswers']);
    Route::get('/detail-examinee-answers/{code?}/{operation}', [EvaluationController::class, 'detailExamineeAnswers']);
    Route::get('/detail-examinee-answers{code?}/{operation}', [EvaluationController::class, 'detailExamineeAnswers']);
    Route::post('/detail-examinee-answers/{code?}', [EvaluationController::class, 'detailExamineeAnswers']);
    Route::post('/detail-examinee-answers/{code?}/{operation}', [EvaluationController::class, 'detailExamineeAnswers']);
    Route::post('/detail-examinee-answers/{code?}/{operation}', [EvaluationController::class, 'detailExamineeAnswers']);

    // result-list
    Route::get('/result-list', [EvaluationController::class, 'resultList']);
    Route::get('/result-list/{operation}', [EvaluationController::class, 'resultList']);
    Route::get('/result-list/{operation}', [EvaluationController::class, 'resultList']);
    Route::post('/result-list', [EvaluationController::class, 'resultList']);
    Route::post('/result-list/{operation}', [EvaluationController::class, 'resultList']);
    Route::post('/result-list/{operation}', [EvaluationController::class, 'resultList']);

    // result-list-with-rubric
    Route::get('/result-list-with-rubric', [EvaluationController::class, 'resultListWithRubric']);
    Route::get('/result-list-with-rubric/{operation}', [EvaluationController::class, 'resultListWithRubric']);
    Route::get('/result-list-with-rubric/{operation}', [EvaluationController::class, 'resultListWithRubric']);
    Route::post('/result-list-with-rubric', [EvaluationController::class, 'resultListWithRubric']);
    Route::post('/result-list-with-rubric/{operation}', [EvaluationController::class, 'resultListWithRubric']);
    Route::post('/result-list-with-rubric/{operation}', [EvaluationController::class, 'resultListWithRubric']);

    // manage-exam data
    Route::get('/manage-exam-data', [EvaluationController::class, 'manageExamData']);
    Route::get('/manage-exam-data/{operation}', [EvaluationController::class, 'manageExamData']);
    Route::get('/manage-exam-data/{operation}', [EvaluationController::class, 'manageExamData']);
    Route::post('/manage-exam-data', [EvaluationController::class, 'manageExamData']);
    Route::post('/manage-exam-data/{operation}', [EvaluationController::class, 'manageExamData']);
    Route::post('/manage-exam-data/{operation}', [EvaluationController::class, 'manageExamData']);
});

Route::prefix('export')
    // ->middleware([AuthenticateMiddleware::class, EnsureUserHasRoleMiddleware::class.':moderator'])
    ->group(function () {
    //export docx
    Route::get('/docx/examinee', [CouncilController::class, 'exportExaminee2Docx']);
    Route::get('/docx/monitor-turn', [CouncilController::class, 'exportMonitor2Docx']);
    Route::get('/docx/monitor-council/{council_id?}', [CouncilController::class, 'exportMonitorByCouncil']);
    //examinee answer 2 docx
    Route::get('/docx/examiner-answer/{code?}/{type?}', [EvaluationController::class, 'exportExamineeAnswer2Docx']);
    // export xlsx
    Route::get('/xlsx/monitor-council/{council_id?}', [CouncilController::class, 'exportMonitorByCouncil2Excel']);
    Route::get('/xlsx/examiner-pair-diff/{pairId?}', [EvaluationController::class, 'exportExaminerRubricDetailDiff']);
    Route::get('/xlsx/reviewer-pair-diff/{pairId?}', [EvaluationController::class, 'exportReviewerRubricDetailDiff']);
    Route::get('/xlsx/all-examiner-pair', [EvaluationController::class, 'exportAllExaminerRubricDetail']);
    Route::get('/xlsx/examiner-pair/{pairId?}', [EvaluationController::class, 'exportExaminerRubricDetail']);
    Route::get('/xlsx/reviewer-pair/{pairId?}', [EvaluationController::class, 'exportReviewerRubricDetail']);
    Route::get('/xlsx/summary-examiner-pair', [EvaluationController::class, 'exportSummaryExaminerPair']);
    Route::get('/xlsx/export-result-list/{councilId?}', [EvaluationController::class, 'exportResultList']);
    Route::get('/xlsx/export-result-list-by-turn/{code?}', [EvaluationController::class, 'exportResultListByTurn']);
    Route::get('/xlsx/export-result-list-with-rubric/{councilId?}', [EvaluationController::class, 'exportResultListWithRubric']);
    Route::get('/xlsx/export-detail-examinee-answers/{councilId?}', [EvaluationController::class, 'exportDetailExamineeAnswers']);
    Route::get('/xlsx/export-detail-examinee-answers-by-turn/{code?}', [EvaluationController::class, 'exportDetailExamineeAnswersByTurn']);

});
