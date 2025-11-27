<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use App\Models\Council;
use App\Models\CouncilTurn;
use App\Models\CouncilTurnRoom;
use App\Models\Examinee;
use App\Models\ExamineeAnswer;
use App\Models\ActivityLog;
use App\Models\AnswerKey;
use App\Models\ExamineeTestMix;
use App\Models\TestMix;
use App\Models\TestGroup;
use App\Models\TestForm;
use App\Models\TestPart;
use App\Models\Subject;
use App\Models\Question;
use App\Models\QuestionMark;

use App\Models\Rubric;
use App\Models\RubricCriteria;
use App\Models\ExaminerRubricDetail;
use App\Models\ExaminerPair;
use App\Models\ExaminerPairDetail;
use App\Models\ExaminerAssignment;
use App\Models\ReviewerRubricDetail;
use App\Models\ReviewerPair;
use App\Models\ReviewerPairDetail;
use App\Models\ReviewerAssignment;
use App\Models\ReviewerPairAssignment;
use App\Models\Monitor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GroceryCrud\Core\GroceryCrud;
use Illuminate\Support\Facades\Storage;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AllRubricDetailExport;
use App\Exports\ExaminerRubricDetailExport;
use App\Exports\ReviewerRubricDetailExport;
use App\Exports\ExaminerRubricDetailDiffExport;
use App\Exports\ReviewerRubricDetailDiffExport;
use App\Exports\ExaminerPairExport;
use App\Exports\ExamineeResultsExport;
use App\Exports\ExamineeResultsByTurnExport;
use App\Exports\ExamineeResultsWithRubricExport;
use App\Exports\DetailExamineeAnswersExport;
use App\Exports\DetailExamineeAnswersByTurnExport;

//service
use App\Services\PhpWordService;

/* DB table name */
define('_USER','users');
define('_ROLE','roles');
define('_RUBRIC','rubrics');
define('_CRITERIA','rubric_criterias');
define('_ORG','organizations');
define('_ROOM','rooms');
define('_MONITOR','monitors');
define('_COUNCIL','councils');
define('_TURN','council_turns');
define('_TURN_ROOM','council_turn_rooms');
define('_EXAMINEE','examinees');
define('_TURN_TEST','test_mixes');
define('_EXAMINEE_TEST_MIXES','examinee_test_mixes');
define('_TURN_TEST_MIX','council_turn_test_mixes');
define('_ANSWER_KEY','answer_keys');
define('_QUESTION','questions');
define('_TEST_ROOT','test_roots');
define('_EXAMINER_PAIR','examiner_pairs');
define('_EXAMINER_PAIR_DETAIL','examiner_pair_details');
define('_EXAMINER_ASSIGNMENT','examiner_assignments');
define('_REVIEWER_PAIR','reviewer_pairs');
define('_REVIEWER_PAIR_DETAIL','reviewer_pair_details');
define('_REVIEWER_PAIR_ASSIGNMENT','reviewer_pair_assignments');

class EvaluationController extends Controller
{
    //
    private $database;
    private $config;
    private $organization;
    private function _getDatabaseConnection() {
        $databaseConnection = config('database.default');
        $databaseConfig = config('database.connections.' . $databaseConnection);
        return [
            'adapter' => [
                'driver' => 'Pdo_Mysql', 
                'host' => $databaseConfig['host'],
                'port' => $databaseConfig['port'],
                'database' => $databaseConfig['database'],
                'username' => $databaseConfig['username'],
                'password' => $databaseConfig['password'],
                'charset' => 'utf8'
            ]
        ];
    }
    private function _example_output($output = null, $title = '', $cat = '', $subcat = '', $data = null) {
        if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            // header('Content-Type: application/json; charset=utf-8');
            // echo $output->output;
            // exit;
            return response($output->output, 200)
                ->header('Content-Type', 'application/json')
                ->header('charset', 'utf-8');
        }
        $js_files = $output->js_files;
        $css_files = $output->css_files;
        $output = $output->output;
        // dd($data);
        return view('pages.evaluation_crud_template', [
            'title' => $title,
            'cat' => $cat,
            'sub_cat' => $subcat,
            'data' => $data,
            'output' => $output,
            'css_files' => $css_files,
            'js_files' => $js_files
        ]);
    }

    public function __construct()
	{
		//parent::__construct();
		// Your own constructor code
		$this->database = $this->_getDatabaseConnection();
        $this->config = config('grocerycrud');
        $this->organization = config('app.organization');
	}

    public function index()
    {
        // if(!Auth::check()){
        //     return Redirect('login');
        // }
  
        // $title = 'Tổ chức thi';
        $title = 'EXAM MANAGEMENT';
        $cat = $subcat = '';
        $output = (object)[
            'css_files' => [],
            'js_files' => [],
            'output' => '<div class="bg-red fg-white p-2">Vui lòng chọn chức năng.</div>'
        ];
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * Rubrics management
     */
    public function rubric()
    {
        $title = 'Rubrics';
        $cat = 'evaluation';
        $subcat = 'rubric';
        $table = _RUBRIC;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('rubrics.deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'subject_id', 'matrix_location','status'])
            ->addFields(['code', 'name', 'desc', 'subject_id', 'matrix_location','status'])
            // ->setClone()
            // ->cloneFields(['code', 'name', 'desc','status'])
            ->editFields(['code', 'name', 'desc', 'subject_id', 'matrix_location','status'])
            ->requiredFields(['code', 'name'])
            ->displayAs([
                'code' => 'Mã',
                'name' => 'Tên',
                'desc' => 'Diễn giải',
                'subject_id' => 'Môn thi',
                'matrix_location' => 'Vị trí ma trận',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->setRelation('subject_id', 'subjects', 'desc')
			->unsetReadFields(['deleted_at'])
			->unsetAddFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();

        $crud->setActionButton('Tiêu chí chấm', 'fa fa-reorder', function ($row) {
            return url('/evaluation/rubric-criteria/' . $row->id);
        }, true);

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            return $data;
        });

        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
            // Custom error messages are only available on Grocery CRUD Enterprise
            DB::table($table)
                ->where('code', $stateParameters->primaryKeyValue)
                ->update(['status' => 0]);
            DB::table($table)
                ->where('code', $stateParameters->primaryKeyValue)
                ->delete();
            return $stateParameters;
        });
        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * Rubric criteria management
     */
    public function rubricCriteria($rubric_id = 0)
    {
        $title = 'Tiêu chí Rubrics';
        $cat = 'evaluation';
        $subcat = 'rubric';
        $table = _CRITERIA;
        $score_arr = ['0','0.25','0.5','0.75','1','1.25','1.5','1.75','2','2.25','2.5','2.75','3','3.25','3.5','3.75','4','4.25','4.5','4.75','5','5.25','5.5','5.75','6','6.25','6.5','6.75','7','7.25','7.5','7.75','8','8.25','8.5','8.75','9','9.25','9.5','9.75','10'];
        $scores = [];
        foreach($score_arr as $score){
            $scores[$score] = $score;
        }
        $rubric = Rubric::find($rubric_id);

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['rubric_id' => $rubric_id])
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'rubric_id',
                'code', 
                'name', 
                'max_score',
                'scores', 
                'status'
            ])
            ->addFields([
                'rubric_id',
                'code', 
                'name', 
                'desc', 
                // 'min_score',
                'max_score',
                'scores',
                'status'
            ])
            ->setClone()
            ->cloneFields([
                'rubric_id',
                'code', 
                'name', 
                'desc', 
                // 'min_score',
                'max_score',
                'scores',
                'status'
            ])
            ->editFields([
                'code', 
                'name', 
                'desc', 
                'rubric_id',
                // 'min_score',
                'max_score',
                'scores',
                'status'
            ])
            ->requiredFields([
                'rubric_id',
                'code', 
                'name', 
                // 'desc', 
                // 'min_score',
                'max_score',
                'scores',
                'status'
            ])
            ->displayAs([
                'rubric_id' => 'Rubric',
                'code' => 'Mã',
                'name' => 'Tên',
                'desc' => 'Diễn giải',
                // 'min_score' => 'Điểm thấp nhất',
                'max_score' => 'Điểm cao nhất',
                'scores' => 'Mức điểm',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            // ->fieldType('min_score', 'float')
            ->fieldType('max_score', 'float')
            ->fieldType('scores', 'multiselect_searchable', $scores)
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->setRelation('rubric_id', 'rubrics', 'name')
			->unsetReadFields(['deleted_at'])
			->unsetAddFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->setExport();

        $crud->callbackAddForm(function ($data) use($rubric) {
            $data['status'] = 1;
            $data['code'] = $rubric->code . '_';
            $data['rubric_id'] = $rubric->id;
            return $data;
        });

        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
            // Custom error messages are only available on Grocery CRUD Enterprise
            DB::table($table)
                ->where('code', $stateParameters->primaryKeyValue)
                ->update(['status' => 0]);
            DB::table($table)
                ->where('code', $stateParameters->primaryKeyValue)
                ->delete();
            return $stateParameters;
        });
        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function councilExamData(){
        $title = 'Đồng bộ bài thi';
        $cat = 'evaluation';
        $subcat = 'sync';
        $table = _COUNCIL;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('councils.status', 1)
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'organization_code', 
                'code', 
                // 'desc', 
                'no_turns', 
                'start_at', 
                'finish_at', 
                // 'monitor_id', 
                // 'is_autostart', 
                'status'
            ])
            ->displayAs([
                'code' => 'Mã HĐ thi',
                'desc' => 'Diễn giải',
                'no_turns' => 'Số ca thi',
                'organization_code' => 'Điểm thi',
                'start_at' => 'Ngày bắt đầu',
                'finish_at' => 'Ngày kết thúc',
                'monitor_id' => 'Điểm trưởng',
                'is_autostart' => 'Cách khởi tạo',
                'import_testdata_before_time' => 'Cho phép nhập đề trước giờ thi (phút)',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('is_autostart', 'dropdown_search',[
                1 => 'Tự động',
                0 => 'Thủ công'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('no_turns', 'dropdown_search', [
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
                '10' => 10,
            ])
            ->fieldType('start_at','date')
            ->fieldType('finish_at','date')
            ->setPrimaryKey('code', 'organizations')
            ->setRelation('organization_code', 'organizations', '{code} - {name}')
            // ->setRelation('monitor_id', 'monitors', 'name', ['role_id' => 7])
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        // $crud->callbackColumn('no_turns', function ($value, $row) {
        //     return "
        //     <div class=\"gc-data-container-text\">Số lượng ca thi: " . $value . "</div><br>
        //     <div class=\"gc-data-container-text\">
        //         <div class=\"cl_manage_parts\">
        //             <a class=\"btn btn-outline-dark r20\" href=\"" . url('exam/council-turns/' . $row->code) . "\">Quản trị các ca thi</a>
        //         </div>
        //     </div>
        //     ";
        // });

        // $crud->callbackColumn('status', function ($value, $row) {
        //     //so bai thi
        //     $count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
        //     if($count_examinee_test_mixes) $html = "<div class=\"bg-success text-white\">Đã đồng bộ</div><br>";
        //     else $html = "<div class=\"bg-danger text-white\">Chưa đồng bộ</div><br>";
        //     return $html;
        // });

        $crud->setActionButton('Đồng bộ bài thi', 'fa fa-cloud-upload', function ($row) {
            return url('/evaluation/sync-exam-data/' . $row->code);
        }, false);
        // $crud->setActionButton('Xuất CBCT', 'fa fa-file-excel-o', function ($row) {
        //     return '/export/xlsx/monitor-council/' . $row->id;
        // }, true);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        // return $this->_example_output($output, $title, $cat, $subcat);

        $form_url = '/exam/import-council';
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'form_url' => $form_url,
            'data' => 'council_data'
        ]);
    }

    public function syncExamData($code){
        $title = 'Thống kê dữ liệu đồng bộ';
        $cat = 'evaluation';
        $subcat = 'sync';
        $table = _TURN;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['council_code' => $code])
            ->setSubject($title, $title)
            ->columns([
                'council_code', 
                'code', 
                'name', 
                'no_rooms',
                'status'
            ])
            ->displayAs([
                'code' => 'Mã ca thi',
                'name' => 'Ca thi',
                'no_rooms' => 'Thống kê',
                'council_code' => 'Mã HĐ thi'
            ])
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('no_rooms', function ($value, $row) {
            //so bai thi
            $count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
            $count_examinee_test_mixes_to = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',1)->count();
            $count_examinee_test_mixes_li = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',2)->count();
            $count_examinee_test_mixes_ho = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',3)->count();
            $count_examinee_test_mixes_si = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',4)->count();
            $count_examinee_test_mixes_va = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',5)->count();
            //so bai thi da gan
            $count_assigned_test = ExaminerPair::where('council_turn_code',$row->code)->sum('no_tests');
            $html = "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $count_examinee_test_mixes . "</div><br>";
            if($count_examinee_test_mixes_to) $html .= "<div class=\"gc-data-container-text\">Toán: " . $count_examinee_test_mixes_to . "</div><br>";
            if($count_examinee_test_mixes_li) $html .= "<div class=\"gc-data-container-text\">Lí: " . $count_examinee_test_mixes_li . "</div><br>";
            if($count_examinee_test_mixes_ho) $html .= "<div class=\"gc-data-container-text\">Hoá: " . $count_examinee_test_mixes_ho . "</div><br>";
            if($count_examinee_test_mixes_si) $html .= "<div class=\"gc-data-container-text\">Sinh: " . $count_examinee_test_mixes_si . "</div><br>";
            if($count_examinee_test_mixes_va) {
                $html .= "<div class=\"gc-data-container-text\">Văn: " . $count_examinee_test_mixes_va . " (Đã gán: " . $count_assigned_test . ", Còn lại: " . ($count_examinee_test_mixes_va - $count_assigned_test) . ")</div><br>";
            }
            return $html;
        });
        $crud->callbackColumn('status', function ($value, $row) {
            //so bai thi
            $count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
            if($count_examinee_test_mixes) $html = "<div class=\"bg-success text-white\">Đã đồng bộ</div><br>";
            else $html = "<div class=\"bg-danger text-white\">Chưa đồng bộ</div><br>";
            return $html;
        });

        // $crud->setActionButton('Đồng bộ bài thi', 'fa fa-cloud-upload', function ($row) {
        //     return '/evaluation/sync-exam-data/' . $row->code;
        // }, false);
        // $crud->setActionButton('Xuất CBCT', 'fa fa-file-excel-o', function ($row) {
        //     return '/export/xlsx/monitor-council/' . $row->id;
        // }, true);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        // return $this->_example_output($output, $title, $cat, $subcat);

        $form_url = url('/evaluation/import-exam-data');
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'form_url' => $form_url,
            'council_turns' => CouncilTurn::where('council_code',$code)->get(),
            'data' => 'sync-exam-data'
        ]);
    }

    public function importExamData(Request $request){
        $council_code = $request->input('council_code');
        $council_turn_code = $request->input('council_turn_code');

        //import test data
        // Store the decryption key
        $file = $request->file('testdata');
        $password = $request->input('testpassword');
        if($file && $password){
            $import_time = time();
            $path = 'testdata/import/' . $council_turn_code . '/';
            $filename = $import_time. '_' . $council_turn_code . '.dat';
            $path = $file->storeAs(
                $path, $filename
            );
    
            $encryption = Storage::get($path);
            // Use openssl_decrypt() function to decrypt the data
            // Store the cipher method
            $ciphering = "AES-128-CTR";
            $options = 0;
            // Non-NULL Initialization Vector for decryption
            $decryption_key = $password;
            $decryption_iv = '1234567891011121';
            $decryption = openssl_decrypt ($encryption, $ciphering, $decryption_key, $options, $decryption_iv);
            Storage::put('testdata/decrypt/' . $filename,$decryption);
            $test_mixes_content = json_decode($decryption,TRUE);
            if(!$test_mixes_content){
                return redirect()->back()->with('message', 'Something wrong!');
            }
            //import to DB
            $council_turn = CouncilTurn::where('code',$council_turn_code)->first();
            if(!$council_turn){
                return redirect()->back()->with('message', 'Something wrong!');
            }
    
            $header = $test_mixes_content['header'];
            $test_group = $header['test_group'];
            if(!(TestGroup::where('id',$test_group['id'])->count())){
                $test_group['encryption_file'] = $filename;
                TestGroup::create($test_group);
            }
    
            $body = $test_mixes_content['body'];
            $test_form_array = $body['test_form'];
            foreach($test_form_array as $test_form){
                if(!(TestForm::where('id',$test_form['id'])->count())){
                    TestForm::create($test_form);
                }
            }
            $test_part_array = $body['test_part'];
            foreach($test_part_array as $test_part){
                if(!(TestPart::where('id',$test_part['id'])->count())){
                    TestPart::create($test_part);
                }
            }
            $subject_array = $body['subject'];
            foreach($subject_array as $subject){
                if(!(Subject::where('id',$subject['id'])->count())){
                    Subject::create($subject);
                }
            }
            $test_mixes_array = $body['test_mixes'];
            foreach($test_mixes_array as $test_mix){
                if(!(TestMix::where('id',$test_mix['id'])->count())){
                    TestMix::create($test_mix);
                }
            }
            $question_array = $body['questions'];
            foreach($question_array as $question){
                $question['question_mark_id'] = 1;
                $question['competency_id'] = 1;
                $question['taxonomy_id'] = 1;
                $question['topic_id'] = 1;
                $question['difficult_id'] = 1;
                if(!(Question::where('id',$question['id'])->count())){
                    Question::create($question);
                }
            }
        }

        //import exam data
        $import_time = time();
        $path = 'councildata/';
        $filename = $import_time . '.dat';
        $file = $request->file('examdata');
        if(!$file){
            return redirect()->back()->with('message', 'Something wrong!');
        }
        $path = $file->storeAs(
            $path, $filename
        );
        $data = Storage::get($path);
        $exam_data = json_decode($data,TRUE);
        if(!$exam_data){
            return redirect()->back()->with('message', 'Something wrong!');
        }
        if(!isset($exam_data['council_turn_code']) || $council_turn_code != $exam_data['council_turn_code']){
            return redirect()->back()->with('message', 'Không đúng dữ liệu ca thi');
        }
        //examinee test mixes
        foreach($exam_data['examinee_test_mixes'] as $item){
            $count = ExamineeTestMix::where('council_turn_code',$item['council_turn_code'])->where('subject_id',$item['subject_id'])->count();
            $check = ExamineeTestMix::where('examinee_test_uuid',$item['examinee_test_uuid'])->count();
            if(!$check){
                //danh ma phach
                $subject = Subject::find($item['subject_id']);
                $test_code = $subject->code . '.' . $item['council_turn_code'] . '.' . ($count + 1);
                $item['examinee_test_code'] = $test_code;
                ExamineeTestMix::create($item);
            }
            if(TestMix::where('id',$item['test_mix_id'])->where('is_used',0)->count()){
                TestMix::where('id',$item['test_mix_id'])->update([
                    'is_used' => 1,
                    'used_time' => $item['start_time']
                ]);
            }
        }
        //examinee answers
        foreach($exam_data['examinee_answers'] as $item){
            $question = Question::find($item['question_id']);
            $examinee_test_mix = ExamineeTestMix::where('examinee_test_uuid',$item['examinee_test_uuid'])->first();
            $examinee_answer = ExamineeAnswer::where('examinee_answer_uuid',$item['examinee_answer_uuid'])->first();
            if(!$examinee_answer){
                $examinee_answer = ExamineeAnswer::create($item);
            }
            //kiem tra answer_key cua thi sinh voi question_id, 
            $answer_key = AnswerKey::where('examinee_test_code',$examinee_test_mix['examinee_test_code'])
                                    // ->where('examinee_answer_uuid',$item['examinee_answer_uuid'])
                                    ->where('question_id',$item['question_id'])
                                    ->where('matrix_location',$item['question_number'])
                                    ->first();
            if(!$answer_key){
                // dd($item);
                // dd($item['question_number']);
                // dd($item['submitting_time']);
                $rubric = Rubric::where('subject_id',$item['subject_id'])->where('matrix_location',$item['question_number'])->first();
                $answer_key = AnswerKey::create([
                    'examinee_test_uuid' => $item['examinee_test_uuid'],
                    'examinee_answer_uuid' => $item['examinee_answer_uuid'],
                    'examinee_test_code' => $examinee_test_mix['examinee_test_code'],
                    'council_code' => $examinee_test_mix['council_code'],
                    'council_turn_code' => $examinee_test_mix['council_turn_code'],
                    'subject_id' => $item['subject_id'],
                    'question_id' => $item['question_id'],
                    'matrix_location' => $item['question_number'],
                    'question_type_id' => $item['question_type_id'],
                    'examinee_answer' => $item['answer_detail'],
                    'submitting_time' => $item['submitting_time'],
                    'answer_type' => $question?$question->answer_type:1,
                    'answer_key' => $question?$question->answer_key:'',
                    'question_mark_id' => $question?$question->question_mark_id:1,
                    'rubric_id' => $rubric?$rubric->id:0,
                    // 'is_correct' => $item['examinee_answer_uuid'],
                    // 'score' => $item['examinee_answer_uuid'],
                ]);
            }else{
                if(intval($answer_key->submitting_time) < intval($item['submitting_time'])){
                    $answer_key->submitting_time = $item['submitting_time'];
                    $answer_key->save();
                }
            }
        }
        //activity logs
/*        foreach($exam_data['activity_logs'] as $item){
            $check = ActivityLog::where('log_uuid',$item['log_uuid'])->count();
            if(!$check){
                ActivityLog::create($item);
            }
        }
*/
        return redirect()->back()->with('message', 'Data is synced successfully.');
    }    

    public function manageTestRoot(Request $request){
        $title = 'Quản lý đáp án';
        $cat = 'evaluation';
        $subcat = 'answer-key';
        $table = _ANSWER_KEY;
        $form_url = url('/evaluation/import-answer-key');


        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            // ->where(['examinee_test_code' => $code])
            ->setSubject($title, $title)
            ->columns([
                'examinee_test_code',
                'subject_id',
                'question_id',
                'question_type_id',
                'matrix_location',
                'examinee_answer',
                'answer_key',
                // 'answer_type',
                'question_mark_id',
                'is_correct',
                'score',
                'rubric_id',
                'is_assigned',
                ])
            ->displayAs([
                'examinee_test_code' => 'Mã phách',
                'subject_id' => 'Môn thi',
                'question_id' => 'Mã câu hỏi',
                'question_type_id' => 'Loại câu hỏi',
                'matrix_location' => 'Vị trí câu hỏi',
                'examinee_answer' => 'Thí sinh trả lời',
                'answer_key' => 'Đáp án gốc',
                'answer_type' => 'Loại đáp án',
                'question_mark_id' => 'Mức điểm',
                'is_correct' => 'Đúng/Sai',
                'score' => 'Điểm đạt được',
                'rubric_id' => 'Rubric',
                'is_assigned' => 'Gán chấm',
            ])
            ->setRead()
            ->setRelation('subject_id', 'subjects', 'desc')
            ->setRelation('question_mark_id', 'question_marks', 'value')
            ->setRelation('question_type_id', 'question_types', 'code')
            ->setRelation('rubric_id', 'rubrics', 'name')
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();


        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'form_url' => $form_url,
            'data' => 'answer-key'
        ]);
    }

    public function manageAnswerKey(){
        $title = 'Quản lý đáp án';
        $cat = 'evaluation';
        $subcat = 'answer-key';
        // $table = _ANSWER_KEY;
        $table = _QUESTION;
        $form_url = url('/evaluation/import-answer-key');


        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            // ->where('question_type_id = 1 OR question_type_id = 2 OR question_type_id = 4')
            ->setSubject($title, $title)
            ->columns([
                'question_type_id',
                'code',
                'subject_id',
                'answer_key',
                'answer_type',
                'question_mark_id',
                'content',
                ])
            ->displayAs([
                'question_type_id' => 'Loại câu hỏi',
                'code' => 'Mã câu hỏi',
                'subject_id' => 'Môn thi',
                'answer_key' => 'Đáp án',
                'answer_type' => 'Kiểu đáp án',
                'question_mark_id' => 'Điểm đạt được',
                'content' => 'Nội dung',
            ])
            ->setRead()
            ->editFields([
                'code',
                'answer_key',
                'answer_type',
                'question_mark_id'
            ])
            ->setRelation('subject_id', 'subjects', 'desc')
            ->setRelation('question_mark_id', 'question_marks', 'value')
            ->setRelation('question_type_id', 'question_types', 'code')
            ->defaultOrdering('questions.subject_id', 'asc')
			->unsetAdd()
			// ->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->callbackAfterUpdate(function ($stateParameters) {
            AnswerKey::where('question_id',$stateParameters->primaryKeyValue)->update([
                'answer_key' => $stateParameters->data['answer_key'],
                'answer_type' => $stateParameters->data['answer_type'],
                'question_mark_id' => $stateParameters->data['question_mark_id'],
            ]);
        
            return $stateParameters;
        });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'form_url' => $form_url,
            'data' => 'answer-key'
        ]);
    }

    public function importAnswerKey(Request $request){
        //import test data
        // Store the decryption key
        $password = $request->input('testpassword');

        $import_time = time();
        $path = 'testdata/import/answerkey/';
        $filename = $import_time. '_' . '.key';
        $file = $request->file('testdata');
        $path = $file->storeAs(
            $path, $filename
        );

        $encryption = Storage::get($path);
        // Use openssl_decrypt() function to decrypt the data
        // Store the cipher method
        $ciphering = "AES-128-CTR";
        $options = 0;
        // Non-NULL Initialization Vector for decryption
        $decryption_key = $password;
        $decryption_iv = '1234567891011121';
        $decryption = openssl_decrypt ($encryption, $ciphering, $decryption_key, $options, $decryption_iv);
        Storage::put('testdata/decrypt/' . $filename,$decryption);
        $test_mixes_content = json_decode($decryption,TRUE);
        if(!$test_mixes_content){
            return redirect()->back()->with('message', 'Wrong password or No data!');
        }
        //import to DB
        $header = $test_mixes_content['header'];
        $test_group = $header['test_group'];
        if(!(TestGroup::where('id',$test_group['id'])->count())){
            $test_group['encryption_file'] = $filename;
            TestGroup::create($test_group);
        }

        $body = $test_mixes_content['body'];
        $test_form_array = $body['test_form'];
        foreach($test_form_array as $test_form){
            if(!(TestForm::where('id',$test_form['id'])->count())){
                TestForm::create($test_form);
            }
        }
        $test_part_array = $body['test_part'];
        foreach($test_part_array as $test_part){
            if(!(TestPart::where('id',$test_part['id'])->count())){
                TestPart::create($test_part);
            }
        }
        $subject_array = $body['subject'];
        foreach($subject_array as $subject){
            if(!(Subject::where('id',$subject['id'])->count())){
                Subject::create($subject);
            }
        }
        $test_mixes_array = $body['test_mixes'];
        foreach($test_mixes_array as $test_mix){
            if(!(TestMix::where('id',$test_mix['id'])->count())){
                TestMix::create($test_mix);
            }else{
		TestMix::where('id',$test_mix['id'])->update(['test_root_id'=>$test_mix['test_root_id']]);
	}
        }
        $question_array = $body['questions'];
        foreach($question_array as $question){
            //không nhập đáp án và điểm cho câu ngữ cảnh và tự luận
            if($question['question_type_id'] == 3 || $question['question_type_id'] == 5) continue;
            // $question['question_mark_id'] = 1;
            $question['competency_id'] = 1;
            $question['taxonomy_id'] = 1;
            $question['topic_id'] = 1;
            $question['difficult_id'] = 1;
            $_question = Question::find($question['id']);
            if($_question){
                $_question->answer_key = $question['answer_key'];
                $_question->answer_type = $question['answer_type'];
                $_question->question_mark_id = $question['question_mark_id'];
                $_question->matrix_location = $question['matrix_location'];
                $_question->save();
                AnswerKey::where('question_id',$question['id'])->update([
                    'answer_key' => $question['answer_key'],
                    'answer_type' => $question['answer_type'],
                    'question_mark_id' => $question['question_mark_id'],
                ]);
                // $_answer_key = AnswerKey::where('question_id',$question['id'])->first();
                // if($_answer_key){
                //     $_answer_key->answer_key = $question['answer_key'];
                //     $_answer_key->answer_type = $question['answer_type'];
                //     $_answer_key->question_mark_id = $question['question_mark_id'];
                //     $_answer_key->save();
                // }
            }
        }

        return redirect()->back()->with('message', 'Answer keys is imported successfully.');
    }

    public function summarySyncData(){
        $title = 'Thống kê chấm thi';
        $cat = 'evaluation';
        $subcat = 'summary-sync-data';
        $table = _TURN;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('status', 1)
            ->setSubject($title, $title)
            ->columns([
                'council_code', 
                'code', 
                'name', 
                'no_rooms',
                'status'
            ])
            ->displayAs([
                'code' => 'Mã ca thi',
                'name' => 'Ca thi',
                'no_rooms' => 'Thống kê',
                'council_code' => 'Mã HĐ thi'
            ])
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('no_rooms', function ($value, $row) {
            $html = '';
            // //so bai thi
            // $_count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
            // $count_examinee_test_mixes_to = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',1)->count();
            // $count_examinee_test_mixes_li = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',2)->count();
            // $count_examinee_test_mixes_ho = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',3)->count();
            // $count_examinee_test_mixes_si = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',4)->count();
            // $count_examinee_test_mixes_va = ExamineeTestMix::where('council_turn_code',$row->code)->where('subject_id',5)->count();
            // //so bai thi da gan
            // $count_assigned_test = ExaminerPair::where('council_turn_code',$row->code)->sum('no_tests');
            // $html = "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $count_examinee_test_mixes . "</div><br>";
            // if($count_examinee_test_mixes_to) $html .= "<div class=\"gc-data-container-text\">Toán: " . $count_examinee_test_mixes_to . "</div><br>";
            // if($count_examinee_test_mixes_li) $html .= "<div class=\"gc-data-container-text\">Lí: " . $count_examinee_test_mixes_li . "</div><br>";
            // if($count_examinee_test_mixes_ho) $html .= "<div class=\"gc-data-container-text\">Hoá: " . $count_examinee_test_mixes_ho . "</div><br>";
            // if($count_examinee_test_mixes_si) $html .= "<div class=\"gc-data-container-text\">Sinh: " . $count_examinee_test_mixes_si . "</div><br>";
            // if($count_examinee_test_mixes_va) {
            //     $html .= "<div class=\"gc-data-container-text\">Văn: " . $count_examinee_test_mixes_va . " (Đã gán: " . ($count_assigned_test) . ", Còn lại: " . ($count_examinee_test_mixes_va - $count_assigned_test) . ")</div><br>";
            // }

            //so bai thi
            $count_examinee_test_mixes = AnswerKey::where('council_turn_code',$row->code)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_to = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',1)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_li = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',2)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_ho = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',3)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_si = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',4)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_va = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',5)->distinct('examinee_test_code')->count('examinee_test_code');
            
            // $html .= "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $_count_examinee_test_mixes . "</div><br>";
            $html .= "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $count_examinee_test_mixes . "</div><br>";
            if($count_examinee_test_mixes_to) $html .= "<div class=\"gc-data-container-text\">Toán: " . $count_examinee_test_mixes_to . "</div><br>";
            if($count_examinee_test_mixes_li) $html .= "<div class=\"gc-data-container-text\">Lí: " . $count_examinee_test_mixes_li . "</div><br>";
            if($count_examinee_test_mixes_ho) $html .= "<div class=\"gc-data-container-text\">Hoá: " . $count_examinee_test_mixes_ho . "</div><br>";
            if($count_examinee_test_mixes_si) $html .= "<div class=\"gc-data-container-text\">Sinh: " . $count_examinee_test_mixes_si . "</div><br>";
            if($count_examinee_test_mixes_va) $html .= "<div class=\"gc-data-container-text\">Văn: " . $count_examinee_test_mixes_va . "</div><br>";
            return $html;
        });
        $crud->callbackColumn('status', function ($value, $row) {
            //so bai thi
            $count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
            if($count_examinee_test_mixes) $html = "<div class=\"bg-success text-white\">Đã đồng bộ</div><br>";
            else $html = "<div class=\"bg-danger text-white\">Chưa đồng bộ</div><br>";
            return $html;
        });

        // $crud->setActionButton('Đồng bộ bài thi', 'fa fa-cloud-upload', function ($row) {
        //     return '/evaluation/sync-exam-data/' . $row->code;
        // }, false);
        // $crud->setActionButton('Xuất CBCT', 'fa fa-file-excel-o', function ($row) {
        //     return '/export/xlsx/monitor-council/' . $row->id;
        // }, true);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        // return $this->_example_output($output, $title, $cat, $subcat);

        // $form_url = '/exam/import-council';
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function summaryExaminerPair(){
        $title = 'Thống kê phân công chấm thi';
        $cat = 'evaluation';
        $subcat = 'summary-examiner-pair';
        $table = _EXAMINER_PAIR_DETAIL;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            // ->where('status', 1)
            ->setSubject($title, $title)
            ->columns([
                'examiner_pair_id', 
                'examiner_id', 
                'examiner_role', 
                'no_assigned_test', 
                'no_done_test', 
                'start_at', 
                'finish_at',
            ])
            ->displayAs([
                'examiner_pair_id' => 'HĐ - Ca',
                'examiner_pair' => 'CBChT',
                'examiner_role' => 'Vai trò',
                'no_assigned_test' => 'Đã gán',
                'no_done_test' => 'Đã chấm',
                'start_at' => 'Ngày BĐ',
                'finish_at' => 'Ngày KT',
            ])
            // ->defaultOrdering('examiner_id', 'asc')
            ->defaultOrdering('examiner_pair_id', 'asc')
            ->setRelation('examiner_pair_id', 'examiner_pairs', 'code')
            ->setRelation('examiner_id', 'monitors', 'name')
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('examiner_pair_id', function ($value, $row) {
            //so bai thi
            $pair = ExaminerPair::find($row->examiner_pair_id);
            $html = "<div class=\"gc-data-container-text\">Mã cặp chấm: " . $pair->code . "</div><br><div class=\"gc-data-container-text\">HĐ thi: " . $pair->council_code . "</div><br><div class=\"gc-data-container-text\">Ca thi: " . $pair->council_turn_code . "</div><br>";
            return $html;
        });
        $crud->callbackColumn('examiner_role', function ($value, $row) {
            //so bai thi
            $pair = ExaminerPair::find($row->examiner_pair_id);
            $html = "<div class=\"gc-data-container-text\">CBChT " . $value . "</div><br>";
            return $html;
        });
        $crud->callbackColumn('no_done_test', function ($value, $row) {
            // $_count = ExaminerAssignment::where('examiner_pair_id',$row->examiner_pair_id)->where('examiner_id',$row->examiner_id)->where('is_done',true)->count();
            if($value == $row->no_assigned_test) {
                $html = "<div class=\"bg-success text-white\">" . $value . "</div><br>";
            }else{
                $html = "<div class=\"gc-data-container-text\">" . $value . "</div><br>";
            }
            
            return $html;
        });

        // $crud->setActionButton('Đồng bộ bài thi', 'fa fa-cloud-upload', function ($row) {
        //     return '/evaluation/sync-exam-data/' . $row->code;
        // }, false);
        // $crud->setActionButton('Xuất CBCT', 'fa fa-file-excel-o', function ($row) {
        //     return '/export/xlsx/monitor-council/' . $row->id;
        // }, true);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        // return $this->_example_output($output, $title, $cat, $subcat);

        // $form_url = '/exam/import-council';
        return $this->_example_output($output, $title, $cat, $subcat,[
            'data' => 'summary-examiner-pair'
        ]);
    }

    public function assignExaminer(){
        $title = 'Phân công chấm thi';
        $cat = 'evaluation';
        $subcat = 'assign-examiner';
        $table = _EXAMINER_PAIR;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['no_tests > ?' => 0])
            ->setSubject($title, $title)
            ->columns([
                'code',
                'council_code',
                'council_turn_code',
                'subject_id',
                'no_tests',
                'examiners',
                'start_at',
                'finish_at',
                ])
            ->displayAs([
                'code' => 'Mã số',
                'council_code' => 'HĐ thi',
                'council_turn_code' => 'Ca thi',
                'subject_id' => 'Môn thi',
                'no_tests' => 'Số bài gán',
                'examiners' => 'Giám khảo',
                'start_at' => 'Ngày bắt đầu',
                'finish_at' => 'Ngày kết thúc',
            ])
            ->setPrimaryKey('code', 'councils')
            ->setRelation('council_code', 'councils', 'code')
            ->setRelation('council_turn_code', 'council_turns', 'name')
            ->setRelation('subject_id', 'subjects', 'desc')
            ->setDependentRelation('council_turn_code', 'council_code', 'council_code')
            ->setRelationNtoN('examiners', 'examiner_pair_details', 'monitors', 'examiner_pair_id', 'examiner_id', '{name}',null,['role_id' => 5])
            ->addFields(['code', 'council_code', 'council_turn_code', 'subject_id','no_tests','examiners','start_at', 'finish_at'])
			// ->unsetAdd()
			// ->unsetEdit()
            ->unsetDelete()
		->unsetPrint();
//		->unsetExport();

        $crud->setActionButton('Chi tiết phân công', 'fa fa-eye', function ($row) {
            return url('/evaluation/detail-assign-examiner/' . $row->code);
        }, true);
        $crud->setActionButton('Bảng chênh lệch', 'fa fa-file-excel-o', function ($row) {
            return url('/export/xlsx/examiner-pair-diff/' . $row->id);
        }, false);
        // $crud->setActionButton('Bảng tổng hợp', 'fa fa-file-excel-o', function ($row) {
        //     return '/evaluation/detail-assign-examiner/' . $row->code;
        // }, false);
        $crud->setActionButton('Bảng chi tiết', 'fa fa-file-excel-o', function ($row) {
            return url('/export/xlsx/examiner-pair/' . $row->id);
        }, false);


        $crud->callbackColumn('no_tests', function ($value, $row) {
            $examiner_pairs = ExaminerPairDetail::where('examiner_pair_id',$row->id)->get();
            //lay so bai da cham cua cap cham
            $no_tests = $row->no_tests;
            $is_done = true;
            foreach($examiner_pairs as $item){
                if($no_tests > $item->no_done_test){
                    $is_done = false;
                    break;
                }
            }
            
            if($is_done) $html = "<div class=\"bg-success text-white\">Xong </div><br>";
            else $html = "<div class=\"bg-danger text-white\">Chưa</div><br>";
            $html .= "<div class=\"gc-data-container-text\">" . $value . "</div>";

            // $diff = $this->countDiff($row->id);
            // //dem so luong lech tu 1 den duoi 2
            // if($diff[0]){
            //     $html .= "<div class=\"bg-danger text-white\">Lech 1.25-1.75</div>";
            // }
            // //dem so luong lech tren 2
            // if($diff[1]){
            //     $html .= "<div class=\"bg-danger text-white\">Lech >=2</div>";
            // }

            return $html;
        });

        $crud->callbackAddForm(function ($data) {
            // $count = ExaminerPair::all()->count();
            $maxId = ExaminerPair::max('id');
            $data['code'] = 'EX_' . ($maxId + 1) . '_' . date('Y') . date('m') . date('d');
        
            return $data;
        });
        $crud->callbackAfterInsert(function ($stateParameters) {
            $data = $stateParameters->data;
            $council_code = $data['council_code'];
            $council_turn_code = $data['council_turn_code'];
            $subject_id = $data['subject_id'];
            //lấy thông tin 2 giám khảo trong cặp chấm
            $examiners = ExaminerPairDetail::where('examiner_pair_id', $stateParameters->insertId)->get();
            foreach($examiners as $i => $examiner){
                $examiner->examiner_role = ($i + 1);
                $examiner->start_at = $data['start_at'];
                $examiner->finish_at = $data['finish_at'];
                $examiner->no_assigned_test = $data['no_tests'];
                $examiner->save();
            }
            for($i = 1; $i <= $data['no_tests']; $i++){
                //lấy 1 bài thi của HĐ thi, ca thi, môn thi mà chưa được gán chấm đủ 2 giám khảo
                $answer_key = AnswerKey::where('council_code',$council_code)
                                        ->where('council_turn_code',$council_turn_code)
                                        ->where('subject_id',$subject_id)
                                        // ->whereIn('matrix_location',[21,22])
                                        ->where('is_assigned',false)
                                        ->inRandomOrder()
                                        ->first();
                if(!$answer_key) break;
                foreach($examiners as $examiner){
                    $examinee_test_code = $answer_key->examinee_test_code;
                    AnswerKey::where('examinee_test_code', $examinee_test_code)->update([
                        'is_assigned' => true
                    ]);
                    ExaminerAssignment::create([
                        'examinee_test_uuid' => $answer_key['examinee_test_uuid'],
                        'examinee_answer_uuid' => $answer_key['examinee_answer_uuid'],
                        'examinee_test_code' => $answer_key['examinee_test_code'],
                        'subject_id' => $answer_key['subject_id'],
                        // 'answer_key_id' => $answer_key['id'],
                        'answer_key_id' => 0,
                        'examiner_id' => $examiner['examiner_id'],
                        'examiner_pair_id' => $examiner['examiner_pair_id'],
                        'start_at' => $data['start_at'],
                        'finish_at' => $data['finish_at'],
                    ]);
                }
            }
            
            return $stateParameters;
        });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'data' => 'summary-assign-examiner'
        ]);
    }

    public function detailAssignExaminer($code){
        $title = 'Phân công chấm thi';
        $cat = 'evaluation';
        $subcat = 'assign-examiner';
        $table = _EXAMINER_PAIR_DETAIL;
        $examiner_pair = ExaminerPair::where('code',$code)->first();
        $examiner_pairs = ExaminerPairDetail::where('examiner_pair_id',$examiner_pair->id)->get();
        foreach($examiner_pairs as $item){
            $no_done = ExaminerAssignment::where('examiner_pair_id',$item->examiner_pair_id)->where('examiner_id',$item->examiner_id)->where('is_done',1)->count();
            $item->no_done_test = $no_done;
            $item->save();
        }
        
        $no_tests = array();
        for ($i=1;$i<=$examiner_pair->no_tests;$i++){
            $no_tests[$i] = $i;
        }
        // dd($no_tests);
        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['examiner_pair_id' => $examiner_pair->id])
            ->setSubject($title, $title)
            ->columns([
                'examiner_pair_id',
                'examiner_id',
                'examiner_role',
                'no_assigned_test',
                'no_done_test',
                'is_assigned',
                ])
            ->displayAs([
                'examiner_pair_id' => 'Mã cặp chấm',
                'examiner_id' => 'Họ tên',
                'examiner_role' => 'Vai trò',
                'no_assigned_test' => 'Số bài gán',
                'no_done_test' => 'Số bài đã chấm',
                'is_assigned' => 'Trạng thái',
            ])
            ->fieldType('examiner_role', 'dropdown_search',[
                1 => 'Giám khảo 1',
                2 => 'Giám khảo 2'
            ])
            ->fieldType('no_assigned_test', 'dropdown_search', $no_tests)
            ->setRelation('examiner_pair_id', 'examiner_pairs', 'code')
            ->setRelation('examiner_id', 'monitors', '{name}')
			->editFields(['examiner_id','examiner_role','no_assigned_test'])
			->unsetAdd()
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->setActionButton('Chi tiết', 'fa fa-eye', function ($row) {
            return url('/evaluation/view-detail-assign-examiner/' . $row->examiner_pair_id . '/' . $row->examiner_id);
        }, true);
        
        $crud->callbackColumn('no_assigned_test', function ($value, $row) use($examiner_pair) {
            $html = "<div class=\"gc-data-container-text\">" . $value . "</div>";

            $diff = $this->countDiff($examiner_pair->id);
            //dem so luong lech tu 1 den duoi 2
            if($diff[0]){
                $html .= "<div class=\"bg-danger text-white\">Lech Doan 1.25-1.75: " . $diff[0] . "</div>";
            }
            if($diff[1]){
                $html .= "<div class=\"bg-danger text-white\">Lech Bai 1.25-1.75: " . $diff[1] . "</div>";
            }
            //dem so luong lech tren 2
            if($diff[2]){
                $html .= "<div class=\"bg-danger text-white\">Lech Doan >=2: " . $diff[2] . "</div>";
                $html .= "<div class=\"gc-data-container-text\">" . implode("<br>",$diff[4]) . "</div>";
            }
            if($diff[3]){
                $html .= "<div class=\"bg-danger text-white\">Lech Bai >=2: " . $diff[3] . "</div>";
                $html .= "<div class=\"gc-data-container-text\">" . implode("<br>",$diff[5]) . "</div>";
            }
            
            return $html;
        });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function viewDetailAssignExaminer($pairId,$examinerId){
        $title = 'Chi tiết bài thi được phân công';
        $cat = 'evaluation';
        $subcat = 'assign-examiner';
        $table = _EXAMINER_ASSIGNMENT;
        // $examiner_pair = ExaminerPair::where('code',$code)->first();
        // $no_tests = array();
        // for ($i=1;$i<=$examiner_pair->no_tests;$i++){
        //     $no_tests[$i] = $i;
        // }
        
        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where([
                'examiner_pair_id' => $pairId,
                'examiner_id' => $examinerId,
            ])
            ->setSubject($title, $title)
            ->columns([
                'examinee_test_code',
                'examiner_pair_id',
                'examiner_id',
                'subject_id',
                'start_at',
                'finish_at',
                'is_done',
                ])
            ->displayAs([
                'examiner_pair_id' => 'Mã cặp chấm',
                'examiner_id' => 'Họ tên',
                'examinee_test_code' => 'Mã phách',
                'subject_id' => 'Môn thi',
                'start_at' => 'Bắt đầu',
                'finish_at' => 'Kết thúc',
                'is_done' => 'Tiến độ',
            ])
            ->fieldType('is_done', 'dropdown_search',[
                1 => 'Đã chấm',
                0 => 'Chưa chấm'
            ])
            ->setRelation('examiner_pair_id', 'examiner_pairs', 'code')
            ->setRelation('examiner_id', 'monitors', '{name}')
            ->setRelation('subject_id', 'subjects', '{desc}')
			->unsetEdit()
			->unsetAdd()
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->setActionButton('Chi tiết', 'fa fa-eye', function ($row) {
            return url('/evaluation/view-detail-assign-examiner/' . $row->examiner_pair_id . '/' . $row->examiner_id);
        }, false);
        // $crud->callbackAddForm(function ($data) {
        //     $count = ExaminerPair::all()->count();
        //     $data['code'] = 'EX_' . ($count + 1);
        
        //     return $data;
        // });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function assignReviewer(){
        $title = 'Phân công chấm PK';
        $cat = 'evaluation';
        $subcat = 'assign-reviewer';
        $table = _REVIEWER_PAIR;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            // ->where(['no_tests > ?' => 0])
            ->setSubject($title, $title)
            ->columns([
                'code',
                'subject_id',
                'no_tests',
                'examiners',
                'examinee_tests',
                'start_at',
                'finish_at',
                ])
            ->displayAs([
                'code' => 'Mã số',
                'subject_id' => 'Môn thi',
                'no_tests' => 'Số bài gán',
                'examiners' => 'Giám khảo',
                'examinee_tests' => 'Bài chấm',
                'start_at' => 'Ngày bắt đầu',
                'finish_at' => 'Ngày kết thúc',
            ])
            ->setPrimaryKey('examinee_test_code', 'examinee_test_mixes')
            ->setRelation('subject_id', 'subjects', 'desc', ['id' => 5])
            ->setRelationNtoN('examiners', 'reviewer_pair_details', 'monitors', 'reviewer_pair_id', 'reviewer_id', '{name}',null,['role_id' => 6])
            ->setRelationNtoN('examinee_tests', 'reviewer_pair_assignments', 'examinee_test_mixes', 'reviewer_pair_id', 'examinee_test_code', '{examinee_test_code}',null,['subject_id' => 5])
            ->addFields([
                'code', 
                'subject_id',
                // 'no_tests',
                'examiners',
                'examinee_tests',
                'start_at', 
                'finish_at'
            ])
            ->unsetDelete()
		    ->unsetPrint();

        $crud->setActionButton('Chi tiết phân công', 'fa fa-eye', function ($row) {
            return url('/evaluation/detail-assign-reviewer/' . $row->code);
        }, true);
        $crud->setActionButton('Bảng chênh lệch', 'fa fa-file-excel-o', function ($row) {
            return url('/export/xlsx/reviewer-pair-diff/' . $row->id);
        }, false);
        $crud->setActionButton('Bảng chi tiết', 'fa fa-file-excel-o', function ($row) {
            return url('/export/xlsx/reviewer-pair/' . $row->id);
        }, false);


        $crud->callbackColumn('no_tests', function ($value, $row) {
            $reviewer_pairs = ReviewerPairDetail::where('reviewer_pair_id',$row->id)->get();
            //lay so bai da cham cua cap cham
            $no_tests = $row->no_tests;
            $is_done = true;
            foreach($reviewer_pairs as $item){
                if($no_tests > $item->no_done_test){
                    $is_done = false;
                    break;
                }
            }
            
            if($is_done) $html = "<div class=\"bg-success text-white\">Xong </div><br>";
            else $html = "<div class=\"bg-danger text-white\">Chưa</div><br>";
            $html .= "<div class=\"gc-data-container-text\">" . $value . "</div>";
            return $html;
        });

        $crud->callbackAddForm(function ($data) {
            // $count = ExaminerPair::all()->count();
            $maxId = ReviewerPair::max('id');
            $data['code'] = 'PK_' . ($maxId + 1) . '_' . date('Y') . date('m') . date('d');
        
            return $data;
        });
        $crud->callbackAfterInsert(function ($stateParameters) {
            $data = $stateParameters->data;
            $reviewer_pair_assignments = ReviewerPairAssignment::where('reviewer_pair_id', $stateParameters->insertId)->get();
            ReviewerPair::where('id',$stateParameters->insertId)->update([
                'no_tests' => count($reviewer_pair_assignments)
            ]);
            //lấy thông tin 2 giám khảo PK trong cặp chấm
            $reviewers = ReviewerPairDetail::where('reviewer_pair_id', $stateParameters->insertId)->get();
            foreach($reviewers as $i => $reviewer){
                $reviewer->reviewer_role = ($i + 1);
                $reviewer->start_at = $data['start_at'];
                $reviewer->finish_at = $data['finish_at'];
                $reviewer->no_assigned_test = count($reviewer_pair_assignments);
                $reviewer->save();
            }
            foreach($reviewer_pair_assignments as $i => $item){
                $answer_key = AnswerKey::where('examinee_test_code',$item->examinee_test_code)
                                        ->first();
                if(!$answer_key) continue;
                foreach($reviewers as $reviewer){                    
                    ExaminerAssignment::create([
                        'examinee_test_uuid' => $answer_key['examinee_test_uuid'],
                        'examinee_answer_uuid' => $answer_key['examinee_answer_uuid'],
                        'examinee_test_code' => $answer_key['examinee_test_code'],
                        'subject_id' => $answer_key['subject_id'],
                        'answer_key_id' => 0,
                        'examiner_id' => $reviewer['reviewer_id'],
                        'examiner_pair_id' => $reviewer['reviewer_pair_id'],
                        'start_at' => $data['start_at'],
                        'finish_at' => $data['finish_at'],
                        'is_review' => true,
                    ]);
                }
            }
            
            return $stateParameters;
        });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'data' => 'summary-assign-examiner'
        ]);
    }

    public function detailAssignReviewer($code){
        $title = 'Phân công chấm thi';
        $cat = 'evaluation';
        $subcat = 'assign-reviewer';
        $table = _REVIEWER_PAIR_DETAIL;
        $examiner_pair = ReviewerPair::where('code',$code)->first();
        $examiner_pairs = ReviewerPairDetail::where('reviewer_pair_id',$examiner_pair->id)->get();
        foreach($examiner_pairs as $item){
            $no_done = ExaminerAssignment::where('examiner_pair_id',$item->reviewer_pair_id)->where('examiner_id',$item->reviewer_id)->where('is_done',1)->where('is_review',1)->count();
            $item->no_done_test = $no_done;
            $item->save();
        }
        
        $no_tests = array();
        for ($i=1;$i<=$examiner_pair->no_tests;$i++){
            $no_tests[$i] = $i;
        }
        // dd($no_tests);
        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['reviewer_pair_id' => $examiner_pair->id])
            ->setSubject($title, $title)
            ->columns([
                'reviewer_pair_id',
                'reviewer_id',
                'reviewer_role',
                'no_assigned_test',
                'no_done_test',
                'is_assigned',
                ])
            ->displayAs([
                'reviewer_pair_id' => 'Mã cặp chấm',
                'reviewer_id' => 'Họ tên',
                'reviewer_role' => 'Vai trò',
                'no_assigned_test' => 'Số bài gán',
                'no_done_test' => 'Số bài đã chấm',
                'is_assigned' => 'Trạng thái',
            ])
            ->fieldType('reviewer_role', 'dropdown_search',[
                1 => 'Giám khảo 1',
                2 => 'Giám khảo 2'
            ])
            ->fieldType('no_assigned_test', 'dropdown_search', $no_tests)
            ->setRelation('reviewer_pair_id', 'reviewer_pairs', 'code')
            ->setRelation('reviewer_id', 'monitors', '{name}')
			->editFields([
                'reviewer_id',
                'reviewer_role',
                // 'no_assigned_test'
            ])
			->unsetAdd()
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->setActionButton('Chi tiết', 'fa fa-eye', function ($row) {
            return url('/evaluation/view-detail-assign-reviewer/' . $row->reviewer_pair_id . '/' . $row->reviewer_id);
        }, false);
        // $crud->callbackAddForm(function ($data) {
        //     $count = ExaminerPair::all()->count();
        //     $data['code'] = 'EX_' . ($count + 1);
        
        //     return $data;
        // });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function viewDetailAssignReviewer($pairId,$examinerId){
        $title = 'Chi tiết bài thi được phân công';
        $cat = 'evaluation';
        $subcat = 'assign-reviewer';
        $table = _EXAMINER_ASSIGNMENT;
        // $examiner_pair = ExaminerPair::where('code',$code)->first();
        // $no_tests = array();
        // for ($i=1;$i<=$examiner_pair->no_tests;$i++){
        //     $no_tests[$i] = $i;
        // }
        
        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where([
                'examiner_pair_id' => $pairId,
                'examiner_id' => $examinerId,
                'is_review' => 1,
            ])
            ->setSubject($title, $title)
            ->columns([
                'examinee_test_code',
                'examiner_pair_id',
                'examiner_id',
                'subject_id',
                'start_at',
                'finish_at',
                'is_done',
                ])
            ->displayAs([
                'examiner_pair_id' => 'Mã cặp chấm',
                'examiner_id' => 'Họ tên',
                'examinee_test_code' => 'Mã phách',
                'subject_id' => 'Môn thi',
                'start_at' => 'Bắt đầu',
                'finish_at' => 'Kết thúc',
                'is_done' => 'Tiến độ',
            ])
            ->fieldType('is_done', 'dropdown_search',[
                1 => 'Đã chấm',
                0 => 'Chưa chấm'
            ])
            ->setRelation('examiner_pair_id', 'examiner_pairs', 'code')
            ->setRelation('examiner_id', 'monitors', '{name}')
            ->setRelation('subject_id', 'subjects', '{desc}')
			->unsetEdit()
			->unsetAdd()
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        // $crud->setActionButton('Chi tiết', 'fa fa-eye', function ($row) {
        //     return '/evaluation/view-detail-assign-examiner/' . $row->examiner_pair_id . '/' . $row->examiner_id;
        // }, false);
        // $crud->callbackAddForm(function ($data) {
        //     $count = ExaminerPair::all()->count();
        //     $data['code'] = 'EX_' . ($count + 1);
        
        //     return $data;
        // });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function examineeAnswer($code = ''){
        $title = 'Bài làm thí sinh';
        $cat = 'evaluation';
        $subcat = 'examinee-answer';
        $table = _ANSWER_KEY;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where([
                'answer_keys.subject_id' => 5,
                'answer_keys.matrix_location > ?' => 20,
            ])
            ->setSubject($title, $title)
            ->columns([
                'council_turn_code', 
                'examinee_test_code', 
                'examinee_answer', 
                'rubric_id', 
                'matrix_location', 
                // 'no_assigned_test', 
                // 'no_done_test', 
                // 'start_at', 
                // 'finish_at',
            ])
            ->displayAs([
                'council_turn_code' => 'HĐ - Ca',
                'examinee_test_code' => 'Mã phách',
                'examinee_answer' => 'Bài làm',
                'rubric_id' => 'Rubric',
                // 'no_done_test' => 'Đã chấm',
                // 'start_at' => 'Ngày BĐ',
                // 'finish_at' => 'Ngày KT',
            ])
            // ->defaultOrdering('examiner_id', 'asc')
            ->defaultOrdering('examinee_test_code', 'asc')
            // ->setRelation('rubric_id', 'rubrics', 'name')
            // ->setRelation('examiner_id', 'monitors', 'name')
			->setRead()
            ->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()
            ->unsetExport();

        $crud->callbackColumn('rubric_id', function ($value, $row) {
            $html = '';
            if($value == 1) $html = "<div>Đoạn</div><br>";
            if($value == 2) $html = "<div>Bài</div><br>";
            return $html;
        });
        // $crud->callbackColumn('examinee_answer', function ($value, $row) {
        //     return nl2br($value);
        // });
        // $crud->callbackColumn('examinee_answer', function ($value, $row) {
        //     // Chuyển toàn bộ \r\n hoặc \n thành chr(10) (ký tự xuống dòng Excel hiểu được)
        //     return str_replace(["\r\n", "\r", "\n"], chr(10), $value);
        // });
        $crud->setActionButton('Xuất file', 'fa fa-file-word-o', function ($row) {
            return '/export/docx/examiner-answer/' . $row->examinee_test_code . '/' . $row->matrix_location;
        }, false);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        // return $this->_example_output($output, $title, $cat, $subcat);

        // $form_url = '/exam/import-council';
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function councilAutoMarking(){
        $title = 'Chấm theo hội đồng thi';
        $cat = 'evaluation';
        $subcat = 'council-auto-marking';
        $table = _COUNCIL;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('councils.status', 1)
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'organization_code', 
                'code', 
                // 'desc', 
                'no_turns', 
                'start_at', 
                'finish_at', 
                // 'monitor_id', 
                // 'is_autostart', 
                'status'
            ])
            ->displayAs([
                'code' => 'Mã HĐ thi',
                'desc' => 'Diễn giải',
                'no_turns' => 'Số ca thi',
                'organization_code' => 'Điểm thi',
                'start_at' => 'Ngày bắt đầu',
                'finish_at' => 'Ngày kết thúc',
                'monitor_id' => 'Điểm trưởng',
                'is_autostart' => 'Cách khởi tạo',
                'import_testdata_before_time' => 'Cho phép nhập đề trước giờ thi (phút)',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('is_autostart', 'dropdown_search',[
                1 => 'Tự động',
                0 => 'Thủ công'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('no_turns', 'dropdown_search', [
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
                '10' => 10,
            ])
            ->fieldType('start_at','date')
            ->fieldType('finish_at','date')
            ->setPrimaryKey('code', 'organizations')
            ->setRelation('organization_code', 'organizations', '{code} - {name}')
            // ->setRelation('monitor_id', 'monitors', 'name', ['role_id' => 7])
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        // $crud->callbackColumn('no_turns', function ($value, $row) {
        //     return "
        //     <div class=\"gc-data-container-text\">Số lượng ca thi: " . $value . "</div><br>
        //     <div class=\"gc-data-container-text\">
        //         <div class=\"cl_manage_parts\">
        //             <a class=\"btn btn-outline-dark r20\" href=\"" . url('exam/council-turns/' . $row->code) . "\">Quản trị các ca thi</a>
        //         </div>
        //     </div>
        //     ";
        // });

        // $crud->callbackColumn('status', function ($value, $row) {
        //     //so bai thi
        //     $count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
        //     if($count_examinee_test_mixes) $html = "<div class=\"bg-success text-white\">Đã đồng bộ</div><br>";
        //     else $html = "<div class=\"bg-danger text-white\">Chưa đồng bộ</div><br>";
        //     return $html;
        // });

        $crud->setActionButton('Chấm thi HĐ', 'fa fa-gavel ', function ($row) {
            return url('/evaluation/auto-marking-all/' . $row->code);
        }, false);
        $crud->setActionButton('Chi tiết bài thi', 'fa fa-info-circle ', function ($row) {
            return url('/evaluation/examinee-tests/' . $row->code);
        }, false);
        // $crud->setActionButton('Xuất CBCT', 'fa fa-file-excel-o', function ($row) {
        //     return '/export/xlsx/monitor-council/' . $row->id;
        // }, true);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function examineeAutoMarking(){
        $title = 'Bài làm thí sinh';
        $cat = 'evaluation';
        $subcat = 'examinee-auto-marking';
        $table = _EXAMINEE_TEST_MIXES;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->setSubject($title, $title)
            ->columns([
                'examinee_test_uuid', 
                'examinee_test_code', 
                'examinee_code', 
                'examinee_account', 
                'subject_id', 
                'council_code', 
                'council_turn_code', 
                'room_code',
                'test_mix_id',
                'created_at',
                ])
            ->displayAs([
                'examinee_test_uuid' => 'Mã bài thi',
                'examinee_test_code' => 'Mã phách',
                'examinee_code' => 'SBD',
                'examinee_account' => 'Tài khoản thi',
                'subject_id' => 'Môn thi',
                'council_code' => 'HĐ thi',
                'council_turn_code' => 'Ca thi',
                'room_code' => 'Phòng thi',
                'test_mix_id' => 'Đề thi',
                'created_at' => 'Ngày đồng bộ'
            ])
            ->defaultOrdering('council_turn_code','asc')
            // ->defaultOrdering('examinee_test_code','asc')
            ->setRelation('subject_id', 'subjects', 'desc')
//            ->setRelation('test_mix_id', 'test_mixes', 'test_root_id')
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint();
//		->unsetExport();

	$crud->callbackColumn('test_mix_id', function ($value, $row) {
             //
             $test_mix = TestMix::find($value);
             if($test_mix) $html = "<div class=\"bg-success text-white\">" . $test_mix->test_root_id . "</div><br>";
             else $html = "<div class=\"bg-danger text-white\">" . $value . "</div><br>";
//             return $html;
		return $test_mix->test_root_id;
         });


        $crud->setActionButton('Chi tiết', 'fa fa-eye', function ($row) {
            return url('/evaluation/detail-examinee-answers/' . $row->examinee_test_code);
        }, true);
        $crud->setActionButtonMultiple('Chấm thi', 'fa fa-check', '/evaluation/auto-making-examinee-answers', false);
        $crud->setActionButton('Chấm thi', 'fa fa-check', function ($row) {
            return url('/evaluation/auto-making-examinee-answer/' . $row->id);
        }, false);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function examineeTests($code){
        $title = 'Quản lý bài thi';
        $cat = 'evaluation';
        $subcat = 'council-auto-marking';
        $table = _EXAMINEE_TEST_MIXES;
        // $form_url = '/evaluation/import-exam-data';

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['council_code' => $code])
            ->setSubject($title, $title)
            ->columns([
                'examinee_test_uuid', 
                'examinee_test_code', 
                'examinee_code', 
                'examinee_account', 
                'subject_id', 
                'council_code', 
                'council_turn_code', 
                'room_code',
                'test_mix_id',
                'created_at',
                ])
            ->displayAs([
                'examinee_test_uuid' => 'Mã bài thi',
                'examinee_test_code' => 'Mã phách',
                'examinee_code' => 'SBD',
                'examinee_account' => 'Tài khoản thi',
                'subject_id' => 'Môn thi',
                'council_code' => 'HĐ thi',
                'council_turn_code' => 'Ca thi',
                'room_code' => 'Phòng thi',
                'test_mix_id' => 'Đề thi',
                'created_at' => 'Ngày đồng bộ'
            ])
            ->setRelation('subject_id', 'subjects', 'desc')
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->setActionButton('Chi tiết', 'fa fa-eye', function ($row) {
            return url('/evaluation/detail-examinee-answers/' . $row->examinee_test_code);
        }, true);
        $crud->setActionButtonMultiple('Chấm thi', 'fa fa-check', '/evaluation/auto-making-examinee-answers', false);
        $crud->setActionButton('Chấm thi', 'fa fa-check', function ($row) {
            return url('/evaluation/auto-making-examinee-answer/' . $row->id);
        }, false);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    public function detailExamineeAnswers($code){
        $title = 'Chi tiết bài thi';
        $cat = 'evaluation';
        $subcat = 'council-auto-marking';
        $table = _ANSWER_KEY;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['examinee_test_code' => $code])
            ->setSubject($title, $title . ' - ' . $code)
            ->columns([
                'examinee_test_code',
                'subject_id',
                'question_type_id',
                'matrix_location',
                'examinee_answer',
                'answer_key',
                'is_correct',
                'score',
                'question_mark_id',
                'answer_type',
                'question_id',
                // 'rubric_id',
                // 'is_assigned',
                ])
            ->displayAs([
                'examinee_test_code' => 'Mã phách',
                'subject_id' => 'Môn thi',
                'question_id' => 'Mã câu hỏi',
                'question_type_id' => 'Loại',
                'matrix_location' => 'Vị trí',
                'examinee_answer' => 'Đán án thí sinh',
                'answer_key' => 'Đáp án gốc',
                'answer_type' => 'Loại đáp án',
                'question_mark_id' => 'Điểm gốc',
                'is_correct' => 'Đúng/Sai',
                'score' => 'Điểm thí sinh',
                'rubric_id' => 'Rubric',
                'is_assigned' => 'Gán chấm',
            ])
            ->defaultOrdering('matrix_location','asc')
            // ->setRead()
		->fieldType('examinee_answer','text')
            ->fieldType('is_correct', 'dropdown_search',[
                1 => 'ĐÚNG',
                0 => 'SAI'
            ])
            ->setRelation('subject_id', 'subjects', 'desc')
            ->setRelation('question_id', 'questions', 'code')
            ->setRelation('question_mark_id', 'question_marks', 'value')
            ->setRelation('question_type_id', 'question_types', 'code')
            ->setRelation('rubric_id', 'rubrics', 'name')
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->setActionButton('Xem câu hỏi', 'mif-eye', function ($row) {
            return url('qbank/preview/'. $row->question_id);
        }, true);

	$crud->setActionButton('Chấm đúng', 'mif-done', function ($row) {
            return url('evaluation/check-correct-examinee-answer/'. $row->id);
        }, false);

        $crud->setActionButton('Chấm sai', 'mif-cancel', function ($row) {
            return url('evaluation/check-incorrect-examinee-answer/'. $row->id);
        }, false);

        $crud->callbackColumn('is_correct', function ($value, $row) {
            if($value){
                return "<div class=\"bg-success text-white\">ĐÚNG</div>";
            }else{
                return "<div class=\"bg-danger text-white\">SAI</div>";
            }
        });

        $crud->setConfig('default_per_page', 50);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

	public function checkCorrectByExamineeAnswer($answerId = 0){
		$answer_key = AnswerKey::find($answerId);
		if($answer_key){
			$mark = QuestionMark::find($answer_key->question_mark_id);
			AnswerKey::where('id',$answerId)->update([
				'is_correct' => 1,
				'score' => $mark?floatval($mark->value):0
			]);
		}
		return redirect()->back();
	}

        public function checkIncorrectByExamineeAnswer($answerId = 0){
                $answer_key = AnswerKey::find($answerId);
                if($answer_key){
//                        $mark = QuestionMark::find($answer_key->question_mark_id);
                        AnswerKey::where('id',$answerId)->update([
                                'is_correct' => 0,
                                'score' => 0
                        ]);
                }
                return redirect()->back();
        }

    public function resultList(){
        $title = 'Kết quả thi';
        $cat = 'evaluation';
        $subcat = 'result-list';
        $table = _TURN;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('status', 1)
            ->setSubject($title, $title)
            ->columns([
                'council_code', 
                'code', 
                'name', 
                'no_rooms',
                'status'
            ])
            ->displayAs([
                'code' => 'Mã ca thi',
                'name' => 'Ca thi',
                'no_rooms' => 'Thống kê',
                'council_code' => 'Mã HĐ thi'
            ])
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('no_rooms', function ($value, $row) {
            $html = '';
            //so bai thi
            $count_examinee_test_mixes = AnswerKey::where('council_turn_code',$row->code)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_to = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',1)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_li = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',2)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_ho = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',3)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_si = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',4)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_va = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',5)->distinct('examinee_test_code')->count('examinee_test_code');
            
            // $html .= "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $_count_examinee_test_mixes . "</div><br>";
            $html .= "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $count_examinee_test_mixes . "</div><br>";
            if($count_examinee_test_mixes_to) $html .= "<div class=\"gc-data-container-text\">Toán: " . $count_examinee_test_mixes_to . "</div><br>";
            if($count_examinee_test_mixes_li) $html .= "<div class=\"gc-data-container-text\">Lí: " . $count_examinee_test_mixes_li . "</div><br>";
            if($count_examinee_test_mixes_ho) $html .= "<div class=\"gc-data-container-text\">Hoá: " . $count_examinee_test_mixes_ho . "</div><br>";
            if($count_examinee_test_mixes_si) $html .= "<div class=\"gc-data-container-text\">Sinh: " . $count_examinee_test_mixes_si . "</div><br>";
            if($count_examinee_test_mixes_va) $html .= "<div class=\"gc-data-container-text\">Văn: " . $count_examinee_test_mixes_va . "</div><br>";
            return $html;
        });
        $crud->callbackColumn('status', function ($value, $row) {
            //so bai thi
            $count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
            if($count_examinee_test_mixes) $html = "<div class=\"bg-success text-white\">Đã đồng bộ</div><br>";
            else $html = "<div class=\"bg-danger text-white\">Chưa đồng bộ</div><br>";
            return $html;
        });

        $crud->setActionButton('Xuất kết quả', 'fa fa-file-excel-o', function ($row) {
            return '/export/xlsx/export-result-list-by-turn/' . $row->code;
        }, false);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }
    
    public function resultListWithRubric(){
        $title = 'Kết quả thi kèm Rubric';
        $cat = 'evaluation';
        $subcat = 'result-list-with-rubric';
        $table = _COUNCIL;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('councils.status', 1)
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'organization_code', 
                'code', 
                // 'desc', 
                'no_turns', 
                'start_at', 
                'finish_at', 
                // 'monitor_id', 
                // 'is_autostart', 
                'status'
            ])
            ->displayAs([
                'code' => 'Mã HĐ thi',
                'desc' => 'Diễn giải',
                'no_turns' => 'Số ca thi',
                'organization_code' => 'Điểm thi',
                'start_at' => 'Ngày bắt đầu',
                'finish_at' => 'Ngày kết thúc',
                'monitor_id' => 'Điểm trưởng',
                'is_autostart' => 'Cách khởi tạo',
                'import_testdata_before_time' => 'Cho phép nhập đề trước giờ thi (phút)',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('is_autostart', 'dropdown_search',[
                1 => 'Tự động',
                0 => 'Thủ công'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('no_turns', 'dropdown_search', [
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
                '10' => 10,
            ])
            ->fieldType('start_at','date')
            ->fieldType('finish_at','date')
            ->setPrimaryKey('code', 'organizations')
            ->setRelation('organization_code', 'organizations', '{code} - {name}')
            // ->setRelation('monitor_id', 'monitors', 'name', ['role_id' => 7])
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('no_turns', function ($value, $row) {
            //so ca thi
            $html = "<div class=\"gc-data-container-text\">Số ca thi: " . $value . "</div><br>";
            //so bai thi
            // $_count_examinee_test_mixes = ExamineeTestMix::where('council_code',$row->code)->count();
            // $_count_examinee_test_mixes_to = ExamineeTestMix::where('council_code',$row->code)->where('subject_id',1)->count();
            // $_count_examinee_test_mixes_li = ExamineeTestMix::where('council_code',$row->code)->where('subject_id',2)->count();
            // $_count_examinee_test_mixes_ho = ExamineeTestMix::where('council_code',$row->code)->where('subject_id',3)->count();
            // $_count_examinee_test_mixes_si = ExamineeTestMix::where('council_code',$row->code)->where('subject_id',4)->count();
            // $_count_examinee_test_mixes_va = ExamineeTestMix::where('council_code',$row->code)->where('subject_id',5)->count();
            $count_examinee_test_mixes = AnswerKey::where('council_code',$row->code)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_to = AnswerKey::where('council_code',$row->code)->where('subject_id',1)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_li = AnswerKey::where('council_code',$row->code)->where('subject_id',2)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_ho = AnswerKey::where('council_code',$row->code)->where('subject_id',3)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_si = AnswerKey::where('council_code',$row->code)->where('subject_id',4)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_va = AnswerKey::where('council_code',$row->code)->where('subject_id',5)->distinct('examinee_test_code')->count('examinee_test_code');
            
            // $html .= "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $_count_examinee_test_mixes . "</div><br>";
            $html .= "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $count_examinee_test_mixes . "</div><br>";
            if($count_examinee_test_mixes_to) $html .= "<div class=\"gc-data-container-text\">Toán: " . $count_examinee_test_mixes_to . "</div><br>";
            if($count_examinee_test_mixes_li) $html .= "<div class=\"gc-data-container-text\">Lí: " . $count_examinee_test_mixes_li . "</div><br>";
            if($count_examinee_test_mixes_ho) $html .= "<div class=\"gc-data-container-text\">Hoá: " . $count_examinee_test_mixes_ho . "</div><br>";
            if($count_examinee_test_mixes_si) $html .= "<div class=\"gc-data-container-text\">Sinh: " . $count_examinee_test_mixes_si . "</div><br>";
            if($count_examinee_test_mixes_va) $html .= "<div class=\"gc-data-container-text\">Văn: " . $count_examinee_test_mixes_va . "</div><br>";
            
            return $html;
        });

        // $crud->callbackColumn('status', function ($value, $row) {
        //     //so bai thi
        //     $count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
        //     if($count_examinee_test_mixes) $html = "<div class=\"bg-success text-white\">Đã đồng bộ</div><br>";
        //     else $html = "<div class=\"bg-danger text-white\">Chưa đồng bộ</div><br>";
        //     return $html;
        // });

        // $crud->setActionButton('Xuất kết quả', 'fa fa-cloud-upload', function ($row) {
        //     return '/export/xlsx/export-result-list/' . $row->id;
        // }, false);
        $crud->setActionButton('Xuất kết quả', 'fa fa-file-excel-o', function ($row) {
            return '/export/xlsx/export-result-list-with-rubric/' . $row->id;
        }, false);
        // $crud->setActionButton('Xuất CBCT', 'fa fa-file-excel-o', function ($row) {
        //     return '/export/xlsx/monitor-council/' . $row->id;
        // }, true);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }    

    public function manageExamData(){
        $title = 'Dữ liệu bài thi';
        $cat = 'evaluation';
        $subcat = 'manage-exam-data';
        $table = _TURN;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('status', 1)
            ->setSubject($title, $title)
            ->columns([
                'council_code', 
                'code', 
                'name', 
                'no_rooms',
                'status'
            ])
            ->displayAs([
                'code' => 'Mã ca thi',
                'name' => 'Ca thi',
                'no_rooms' => 'Thống kê',
                'council_code' => 'Mã HĐ thi'
            ])
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('no_rooms', function ($value, $row) {
            $html = '';
            //so bai thi
            $count_examinee_test_mixes = AnswerKey::where('council_turn_code',$row->code)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_to = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',1)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_li = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',2)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_ho = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',3)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_si = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',4)->distinct('examinee_test_code')->count('examinee_test_code');
            $count_examinee_test_mixes_va = AnswerKey::where('council_turn_code',$row->code)->where('subject_id',5)->distinct('examinee_test_code')->count('examinee_test_code');
            
            // $html .= "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $_count_examinee_test_mixes . "</div><br>";
            $html .= "<div class=\"gc-data-container-text\">Số lượng bài thi: " . $count_examinee_test_mixes . "</div><br>";
            if($count_examinee_test_mixes_to) $html .= "<div class=\"gc-data-container-text\">Toán: " . $count_examinee_test_mixes_to . "</div><br>";
            if($count_examinee_test_mixes_li) $html .= "<div class=\"gc-data-container-text\">Lí: " . $count_examinee_test_mixes_li . "</div><br>";
            if($count_examinee_test_mixes_ho) $html .= "<div class=\"gc-data-container-text\">Hoá: " . $count_examinee_test_mixes_ho . "</div><br>";
            if($count_examinee_test_mixes_si) $html .= "<div class=\"gc-data-container-text\">Sinh: " . $count_examinee_test_mixes_si . "</div><br>";
            if($count_examinee_test_mixes_va) $html .= "<div class=\"gc-data-container-text\">Văn: " . $count_examinee_test_mixes_va . "</div><br>";
            return $html;
        });
        $crud->callbackColumn('status', function ($value, $row) {
            //so bai thi
            $count_examinee_test_mixes = ExamineeTestMix::where('council_turn_code',$row->code)->count();
            if($count_examinee_test_mixes) $html = "<div class=\"bg-success text-white\">Đã đồng bộ</div><br>";
            else $html = "<div class=\"bg-danger text-white\">Chưa đồng bộ</div><br>";
            return $html;
        });

        $crud->setActionButton('Xuất đáp án thí sinh', 'fa fa-file-excel-o', function ($row) {
            return '/export/xlsx/export-detail-examinee-answers-by-turn/' . $row->code;
        }, false);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        // return $this->_example_output($output, $title, $cat, $subcat);

        // $form_url = '/exam/import-council';
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

/* auto marking functions */
    public function autoMarkingByExamineeTests(Request $request){
        $ids = $request->input('id');
        foreach($ids as $id){
            $this->autoMarking($id);
        }
        return redirect()->back();
    }
    public function autoMarkingByExamineeTest($id){
//dd($id);
        $this->autoMarking($id);
        return redirect()->back();
    }
    public function autoMarkingByCouncil($code){
        $council = Council::where('code',$code)->first();
        // dd($council);
        if($council){
            $examinee_test_mixes = ExamineeTestMix::where('council_code',$code)->get();
            // dd($examinee_test_mixes);
            // $ids = $request->input('id');
            foreach($examinee_test_mixes as $item){
                $this->autoMarking($item->id);
            }
        }
        return redirect()->back();
    }

    private function autoMarking($examineeTestId){
//dd($examineeTestId);
        $examinee_test_mix = ExamineeTestMix::find($examineeTestId);
        if(!$examinee_test_mix) return false;
        $examinee_test_code = $examinee_test_mix->examinee_test_code;
        $answer_keys = AnswerKey::where('examinee_test_code',$examinee_test_code)->orderBy('matrix_location','asc')->get();
        foreach($answer_keys as $item){
            if(!$item->examinee_answer) continue;
            $question_mark = QuestionMark::find($item->question_mark_id);
            $is_correct = false;
            $score = 0.0;
            switch($item->question_type_id){
                case 1: //1 PA đúng
                    //so trùng đáp án
                    if($item->examinee_answer == $item->answer_key){
                        $is_correct = true;
                        if($is_correct) $score = floatval($question_mark->value);
                    }
                    break;
                case 2: //nhiều PA đúng
                    $_answer_key_set = array_keys(array_flip(explode(',',$item->answer_key)));
                    // dd($_answer_key_set);
                    $_examiner_answer_set = array_keys(array_flip(explode(',',$item->examinee_answer)));
                    // dd(count($_answer_key_set) == count($_examiner_answer_set));
                    if(count($_answer_key_set) == count($_examiner_answer_set)){
                        $is_correct = true;
                        foreach($_examiner_answer_set as $_item){
                            if(!in_array($_item, $_answer_key_set)){
                                $is_correct = false;
                                $score = 0;
                                break;
                            }
                        }
                        // $is_correct = true;
                        if($is_correct) $score = floatval($question_mark->value);
                    }else{
                        // dd($_examiner_answer_set);
                        $is_correct = true;
                        foreach($_examiner_answer_set as $_item){
                            if(!in_array($_item, $_answer_key_set)){
                                $is_correct = false;
                                $score = 0;
                                break;
                            }
                        }
                        if($is_correct) $score = floatval($question_mark->value) / 2;
                    }
                    break;
                case 4: //TLN
			// Bước 1: Xóa khoảng trắng đầu và cuối
			$trimmed = trim($item->examinee_answer);
			// Bước 2: Thay thế nhiều khoảng trắng liên tiếp bằng 1 khoảng trắng
			$cleaned = preg_replace('/\s+/', ' ', $trimmed);
			$item->examinee_answer = $cleaned;
                    switch($item->answer_type){
                        case 'INTEGER_NUMBER': 
                            if(strval($item->examinee_answer) == strval($item->answer_key)){
                                $is_correct = true;
                                if($is_correct) $score = floatval($question_mark->value);
                            }
                            break;
                        case 'DOUBLE_NUMBER': 
                            $newStr = str_replace(".", ",", strval($item->examinee_answer));
                            if(strval($newStr) == strval($item->answer_key)){
                                $is_correct = true;
                                if($is_correct) $score = floatval($question_mark->value);
                            }
                            break;
                        case 'ORDER_LIST': 
                            //loại bỏ các kí tự không phải là số
                            $_answer_key = preg_replace('/\D/', '', $item->answer_key);
                            $_examinee_answer = preg_replace('/\D/', '', $item->examinee_answer);
                            if(strval($_examinee_answer) == strval($_answer_key)){
                                $is_correct = true;
                                if($is_correct) $score = floatval($question_mark->value);
                            }
                            break;
                        case 'UNORDER_LIST': 
                            //loại bỏ các kí tự không phải là số
                            $_answer_key = preg_replace('/\D/', '', $item->answer_key);
                            $_examinee_answer = preg_replace('/\D/', '', $item->examinee_answer);
                            //chuyển các kí tự trong chuỗi thành mảng
                            $_examinee_answer = str_split($_examinee_answer);
                            $_answer_key = str_split($_answer_key);
                            if(count($_answer_key) == count($_examinee_answer)){
                                $is_correct = true;
                                foreach($_examinee_answer as $_item){
                                    if(!in_array($_item, $_answer_key)){
                                        $is_correct = false;
                                        $score = 0;
                                        break;
                                    }
                                }
                                if($is_correct) $score = floatval($question_mark->value);
                            }
                            break;
                        default: 
                            if(strval($item->examinee_answer) == strval($item->answer_key)){
                                $is_correct = true;
                                if($is_correct) $score = floatval($question_mark->value);
                            }
                            break;
                    }
                    if(strval($item->examinee_answer) == strval($item->answer_key)){
                        $is_correct = true;
                        if($is_correct) $score = floatval($question_mark->value);
                    }
                    break;
                case 5: //LNN
                    // tổng hợp điểm từ rubrics
                    //dd($item->score);
                    if(true || !$item->score){
                        $rubric_criteria = RubricCriteria::where('rubric_id',$item->rubric_id)->get();
                        $pair = ExaminerAssignment::where('examinee_test_code',$item->examinee_test_code)->first();
                        $examiner1_pair = ExaminerPairDetail::where('examiner_pair_id',$pair->examiner_pair_id)->where('examiner_role',1)->first();
                        $examiner1 = Monitor::find($examiner1_pair->examiner_id);
                        $examiner2_pair = ExaminerPairDetail::where('examiner_pair_id',$pair->examiner_pair_id)->where('examiner_role',2)->first();
                        $examiner2 = Monitor::find($examiner2_pair->examiner_id);
                        $_score1 = 0;
			$_score11 = 0;
			$_score21 = 0;
                        foreach($rubric_criteria as $rubric){
                            $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner1->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
                            $_score11 += floatval($_detail?$_detail->score:0);
                            $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
                            $_score21 += floatval($_detail?$_detail->score:0);
                        }
//dd($_score11);
			$_score1 = floatval($_score11 + $_score21) / 2;

                        $_score2 = 0;
                        $_score12 = 0;
                        $_score22 = 0;
                        foreach($rubric_criteria as $rubric){
                            $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner1->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
                            $_score12 += floatval($_detail?$_detail->score:0);
                            $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
                            $_score22 += floatval($_detail?$_detail->score:0);
                        }
                        $_score2 = floatval($_score12 + $_score22) / 2;
//dd($_score2);
                        $is_correct = true;
                        $score = floatval($_score1 + $_score2) / 2;
                    }else{
                        $is_correct = true;
                        $score = floatval($item->score);
                    }
                    break;
            }
            $item->is_correct = $is_correct;
            $item->score = $score;
            $item->save();
        }
    }
/* end auto marking functions  */

/* count diff */
private function countDiff($examiner_pair_id){
    $lech21 = $lech31 = 0;
    $lech22 = $lech32 = 0;
    $lech31_arr = [];
    $lech32_arr = [];
    // dd($examiner_pair_id);
    // $examiner_pair = ExaminerPair::find($examiner_pair_id);
    $examiner1_pair = ExaminerPairDetail::where('examiner_pair_id',$examiner_pair_id)->where('examiner_role',1)->first();
    $examiner1 = Monitor::find($examiner1_pair->examiner_id);
    $examiner2_pair = ExaminerPairDetail::where('examiner_pair_id',$examiner_pair_id)->where('examiner_role',2)->first();
    $examiner2 = Monitor::find($examiner2_pair->examiner_id);
    
    $examine_tests = DB::table('examiner_assignments')
                ->select('examinee_test_code')
                ->distinct()
                ->where('examiner_pair_id',$examiner_pair_id)
                ->where('examiner_id',$examiner1->id)
                ->get();
    // dd($examine_tests);
    $num = 0;
    foreach($examine_tests as $item){
        // $examinee_test_mix = ExamineeTestMix::where('examinee_test_code',$item->examinee_test_code)->first();
        
        $examinee_answer21 = AnswerKey::where('examinee_test_code',$item->examinee_test_code)->where('matrix_location',21)->first();
        // dd($examinee_answer21);
        $rubric_criteria_21 = $examinee_answer21?(RubricCriteria::where('rubric_id',$examinee_answer21->rubric_id)->get()):[];
        $_total_score121 = 0;
        // $detail121 = [];
        foreach($rubric_criteria_21 as $rubric){
            $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner1->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
            $_total_score121 += floatval($_detail?$_detail->score:0);
            // $detail121[] = floatval($_detail?$_detail->score:0);
        }
        // dd($_total_score121);
        $_total_score221 = 0;
        // $detail221 = [];
        foreach($rubric_criteria_21 as $rubric){
            $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
            $_total_score221 += floatval($_detail?$_detail->score:0);
            // $detail221[] = floatval($_detail?$_detail->score:0);
        }

        $examinee_answer22 = AnswerKey::where('examinee_test_code',$item->examinee_test_code)->where('matrix_location',22)->first();
        $rubric_criteria_22 = $examinee_answer22?(RubricCriteria::where('rubric_id',$examinee_answer22->rubric_id)->get()):[];
        $_total_score122 = 0;
        // $detail122 = [];
        foreach($rubric_criteria_22 as $rubric){
            $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner1->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
            $_total_score122 += floatval($_detail?$_detail->score:0);
            // $detail122[] = floatval($_detail?$_detail->score:0);
        }
        $_total_score222 = 0;
        // $detail222 = [];
        foreach($rubric_criteria_22 as $rubric){
            $_detail = ExaminerRubricDetail::where('rubric_criteria_id',$rubric->id)->where('examiner_id',$examiner2->id)->where('examinee_test_code',$item->examinee_test_code)->orderBy('id','desc')->first();
            $_total_score222 += floatval($_detail?$_detail->score:0);
            // $detail222[] = floatval($_detail?$_detail->score:0);
        }
        // dd($_total_score222);
        $diff_score21 = abs($_total_score121 - $_total_score221)?abs($_total_score121 - $_total_score221):'0';
        $diff_score22 = abs($_total_score122 - $_total_score222)?abs($_total_score122 - $_total_score222):'0';
        if((floatval($diff_score21) > 1 && floatval($diff_score21) <2)) {
            // if(!$lech21) $lech21 = true;
            $lech21++;
        }
        if((floatval($diff_score22) > 1 && floatval($diff_score22) <2)) {
            // if(!$lech22) $lech22 = true;
            $lech22++;
        }
        if((floatval($diff_score21) >= 2)) {
            // if(!$lech31) $lech31 = true;
            $lech31++;
            $lech31_arr[] = $item->examinee_test_code;
        }
        if((floatval($diff_score22) >= 2)) {
            // if(!$lech32) $lech32 = true;
            $lech32++;
            $lech32_arr[] = $item->examinee_test_code;
        }
        // if($lech2 && $lech3) return array($lech2, $lech3);
    }
    return array($lech21, $lech22, $lech31, $lech32, $lech31_arr, $lech32_arr);
}
/* end count diff*/

/* export functions */

    private function formatTextWithBreaks($text) {
        // Escape các ký tự XML đặc biệt
        $text = htmlspecialchars($text);
    
        // Xử lý khoảng trắng liên tiếp (chỉ từ 2 dấu cách trở lên)
        $text = preg_replace_callback('/ {2,}/', function ($matches) {
            $len = strlen($matches[0]);
            return str_repeat(' ', 1) . str_repeat(' ', $len - 1);
        }, $text);
    
        // Xử lý xuống dòng: chèn tag <w:br/> đúng cách trong XML Word
        $text = str_replace("\n", '</w:t><w:br/><w:t>', $text);
    
        // Bọc toàn bộ lại cho đúng cấu trúc XML Word fragment
        return "<w:t>{$text}</w:t>";
    }

    public function exportExamineeAnswer2Docx(Request $request, $code, $type){
        $examinee_test_code = $code;
        $examinee_answer21 = AnswerKey::where('examinee_test_code',$examinee_test_code)->where('subject_id',5)->where('matrix_location',21)->first();
        $answer21 = $this->formatTextWithBreaks($examinee_answer21->examinee_answer);
        $examinee_answer22 = AnswerKey::where('examinee_test_code',$examinee_test_code)->where('subject_id',5)->where('matrix_location',22)->first();
        $answer22 = $this->formatTextWithBreaks($examinee_answer22->examinee_answer);

        $data= [
            'code' => $examinee_test_code,
            'answer' => ($type == 21)?$answer21:$answer22,
            // 'answer22' => $answer22,
            'type' => ($type == 21)?'ĐOẠN':'BÀI',
            'dd' => date('d', time()),
            'mm' => date('m', time()),
            'yy' => date('y', time()),
        ];

        $filename = $examinee_test_code . '- Nội dung viết ' . (($type == 21)?'ĐOẠN':'BÀI') . ' môn Ngữ Văn.docx';
        // $filepath = Storage::path('export/docx/' . $filename);
        // $filepath = ('storage/export/docx/' . $filename);
        PhpWordService::createFromTemplateNoBLock($data,'assets/word-template/2025_Answer_template.docx',$filename);
        // return response()->download($filepath)->deleteFileAfterSend(true);
        return Storage::download('export/docx/' . $filename);
    }

    public function exportExaminerRubricDetail($pairId){
        $pair= ExaminerPair::find($pairId);
        return Excel::download(new ExaminerRubricDetailExport($pairId), 'Dữ liệu chi tiết cặp chấm '. $pair->code . '.xlsx');
    }
    public function exportReviewerRubricDetail($pairId){
        $pair= ReviewerPair::find($pairId);
        return Excel::download(new ReviewerRubricDetailExport($pairId), 'Dữ liệu chi tiết cặp chấm '. $pair->code . '.xlsx');
    }

    public function exportAllExaminerRubricDetail(){
        return Excel::download(new AllRubricDetailExport(), 'Dữ liệu chi tiết cặp chấm.xlsx');
    }

    public function exportExaminerRubricDetailDiff($pairId){
        $pair= ExaminerPair::find($pairId);
        return Excel::download(new ExaminerRubricDetailDiffExport($pairId), 'Dữ liệu điểm chênh lệch cặp chấm '. $pair->code . '.xlsx');
    }

    public function exportReviewerRubricDetailDiff($pairId){
        $pair= ReviewerPair::find($pairId);
        return Excel::download(new ReviewerRubricDetailDiffExport($pairId), 'Dữ liệu điểm chênh lệch cặp chấm '. $pair->code . '.xlsx');
    }

    public function exportSummaryExaminerPair(){
        return Excel::download(new ExaminerPairExport(), 'Dữ liệu chi tiết phân công.xlsx');
    }

    public function exportResultList($councilId){
        $council = Council::find($councilId);
        return Excel::download(new ExamineeResultsExport($councilId), 'Kết quả thi - HĐ thi ' . $council->code . '.xlsx');
    }

    public function exportResultListByTurn($code){
        return Excel::download(new ExamineeResultsByTurnExport($code), 'Kết quả thi - Ca thi ' . $code . '.xlsx');
    }

    public function exportResultListWithRubric($councilId){
        $council = Council::find($councilId);
        return Excel::download(new ExamineeResultsWithRubricExport($councilId), 'Chi tiết điểm bài thi Ngữ văn - HĐ thi ' . $council->code . '.xlsx');
    }

    public function exportDetailExamineeAnswers($councilId){
        $council = Council::find($councilId);
        return Excel::download(new DetailExamineeAnswersExport($councilId), 'Chi tiết bài làm thí sinh - HĐ thi ' . $council->code . '.xlsx');
    }
    public function exportDetailExamineeAnswersByTurn($code){
        // $council_turn = CouncilTurn::where($councilId);
        return Excel::download(new DetailExamineeAnswersByTurnExport($code), 'Chi tiết bài làm thí sinh - Ca thi ' . $code . '.xlsx');
    }
/* end export functions */
}
