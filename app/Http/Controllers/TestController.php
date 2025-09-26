<?php

namespace App\Http\Controllers;

use App\Models\CouncilTurn;
use App\Models\User;
use App\Models\Difficult;
use App\Models\Grade;
use App\Models\Question;
use App\Models\QuestionMark;
use App\Models\QuestionStore;
use App\Models\QuestionType;
use App\Models\Subject;
use App\Models\Taxonomy;
use App\Models\Topic;

use App\Models\TestForm;
use App\Models\TestPart;
use App\Models\TestFormPart;
use App\Models\TestGroup;
use App\Models\TestGroupSubject;
use App\Models\Test;
use App\Models\TestRoot;
use App\Models\TestMix;
use App\Models\CouncilTurnTestMix;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use GroceryCrud\Core\GroceryCrud;

/* DB table name */
define('_USER','users');
define('_ROLE','roles');
define('_DIFFCULT','difficults');
define('_GRADE','grades');
define('_SUBJECT','subjects');
define('_TAXONOMY','taxonomies');
define('_TOPIC','topics');
define('_QUESTION','questions');
define('_QMARK','question_marks');
define('_QTYPE','question_types');
define('_QSTORE','question_stores');
define('_QOPTION','question_options');
define('_TPART','test_parts');
define('_TFORM','test_forms');
define('_TFORMPART','test_form_parts');
define('_TGROUP','test_groups');
define('_TEST','tests');
define('_TROOT','test_roots');
define('_TMIX','test_mixes');

class TestController extends Controller
{
    //
    private $database;
    private $config;
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
    private function _example_output($output = null, $title = '', $cat = '', $subcat = '', $back_url = '') {
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
        return view('pages.qbank_crud_template', [
            'title' => $title,
            'cat' => $cat,
            'sub_cat' => $subcat,
            'back_url' => $back_url,
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
	}

    public function index()
    {
        $title = 'Quản lý dữ liệu đề thi';
        $cat = $subcat = '';
        $output = (object)[
            'css_files' => [],
            'js_files' => [],
            'output' => '<div class="bg-red fg-white p-2">Vui lòng chọn chức năng.</div>'
        ];
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * Test part management
     */
    public function testPart()
    {
        $title = 'Phần thi';
        $cat = 'test';
        $subcat = 'tpart';
        $table = _TPART;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['name', 'desc', 'caltype', 'is_shuffled', 'status'])
            ->fields(['name', 'desc', 'part_title', 'caltype', 'is_shuffled', 'status'])
            ->requiredFields(['name', 'caltype', 'status'])
            ->setRead()
            ->displayAs([
                'name' => 'Tên',
                'part_title' => 'Tiêu đề',
                'desc' => 'Diễn giải',
                'caltype' => 'Cách chấm',
                'is_shuffled' => 'Đảo câu hỏi',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->setTexteditor([
                'part_title'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('is_shuffled', 'dropdown_search',[
                1 => 'Đảo',
                0 => 'Không'
            ])
			->unsetReadFields(['deleted_at'])
			->unsetAddFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();

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
        $crud->callbackAfterUpdate(function ($stateParameters) {
            $test_form_parts = TestFormPart::where('test_part_id',$stateParameters->primaryKeyValue)->get();
            
            foreach($test_form_parts as $item){
                $item->desc = $stateParameters->data['name'] . ' ' . $stateParameters->data['desc'];
                $item->save();
            }
            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
            // Custom error messages are only available on Grocery CRUD Enterprise
            DB::table($table)
                ->where('id', $stateParameters->primaryKeyValue)
                ->update(['status' => 0]);
            DB::table($table)
                ->where('id', $stateParameters->primaryKeyValue)
                ->delete();
            return $stateParameters;
        });
        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * Test form management
     */
    public function testForm()
    {
        $title = 'Dạng đề thi';
        $cat = 'test';
        $subcat = 'tform';
        $table = _TFORM;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['name', 'status'])
            ->fields(['code', 'name', 'time', 'parts', 'status'])
            ->setRelationNtoN('parts', 'test_form_parts', 'test_parts', 'test_form_id', 'test_part_id', 'name',null,['status' => 1])
            ->requiredFields(['name', 'time'])
            ->setRead()
            ->displayAs([
                'code' => 'Mã',
                'name' => 'Mô tả',
                'time' => 'T/gian làm bài',
                'no_questions' => 'Số câu hỏi',
                'no_parts' => 'Số phần thi',
                'parts' => 'Các phần thi',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->fieldType('status', 'dropdown_search',[
                '1' => 'Sử dụng',
                '0' => 'Ẩn'
            ])
			->unsetReadFields(['deleted_at'])
			->unsetAddFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();

            $crud->callbackAddForm(function ($data) {
                $data['status'] = 1;
                return $data;
            });

        $crud->callbackColumn('name', function ($value, $row) {
            $test_form = TestForm::find($row->id);
            $no_questions = $test_form->no_questions;
            $no_parts = $test_form->no_parts;
            $time = $test_form->time;

            return "
            <div class=\"gc-data-container-text\"><b>" . $value . "</b></div>
            <div class=\"gc-data-container-text\">Số câu hỏi: " . $no_questions . "</div>
            <div class=\"gc-data-container-text\">Số phần thi: " . $no_parts . "</div>
            <div class=\"gc-data-container-text\">Thời gian: " . $time . "</div><br>
            <div class=\"gc-data-container-text\">
                <div class=\"cl_manage_parts\">
                    <a class=\"btn btn-outline-dark r20\" href=\"" . url('test/formpart/' . $row->id) . "\"> Quản trị các phần thi</a>
                </div>
            </div>
            ";
        });

        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackAfterInsert(function ($stateParameters) {
            $test_form_parts = TestFormPart::where('test_form_id',$stateParameters->insertId)->get();
            TestForm::where('id',$stateParameters->insertId)->update([
                'no_parts' => count($test_form_parts)
            ]);
            foreach($test_form_parts as $item){
                $test_part = TestPart::find($item->test_part_id);
                $item->desc = $test_part->name . ' ' . $test_part->desc;
                $item->save();
            }
            return $stateParameters;
        });
        $crud->callbackAfterUpdate(function ($stateParameters) {
            $test_form_parts = TestFormPart::where('test_form_id',$stateParameters->primaryKeyValue)->get();
            TestForm::where('id',$stateParameters->primaryKeyValue)->update([
                'no_parts' => count($test_form_parts)
            ]);
            foreach($test_form_parts as $item){
                $test_part = TestPart::find($item->test_part_id);
                $item->desc = $test_part->name . ' ' . $test_part->desc;
                $item->save();
            }
            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
            // Custom error messages are only available on Grocery CRUD Enterprise
            DB::table($table)
                ->where('id', $stateParameters->primaryKeyValue)
                ->update(['status' => 0]);
            DB::table($table)
                ->where('id', $stateParameters->primaryKeyValue)
                ->delete();
            return $stateParameters;
        });
        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * Test form part management
     */
    public function testFormPart($form_id = 0)
    {
        $title = 'Chi tiết dạng đề thi';
        $cat = 'test';
        $subcat = 'tform';
        $table = _TFORMPART;

        $multi_select_50 = array();
		for ($i=1;$i<=50;$i++){
			$multi_select_50[$i] = $i;
		}
        $multi_select_10 = array();
        for ($i=1;$i<=10;$i++){
            $multi_select_10[$i] = $i;
        }

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where([
                'test_form_id' => $form_id
            ])
            ->setSubject($title, 'Quản lý ' . $title)
            // ->columns(['test_form_id', 'test_part_id', 'question_type_id', 'order', 'no_questions', 'list_questions'])
            ->columns(['test_part_id', 'desc', 'order', 'no_questions', 'list_questions','test_form_id'])
            ->defaultOrdering('order', 'asc')
            ->setRelation('test_form_id', 'test_forms', 'name')
            ->setRelation('test_part_id', 'test_parts', 'name')
            // ->setRelation('question_type_id', 'question_types', '{code}.{name}')
            // ->fields(['test_form_id', 'test_part_id', 'question_type_id', 'order', 'no_questions', 'list_questions'])
            ->fields(['test_form_id', 'test_part_id', 'order', 'no_questions', 'list_questions'])
            // ->requiredFields(['question_type_id', 'order', 'no_questions', 'list_questions'])
            ->requiredFields(['order', 'no_questions', 'list_questions'])
            ->setRead()
            ->displayAs([
                'desc' => 'Mô tả',
                'test_form_id' => 'Dạng đề',
                'test_part_id' => 'Phần thi',
                // 'question_type_id' => 'Loại câu hỏi',
                'order' => 'Thứ tự',
                'no_questions' => 'Số câu hỏi',
                'list_questions' => 'Ma trận câu hỏi'
            ])
            ->fieldType('list_questions', 'multiselect_searchable', $multi_select_50)
            ->fieldType('order', 'dropdown_search', $multi_select_10)
            ->fieldType('status', 'dropdown_search',[
                '1' => 'Sử dụng',
                '0' => 'Ẩn'
            ])
			->unsetReadFields(['deleted_at'])
			->unsetAdd()
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackAfterUpdate(function ($stateParameters) {
            $test_form_parts = TestFormPart::where('test_form_id',$stateParameters->data['test_form_id'])->get();
            $no_questions = 0;
            foreach($test_form_parts as $item){
                $no_questions += $item->no_questions;
            }
            TestForm::where('id',$stateParameters->data['test_form_id'])->update([
                'no_questions' => $no_questions
            ]);
            return $stateParameters;
        });
        
        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * test group management
     */
    public function testGroup()
    {
        $title = 'Bộ đề thi';
        $cat = 'test';
        $subcat = 'tgroup';
        $table = _TGROUP;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('test_groups.deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'desc','subjects', 'password', 'packaged_at', 'is_used'])
            ->fields(['code', 'desc','subjects', 'password'])
            ->setRelationNtoN('subjects', 'test_group_subjects', 'subjects', 'test_group_id', 'subject_id', 'desc','code_number',['status' => 1])
            // ->setRead()
            ->displayAs([
                'code' => 'Mã bộ đề',
                'desc' => 'Chi tiết',
                'subjects' => 'Các môn thi',
                'password' => 'Mk mở đề',
                'packaged_at' => 'Ngày xuất đề',
                'is_used' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
			->unsetReadFields(['deleted_at'])
			->unsetAddFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();
            
        $crud->setActionButton('Tải bộ đề', 'fa fa-download', function ($row) {
            return '/test/download/' . $row->id;
        }, true);
        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            return $data;
        });

        $crud->callbackColumn('desc', function ($value, $row) {
            $test_group = TestGroup::find($row->id);

            return "
            <div class=\"gc-data-container-text\">Số lượng môn thi: " . $test_group->no_subjects . "</div><br>
            <div class=\"gc-data-container-text\">
                <div class=\"cl_manage_parts\">
                    <div class=\"cl_manage_parts\">
                        <a class=\"btn btn-outline-dark r20\" href=\"" . url('test/tgroup-detail/' . $row->id) . "\"> Quản trị các đề thi</a>
                    </div>
                </div>
            </div>
            ";
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackAfterInsert(function ($stateParameters) {
            $id = $stateParameters->insertId;
            $data = $stateParameters->data;
            $test_group_subjects = TestGroupSubject::where('test_group_id',$id)->get();
            TestGroup::where('id',$id)->update([
                'no_subjects' => count($test_group_subjects)
            ]);
            foreach($test_group_subjects as $i => $item){
                $subject = Subject::find($item->subject_id);
                Test::create([
                    'code' => $data['code'] . "#" . ($i + 1),
                    'name' => "Đề thi môn " . $subject->name,
                    'desc' => "",
                    'test_group_id' => $id,
                    'test_root_numbers' => 0,
                    'test_mix_numbers' => 0,
                    'test_form_id' => null,
                    'subject_id' => $subject->id
                ]);
            }
            return $stateParameters;
        });
        $crud->callbackAfterUpdate(function ($stateParameters) {
            $id = $stateParameters->primaryKeyValue;
            $data = $stateParameters->data;
            $test_group_subjects = TestGroupSubject::where('test_group_id',$id)->get();
            TestGroup::where('id',$id)->update([
                'no_subjects' => count($test_group_subjects)
            ]);
            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
            DB::table($table)
                ->where('id', $stateParameters->primaryKeyValue)
                ->update(['status' => 0]);
            DB::table($table)
                ->where('id', $stateParameters->primaryKeyValue)
                ->delete();
            return $stateParameters;
        });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * Test group detail management
     */
    public function testGroupDetail($group_id = 0)
    {
        $test_group = TestGroup::find($group_id);
        $title = 'Chi tiết ' . $test_group->name;
        $cat = 'test';
        $subcat = 'tgroup';
        $table = _TEST;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where([
                'test_group_id' => $group_id
            ])
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'test_group_id', 'test_form_id', 'status'])
            ->setRelation('test_form_id', 'test_forms', 'code')
            ->setRelation('test_group_id', 'test_groups', 'code')
            ->setRelation('subject_id', 'subjects', 'name')
            ->fields(['code', 'test_group_id', 'subject_id', 'test_form_id', 'test_root_numbers', 'test_mix_numbers', 'status'])
            ->requiredFields(['test_root_numbers', 'test_mix_numbers', 'test_form_id', 'status'])
            ->displayAs([
                'code' => 'Mã đề',
                'name' => 'Tên',
                'subject_id' => 'Môn thi',
                'test_group_id' => 'Chi tiết',
                'test_form_id' => 'Dạng đề',
                'test_root_numbers' => 'Số lượng đề gốc',
                'test_mix_numbers' => 'Số lượng đề hoán vị',
            ])
            ->fieldType('status', 'dropdown_search',[
                '1' => 'Sử dụng',
                '0' => 'Ẩn'
            ])
			->unsetReadFields(['deleted_at'])
            // ->setActionButton('Xem trước', 'mif-eye', function ($row) {
			// 	return url('test/test-root-preview/'. $row->id);
			// }, true)
			->unsetAdd()
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('test_group_id', function ($value, $row) use ($group_id) {
            // DB::connection()->enableQueryLog();
            $test = Test::find($row->id);
            $is_generated = TestRoot::where('test_id',$test->id)->where('content','<>','')->count();
            // $queries = DB::getQueryLog();
            // dd($queries);
            if($test->test_form_id && $test->test_root_numbers > 0 && $test->test_mix_numbers > 0){
                if($is_generated){
                    return "
                    <div class=\"gc-data-container-text\">Số lượng đề gốc: " . $test->test_root_numbers . "</div>
                    <div class=\"gc-data-container-text\">Số lượng đề hoán vị: " . $test->test_mix_numbers . "</div>
                    <div class=\"gc-data-container-text mt-2\">
                        <div class=\"cl_manage_parts\">
                            <a class=\"btn btn-outline-dark r20\" href=\"" . url('test/troot/' . $row->id) . "\">Quản trị đề gốc</a>
                        </div>
                    </div>
                    ";
                }else{
                    return "
                    <div class=\"gc-data-container-text\">Số lượng đề gốc: " . $test->test_root_numbers . "</div>
                    <div class=\"gc-data-container-text\">Số lượng đề hoán vị: " . $test->test_mix_numbers . "</div>
                    <div class=\"gc-data-container-text mt-3\">
                        <div class=\"cl_manage_parts\">
                            <a class=\"btn btn-outline-danger r20\" href=\"" . url('test/create-test-root/' . $row->id) . "\">Tạo đề gốc</a>
                        </div>
                    </div>
                    ";
                }
            }else{
                return "
                <div class=\"gc-data-container-text bg-danger\">Chưa cấu hình đề thi</div>
                <div class=\"gc-data-container-text mt-2\">
                    <div class=\"cl_manage_parts\">
                        <a class=\"btn btn-warning r20\" href=\"" . url('test/tgroup-detail/' . $group_id . '#/edit/' . $row->id) . "\">Cấu hình đề thi</a>
                    </div>
                </div>
                ";
            }
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackAfterUpdate(function ($stateParameters) {
            $test_id = $stateParameters->primaryKeyValue;
            $test_root_numbers = $stateParameters->data['test_root_numbers'];
            for($i = 0; $i < $test_root_numbers; $i++){
                TestRoot::create([
                    'code' => $stateParameters->data['code'] . "#" . ($i+1),
                    'test_group_id' => intval($stateParameters->data['test_group_id']),
                    'test_id' => intval($test_id),
                    'test_form_id' => intval($stateParameters->data['test_form_id']),
                    'subject_id' => intval($stateParameters->data['subject_id']),
                    'content' => ''
                ]);
            }
            return $stateParameters;
        });
        
        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * Test root management
     */
    public function testRoot($test_id = 0)
    {
        $test = Test::find($test_id);
        $subject = Subject::find($test->subject_id);
        $title = 'Quản trị đề gốc ' . $test->name . ' - ' . $subject->name;
        $cat = 'test';
        $subcat = 'tgroup';
        $table = _TROOT;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where([
                'test_id' => $test_id
            ])
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'test_group_id', 'subject_id', 'content','is_used'])
            ->setRelation('test_group_id', 'test_groups', 'code')
            // ->setRelation('test_id', 'tests', 'code')
            ->setRelation('subject_id', 'subjects', 'name')
            // ->setRelation('test_form_id', 'test_forms', 'name')
            ->fields(['code', 'test_group_id', 'subject_id', 'content', 'status'])
            ->requiredFields(['test_form_id', 'test_mix_numbers', 'test_form_id', 'status'])
            ->displayAs([
                'code' => 'Mã đề',
                'test_group_id' => 'Bộ đề',
                // 'test_id' => 'Đề thi',
                'subject_id' => 'Môn thi',
                'test_form_id' => 'Dạng đề',
                'content' => 'Chi tiết',
                'is_used' => 'Tình trạng'
                // 'test_root_numbers' => 'Số lượng đề gốc',
                // 'test_mix_numbers' => 'Số lượng đề hoán vị',
            ])
            ->fieldType('is_used', 'dropdown_search',[
                '1' => 'Đã dùng',
                '0' => 'Chưa dùng'
            ])
			->unsetReadFields(['deleted_at'])
            ->setActionButton('Xem trước', 'mif-eye', function ($row) {
				return url('test/test-root-preview/'. $row->id);
			}, true)
			->unsetAdd()
			->unsetEdit()
			->unsetDelete()
			// ->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('content', function ($value, $row) {
            $test_root = TestRoot::find($row->id);
            $test_mix_numbers = TestMix::where('test_root_id',$row->id)->count();
            return "
                <div class=\"gc-data-container-text\">Số lượng đề hoán vị: " . $test_mix_numbers . "</div>
                <div class=\"gc-data-container-text mt-2\">
                    <div class=\"cl_manage_parts\">
                        <a class=\"btn btn-outline-dark r20\" href=\"" . url('test/tmix/' . $row->id) . "\">Quản trị đề hoán vị</a>
                    </div>
                </div>
                ";
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        // $crud->callbackAfterUpdate(function ($stateParameters) {
        //     $test_id = $stateParameters->primaryKeyValue;
        //     $test_root_numbers = $stateParameters->data['test_root_numbers'];
        //     for($i = 0; $i < $test_root_numbers; $i++){
        //         TestRoot::create([
        //             'code' => $stateParameters->data['code'] . "#" . ($i+1),
        //             'test_group_id' => intval($stateParameters->data['test_group_id']),
        //             'test_id' => intval($test_id),
        //             'test_form_id' => intval($stateParameters->data['test_form_id']),
        //             'subject_id' => intval($stateParameters->data['subject_id']),
        //             'content' => ''
        //         ]);
        //     }
        //     return $stateParameters;
        // });
        
        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        $back_url = 'test/tgroup-detail/' . $test->test_group_id;

        return $this->_example_output($output, $title, $cat, $subcat, $back_url);
    }

    /**
     * Test mix management
     */
    public function testMix($test_root_id = 0)
    {
        $test_root = TestRoot::find($test_root_id);
        $test = Test::find($test_root->test_id);
        $subject = Subject::find($test->subject_id);
        $title = 'Quản trị đề hoán vị ' . $test->name . ' - ' . $subject->name;
        $cat = 'test';
        $subcat = 'tgroup';
        $table = _TMIX;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where([
                'test_mixes.test_root_id' => $test_root_id
            ])
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'test_root_id', 'subject_id', 'content','is_used'])
            ->setRelation('test_group_id', 'test_groups', 'code')
            // ->setRelation('test_id', 'tests', 'code')
            ->setRelation('test_root_id', 'test_mixes', 'code')
            ->setRelation('subject_id', 'subjects', 'name')
            // ->setRelation('test_form_id', 'test_forms', 'name')
            ->fields(['code', 'test_group_id', 'subject_id', 'content', 'status'])
            // ->requiredFields(['test_mix_numbers', 'test_form_id', 'status'])
            ->displayAs([
                'code' => 'Mã đề',
                'test_group_id' => 'Bộ đề',
                // 'test_id' => 'Đề thi',
                'test_root_id' => 'Đề gốc',
                'subject_id' => 'Môn thi',
                // 'test_form_id' => 'Dạng đề',
                'content' => 'Chi tiết',
                'is_used' => 'Tình trạng'
                // 'test_root_numbers' => 'Số lượng đề gốc',
                // 'test_mix_numbers' => 'Số lượng đề hoán vị',
            ])
            ->fieldType('is_used', 'dropdown_search',[
                '1' => 'Đã dùng',
                '0' => 'Chưa dùng'
            ])
			->unsetReadFields(['deleted_at'])
            ->setActionButton('Xem trước', 'mif-eye', function ($row) {
				return url('test/test-mix-preview/'. $row->id);
			}, true)
			->unsetAdd()
			->unsetEdit()
			->unsetDelete()
			// ->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('test_root_id', function ($value, $row) use ($test_root_id) {
            return "
                <div class=\"gc-data-container-text mt-2\">
                    <div class=\"cl_manage_parts\">
                        <a class=\"btn btn-outline-danger r20\" href=\"" . url('test/test-root-preview/'. $test_root_id) . "\" target=\"_blank\">
                            <i class=\"mif-eye\"></i><span> Xem đề gốc
                        </a>
                    </div>
                </div>
                ";
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        // $crud->callbackAfterUpdate(function ($stateParameters) {
        //     $test_id = $stateParameters->primaryKeyValue;
        //     $test_root_numbers = $stateParameters->data['test_root_numbers'];
        //     for($i = 0; $i < $test_root_numbers; $i++){
        //         TestRoot::create([
        //             'code' => $stateParameters->data['code'] . "#" . ($i+1),
        //             'test_group_id' => intval($stateParameters->data['test_group_id']),
        //             'test_id' => intval($test_id),
        //             'test_form_id' => intval($stateParameters->data['test_form_id']),
        //             'subject_id' => intval($stateParameters->data['subject_id']),
        //             'content' => ''
        //         ]);
        //     }
        //     return $stateParameters;
        // });
        
        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        $back_url = 'test/troot/' . $test->id;
        return $this->_example_output($output, $title, $cat, $subcat, $back_url);
    }

    private function random_keys_array(& $array){
        $keys = array_keys($array);
    
        shuffle($keys);
    
        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }
    
        $array = $new;
        return true;
    }

    public function createTestRoot($test_id){
        ini_set('max_execution_time', 0);
        $test_roots = TestRoot::where('test_id',$test_id)->get();
        $test = Test::find($test_id);
        //ma trận mặc định là ô 1 -> 40, hoặc có thể nhận từ nguồn bên ngoài
        $matrix_40 = [
            1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,
            21,22,23,24,25,
            26,27,28,29,30,
            31,32,33,34,35,36,37,38,39,40
        ];
        $matrix_22 = [
            1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,
            21,22
        ];
        foreach($test_roots as $test_root){
            //Tạo đề thi gốc theo môn, dạng đề
            $test_root_content = [];
            $question_number = 0;
            $subject_id = $test_root->subject_id;
            $test_form_id = $test_root->test_form_id;
            $test_form = TestForm::find($test_form_id);
            $duration = $test_form->time;
            $test_parts = TestFormPart::where('test_form_id',$test_form_id)->orderBy('order','asc')->get();
            if($subject_id == 1 || $subject_id == 2 || $subject_id == 3 || $subject_id == 4){
                $matrix = $matrix_40;
            }
            if($subject_id == 5){
                $matrix = $matrix_22;
            }

            foreach($test_parts as $test_part){
                $part = TestPart::find($test_part->test_part_id);
                $part_questions = explode(',',$test_part->list_questions);
                // $question_type_id = $test_part->question_type_id;
                
                foreach($part_questions as $question_location){
                    //câu thứ i sẽ tương ứng ô thứ i trong ma trận
                    $matrix_location = $matrix[$question_location - 1];

                    // $min_used_query = "SELECT MIN(no_used) FROM questions WHERE status = 1 AND matrix_location = {$matrix_location} AND subject_id = {$subject_id} AND question_type_id = {$question_type_id} AND cloze_id IS NULL";
                    $min_used_query = "SELECT MIN(no_used) FROM questions WHERE status = 1 AND matrix_location = {$matrix_location} AND subject_id = {$subject_id} AND cloze_id IS NULL";

                    // $question = Question::whereRaw('no_used = ('.$min_used_query.') AND status = 1 AND matrix_location = '.$matrix_location.' AND subject_id = '.$subject_id.' AND question_type_id = '.$question_type_id.' AND cloze_id IS NULL')
                    $question = Question::whereRaw('no_used = (' . $min_used_query . ') AND status = 1 AND matrix_location = ' . $matrix_location . ' AND subject_id = ' . $subject_id . ' AND cloze_id IS NULL')
                                // ->orderBy('last_used','asc')
                                ->inRandomOrder()
                                ->first();
                    
                    if(!isset($question)){
                        continue;
                    }

                    // dd($question);
                    $question->no_used = intval($question->no_used?$question->no_used:0) + 1;
                    $question->last_used = time();
                    $question->save();

                    //thêm vào nội dung đề gốc
                    if($part->caltype != 'auto_context'){
                        $question_number++;
                        $test_root_content[$part->id][$question_number] = $question->id;
                    }elseif($part->caltype == 'auto_context'){
                        //câu hỏi ngữ cảnh
                        // $question_number++;
                        $context_questions = Question::where('cloze_id',$question->id)->orderBy('matrix_location','asc')->get();
                        // dd($context_questions);
                        // $_cur_question_number = $question_number;
                        // $_question_number = $_cur_question_number;
                        foreach($context_questions as $context_question){
                            $question_number++;
                            // $test_root_content[$part->id][$question_number][$question->id][$_question_number] = $context_question->id;
                            $test_root_content[$part->id][$question_number] = $context_question->id;
                            // $_cur_question_number = $_question_number;
                            // $_question_number++;
                            $context_question->no_used = $question->no_used;
                            $context_question->last_used = $question->last_used;
                            $context_question->save();
                        }
                        // $question_number = $_cur_question_number;
                    }else{
                        continue;
                    }
                }
            }
            $test_root->duration = $duration;
            $test_root->content = json_encode($test_root_content);
            $test_root->save();
            // dd($test_root_content);

            //tạo đề hoán vị
            $test_mix_numbers = $test->test_mix_numbers;
            for($mix_number = 1; $mix_number <= $test_mix_numbers; $mix_number++){
                $test_mix_content = [];
                foreach($test_root_content as $_part_id => $_questions){
                    $test_mix_content[$_part_id] = [];
                    // $count_question = count($_questions);
                    // $_part = TestFormPart::where('test_form_id',$test_form_id)->where('test_part_id',$_part_id)->first();
                    $_part = TestPart::find($_part_id);
                    // dd($_part);
                    if($_part->is_shuffled){
                        $whole_part = $_questions;
                        $this->random_keys_array($whole_part);
                        $test_mix_content[$_part_id] = $whole_part;
                    }else{
                        $test_mix_content[$_part_id] = $_questions;
                    }
                    // switch($_part->question_type_id){
                    //     case 11: case 2: case 17:
                    //         $whole_part = $_questions;
                    //         $this->random_keys_array($whole_part);
                    //         $test_mix_content[$_part_id] = $whole_part;
                    //         break;
                    //     case 9: case 20: // câu tự luận Văn / câu ngữ cảnh
                    //         $test_mix_content[$_part_id] = $_questions;
                    //         break;
                    // }
                }
                // dd($test_mix_content);
                //trộn vị trí đáp án
                $mixes = array();
                //Tìm đến các câu hỏi và hoán vị các phương án trả lời với các câu hỏi trắc nghiệm đa lựa chọn
                foreach ($test_mix_content as $mx_part => $mx_questions) {
                    foreach ($mx_questions as $mx_location => $mx_question_id) {
                        // dd($mx_question_id);
                        $_question = Question::find($mx_question_id);
                        if(!$_question) continue;
                        // dd($_question);
                        // đảo vị trí phương án
                        $mix_options = array();
                        if($_question->no_options){
                            for($i=1; $i<=intval($_question->no_options); $i++){
                                $mix_options[] = $i;
                            }
                            if(intval($_question->is_shuffled)) shuffle($mix_options);
                        }
                        // $mix_options = $array;
                        // dd($mix_options);
                        $mixes[intval($mx_part)][intval($mx_location)][intval($mx_question_id)] = $mix_options;
                    }
                }
                // dd($mixes);
                TestMix::create([
                    'code' => $test_root->code . "#" . $mix_number,
                    // 'content' => json_encode($test_mix_content),
                    'content' => json_encode($mixes),
                    'duration' => $duration,
                    'test_group_id' => intval($test_root->test_group_id),
                    'test_id' => intval($test_root->test_id),
                    'test_root_id' => intval($test_root->id),
                    'test_form_id' => intval($test_root->test_form_id),
                    'subject_id' => intval($test_root->subject_id),
                ]);
            }
        }
        
        return back();
    }

    public function testRootPreview($test_root_id){
		$test_root = TestRoot::find($test_root_id);
		$test_root_content = json_decode($test_root->content, TRUE);
        $html = '<legend class="p-1 fw-bold bg-light" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><h1>'.$test_root->code.'</h1></div><div class="col text-end fs-6"></div></div></legend>';
		foreach($test_root_content as $part_id => $questions){
			$part = TestPart::find($part_id);
			$part_caltype = $part->caltype;
			$html .= '<div><h1>'.$part->part_title.'</h1></div>';
			foreach($questions as $location => $question_id){
				if ($part_caltype == 'auto'){
                    $question = Question::find($question_id);
                    
					$html .= '<legend class="p-1 fw-bold bg-light my-2" id="q2_'. $question_id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.$location.' ('.$question->code.')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div>';
                    
                    $question_mark = QuestionMark::find($question->question_mark_id);
                    $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Điểm: ' . floatval($question_mark->value) . '</strong></div>';

                    $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Đáp án: ' . $question->answer_key . '</strong></div>';
                    $options_order = [1,2,3,4];
                    if($question->question_type_id == 1){
                        foreach($options_order as $i){
                            $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question_id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q'. $question_id .'_' . $i . '" id="q'. $question_id .'_' . $i . '">'.$question['option_' . $i].'</label></div>';
                        }
                    }elseif($question->question_type_id == 2){
                        // foreach($options_order as $i){
                        //     $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question_id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="checkbox" name="q'. $question_id .'_' . $i . '" id="q'. $question_id .'_' . $i . '">'.$question['option_' . $i].'</label></div>';
                        // }

                        for($i = 1; $i <= $question->no_options; $i++){
                            $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question_id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="checkbox" name="q'. $question_id .'_' . $i . '" id="q'. $question_id .'_' . $i . '">'.$question['option_' . $i].'</label></div>';
                        }
                    }
				} else if ($part_caltype == 'auto_fill'){
                    $question = Question::find($question_id);
					$html .= '<legend class="p-1 fw-bold bg-light my-2" id="q2_'. $question_id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.$location.' ('.$question->code.')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div>';
                    
                    $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Kiểu đáp án: ' . $question->answer_type . '</strong></div>';
                    $question_mark = QuestionMark::find($question->question_mark_id);
                    $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Điểm: ' . floatval($question_mark->value) . '</strong></div>';
                    $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Đáp án: ' . $question->answer_key . '</strong></div>';
					
                    $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question_id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button">Đáp án: <input class="form-check-input me-2  q11" type="text" name="q11_'. $question_id .'" id="q11_'. $question_id.'"></div><hr>';
				} else if ($part_caltype == 'auto_context'){
                    $question = Question::find($question_id);
                    if($question->context_order == 1){
                        //show context sentence
                        $context_question = Question::find($question->cloze_id);
                        $html .= '<div class="my-2"><strong>'.$context_question->code.'</strong></div><div class="m-1 cl_question_content row">'.$context_question->pre_content.'</div><div class="m-1 cl_question_content row">'.$context_question->content.'</div>';
                    }
                    $html .= '<legend class="p-1 fw-bold bg-light" id="q2_'.$question->id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.($location).' ('.$question->code.')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div>';

                    $question_mark = QuestionMark::find($question->question_mark_id);
                    if($question->question_type_id != 5){
                        $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Điểm: ' . floatval($question_mark->value) . '</strong></div>';
                        $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Đáp án: ' . $question->answer_key . '</strong></div>';
                        $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question->id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q11_'. $question->id .'" id="q11_'. $question->id.'">'.$question->option_1.'</label></div>';
                        $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question->id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q11_'. $question->id .'" id="q11_'. $question->id.'">'.$question->option_2.'</label></div>';
                        $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question->id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q11_'. $question->id .'" id="q11_'. $question->id.'">'.$question->option_3.'</label></div>';
                        $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question->id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q11_'. $question->id .'" id="q11_'. $question->id.'">'.$question->option_4.'</label></div><hr>';
                    }else{
                        // $html .= '<legend class="p-1 fw-bold bg-light my-2" id="q2_'. $question_id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.$location.' ('.$question->code.')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div><hr>';
                    }
				} else if ($part_caltype == 'reader'){
                    $question = Question::find($question_id);
					$html .= '<legend class="p-1 fw-bold bg-light my-2" id="q2_'. $question_id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.$location.' ('.$question->code.')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div><hr>';
				}
			}
		}

        return html_entity_decode($html);
    }

    public function testMixPreview($test_mix_id){
        $test_mix = TestMix::find($test_mix_id);
		$test_mix_content = json_decode($test_mix->content, TRUE);
        $html = '<legend class="p-1 fw-bold bg-light" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><h1>'.$test_mix->code.'</h1></div><div class="col text-end fs-6"></div></div></legend>';
        $number = 0;
		foreach($test_mix_content as $part_id => $questions){
			$part = TestPart::find($part_id);
			$part_caltype = $part->caltype;
			$html .= '<div><h1>'.$part->part_title.'</h1></div>';
			foreach($questions as $location => $question){
                $number++;
                foreach($question as $question_id => $options_order){
                    // dd($question_id);
                    // dd($options_order);
                    if ($part_caltype == 'auto'){
                        $question = Question::find($question_id);
                        $html .= '<legend class="p-1 fw-bold bg-light" id="q2_'. $question_id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.$number .' - '.$question->code. ' (' . $location .')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div>';
                        $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Đáp án: ' . $question->answer_key . '</strong></div>';
                        if($question->question_type_id == 1){
                            foreach($options_order as $i){
                                $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question_id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q'. $question_id .'_' . $i . '" id="q'. $question_id .'_' . $i . '">'.$question['option_' . $i].'</label></div>';
                            }
                        }elseif($question->question_type_id == 2){
                            foreach($options_order as $i){
                                $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question_id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="checkbox" name="q'. $question_id .'_' . $i . '" id="q'. $question_id .'_' . $i . '">'.$question['option_' . $i].'</label></div>';
                            }
                        }
                    } else if ($part_caltype == 'auto_fill'){
                        $question = Question::find($question_id);
                        $html .= '<legend class="p-1 fw-bold bg-light" id="q2_'. $question_id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.$number .' - '.$question->code. ' (' . $location .')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div>';
                        $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Đáp án: ' . $question->answer_key . '</strong></div>';
                        $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question_id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button">Đáp án: <input class="form-check-input me-2  q11" type="text" name="q11_'. $question_id .'" id="q11_'. $question_id.'"></div><hr>';
                    } else if ($part_caltype == 'auto_context'){
                        $question = Question::find($question_id);
                        if($question->context_order == 1){
                            //show context sentence
                            $context_question = Question::find($question->cloze_id);
                            $html .= '<div><strong>'.$context_question->code.'</strong></div><div class="m-1 cl_question_content row">'.$context_question->pre_content.'</div><div class="m-1 cl_question_content row">'.$context_question->content.'</div>';
                        }
                        $html .= '<legend class="p-1 fw-bold bg-light" id="q2_'.$question->id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.$number .' - '.$question->code. ' (' . $location .')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div>';
                        $html .= '<div class="col text-end fs-6"><strong class="mb-3 fg-red">Đáp án: ' . $question->answer_key . '</strong></div>';
                        foreach($options_order as $i){
                            $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question_id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q'. $question_id .'_' . $i . '" id="q'. $question_id .'_' . $i . '">'.$question['option_' . $i].'</label></div>';
                        }
                        // $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question->id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q11_'. $question->id .'" id="q11_'. $question->id.'">'.$question->option_1.'</label></div>';
                        // $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question->id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q11_'. $question->id .'" id="q11_'. $question->id.'">'.$question->option_2.'</label></div>';
                        // $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question->id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q11_'. $question->id .'" id="q11_'. $question->id.'">'.$question->option_3.'</label></div>';
                        // $html .= '<div class="form-checkcol position-relative align-middle"><label  id="q11_'. $question->id . '_label" class="label_question alert alert-primary  col-6 border border-light m-1 p-1 ps-3 text-start q11" role="button"><input class="form-check-input me-2  q11" type="radio" name="q11_'. $question->id .'" id="q11_'. $question->id.'">'.$question->option_4.'</label></div><hr>';
                    } else if ($part_caltype == 'reader'){
                        $question = Question::find($question_id);
                        $html .= '<legend class="p-1 fw-bold bg-light" id="q2_'. $question_id . '" style="scroll-margin-top: 2em;"><div class="row"><div class="col"><strong>Câu '.$number .' - '.$question->code. ' (' . $location .')</strong></div><div class="col text-end fs-6"></div></div></legend><div class="m-1 cl_question_content row">'.$question->content.'</div><hr>';
                    }
                }
			}
		}

        return html_entity_decode($html);
    }

    //download test group
    public function downloadTestGroup(Request $request, $id){
        ini_set('max_execution_time', 0);
        // try {
        //     if (! $user = JWTAuth::parseToken()->authenticate()) {
        //         return response()->json(['error' => 'User not found'], 404);
        //     }
        // } catch (JWTException $e) {
        //     return response()->json(['error' => 'Invalid token'], 400);
        // }
        $test_group_id = $id;
        $test_group = TestGroup::find($test_group_id);
        if(!$test_group){
            return response()->json([
                'success' => false,
                'message' => 'Test not found',
            ], 404);
        }
        // $password = $request->input('password');
        // if(!$password) $password = $test_group->password;
        $password = $test_group->password;
        $test_mixes = TestMix::where('test_group_id',$test_group->id)
                            ->where('is_used',0)
                            ->get();
        $test_form_response = [];
        $test_part_response = [];
        $test_subject_response = [];
        $test_mixes_response = [];
        $questions_response = [];
        $form_id_array = [];
        $part_id_array = [];
        $subject_id_array = [];
        $question_id_array = [];
        foreach($test_mixes as $test_mix){
            $test_mixes_response[] = array(
                'id' => $test_mix->id,
                'code' => $test_mix->code,
                'subject_id' => $test_mix->subject_id,
                'content' => $test_mix->content,
                'is_used' => $test_mix->is_used,
                'duration' => $test_mix->duration,
                'test_group_id' => $test_mix->test_group_id,
                'test_form_id' => $test_mix->test_form_id,
                'test_root_id' => $test_mix->test_root_id,
                'subject_id' => $test_mix->subject_id,
                'status' => $test_mix->status,
            );
            if(!in_array($test_mix->test_form_id, $form_id_array)){
                $form_id_array[] = $test_mix->test_form_id;
                $test_form = TestForm::find($test_mix->test_form_id);
                $test_form_response[] = array(
                    'id' => $test_mix->test_form_id,
                    'code' => $test_form->code,
                    'name' => $test_form->name,
                    'time' => $test_form->time,
                    'no_questions' => $test_form->no_questions,
                    'no_part' => $test_form->no_part,
                    'status' => $test_form->status,
                );
            }
            if(!in_array($test_mix->subject_id, $subject_id_array)){
                $subject_id_array[] = $test_mix->subject_id;
                $test_subject = Subject::find($test_mix->subject_id);
                $test_subject_response[] = array(
                    'id' => $test_mix->subject_id,
                    'code' => $test_subject->code,
                    'short_code' => $test_subject->short_code,
                    'code_number' => $test_subject->code_number,
                    'name' => $test_subject->name,
                    'desc' => $test_subject->desc,
                    'status' => $test_subject->status
                );
            }

            $test_mix_content = json_decode($test_mix->content, TRUE);
            $_tmp = array(
                "id" => 0,
                "cloze_id" => 0,
                "question_type_id" => 0,
                "subject_id" => 0,
                "uuid" => "",
                "code" => "",
                "pre_content" => "",
                "content" => "",
                "post_content" => "",
                "option_1" => "",
                "option_2" => "",
                "option_3" => "",
                "option_4" => "",
                "option_5" => "",
                "option_6" => "",
                "option_7" => "",
                "option_8" => "",
                "option_9" => "",
                "option_10" => "",
                "min_words" => 0,
                "max_words" => 0,
                "answer_type" => 0,
                "no_options" => 0,
                "is_shuffled" => 0,
                "no_used" => 0,
                "last_used" => 0,
            );
            foreach($test_mix_content as $part_id => $questions){
                if(!in_array($part_id, $part_id_array)){
                    $part_id_array[] = $part_id;
                    $test_part = TestPart::find($part_id);
                    $test_part_response[] = array(
                        'id' => $test_part->id,
                        'name' => $test_part->name,
                        'part_title' => $test_part->part_title,
                        'desc' => $test_part->desc,
                        'caltype' => $test_part->caltype,
                        'is_shuffled' => $test_part->is_shuffled,
                        'status' => $test_part->status,
                    );
                }
                foreach($questions as $question){
                    foreach($question as $question_id => $option_order){
                        if(in_array($question_id,$question_id_array)){
                            continue;
                        }
                        $question_id_array[] = $question_id;
                        $_question = $_tmp;
                        $question = Question::find($question_id);
                        $_question['id'] = $question_id;
                        $_question['cloze_id'] = $question->cloze_id;
                        $_question['question_type_id'] = $question->question_type_id;
                        $_question['subject_id'] = $question->subject_id;
                        $_question['uuid'] = $question->uuid;
                        $_question['code'] = $question->code;
                        $_question['pre_content'] = $question->pre_content;
                        $_question['content'] = $question->content;
                        $_question['post_content'] = $question->post_content;
                        $_question['option_1'] = $question->option_1;
                        $_question['option_2'] = $question->option_2;
                        $_question['option_3'] = $question->option_3;
                        $_question['option_4'] = $question->option_4;
                        $_question['option_5'] = $question->option_5;
                        $_question['option_6'] = $question->option_6;
                        $_question['option_7'] = $question->option_7;
                        $_question['option_8'] = $question->option_8;
                        $_question['option_9'] = $question->option_9;
                        $_question['option_10'] = $question->option_10;
                        $_question['min_words'] = $question->min_words;
                        $_question['max_words'] = $question->max_words;
                        $_question['answer_type'] = $question->answer_type;

                        //attach answer_key & mark
                        // $_question['answer_key'] = $question->answer_key;
                        // $_question['question_mark_id'] = $question->question_mark_id;

                        $_question['matrix_location'] = $question->matrix_location;
                        $_question['context_order'] = $question->context_order;
                        $_question['no_options'] = $question->no_options;
                        $_question['is_shuffled'] = $question->is_shuffled;
                        $_question['no_used'] = $question->no_used;
                        $_question['last_used'] = $question->last_used;
                        
                        $questions_response[] = $_question;

                        // if ($question->cloze_id && !in_array($question->cloze_id,$question_id_array)){
                        if ($question->cloze_id && $question->context_order == 1){
                            $cloze_id = $question->cloze_id;
                            $context_question = Question::find($cloze_id);
                            $question_id_array[] = $context_question->id;
                            
                            $_question = $_tmp;
                            $_question['id'] = $context_question->id;
                            $_question['cloze_id'] = $context_question->cloze_id;
                            $_question['question_type_id'] = $context_question->question_type_id;
                            $_question['subject_id'] = $context_question->subject_id;
                            $_question['uuid'] = $context_question->uuid;
                            $_question['code'] = $context_question->code;
                            $_question['matrix_location'] = $context_question->matrix_location;
                            $_question['pre_content'] = $context_question->pre_content;
                            $_question['content'] = $context_question->content;
                            $_question['post_content'] = $context_question->post_content;
                            $_question['no_used'] = $context_question->no_used;
                            $_question['last_used'] = $context_question->last_used;
                            $questions_response[] = $_question;
                        }
                    }
                }
            }
        }
        
        $packaged_time = time();
        $filename = $packaged_time. '_' . $test_group->code . '.dat';
        $test_group->packaged_at = $packaged_time;
        $test_group->encryption_file = $filename;
        $test_group->is_used = 1;
        $test_group->save();

        $header = array(
            'code' => $test_group->code,
            'test_group' => $test_group,
            'packaged_at' => $packaged_time,
            // 'packaged_by' => $user->email,
            'private_key' => $password
        );
        $body = array(
            'test_form' => $test_form_response,
            'test_part' => $test_part_response,
            'subject' => $test_subject_response,
            'test_mixes' => $test_mixes_response,
            'questions' => $questions_response
        );
        $package = array(
            'header' => $header,
            'body' => $body
        );

        $plain = json_encode($package);

        // Store the cipher method
        $ciphering = "AES-128-CTR";
        // Use OpenSSl Encryption method
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = 0;
        
        // Non-NULL Initialization Vector for encryption
        $encryption_iv = '1234567891011121';
        // Store the encryption key
        $encryption_key = $password;
        // Use openssl_encrypt() function to encrypt the data
        $encryption = openssl_encrypt($plain, $ciphering, $encryption_key, $options, $encryption_iv);

        // Non-NULL Initialization Vector for decryption
        $decryption_key = $password;
        $decryption_iv = '1234567891011121';
        $decryption = openssl_decrypt ($encryption, $ciphering, $decryption_key, $options, $decryption_iv);

        //write file
        Storage::put('testdata/plain/' . $filename,$plain);
        Storage::put('testdata/encrypt/' . $filename,$encryption);
        Storage::put('testdata/decrypt/' . $filename,$decryption);

        return Storage::download('testdata/encrypt/' . $filename);
    }

    public function importTestGroup(Request $request, $code){
        // $contents = Storage::json('file.jpg');
        // $contents = Storage::get('file.jpg');
        // Store the decryption key
        $password = $request->input('testpassword');
        // dd($password);

        $import_time = time();
        $path = 'testdata/import/' . $code . '/';
        $filename = $import_time. '_' . $code . '.dat';
        $file = $request->file('testdata');
        $path = $file->storeAs(
            $path, $filename
        );

        $encryption = Storage::get($path);
        // dd($encryption);
        // Use openssl_decrypt() function to decrypt the data
        // Store the cipher method
        $ciphering = "AES-128-CTR";
        $options = 0;
        // Non-NULL Initialization Vector for decryption
        // $decryption_iv = '1234567891011121';
        // $decryption = openssl_decrypt ($contents, $ciphering, $decryption_key, $options, $decryption_iv);
        $decryption_key = $password;
        $decryption_iv = '1234567891011121';
        $decryption = openssl_decrypt ($encryption, $ciphering, $decryption_key, $options, $decryption_iv);
        Storage::put('testdata/decrypt/' . $filename,$decryption);
        // dd($decryption);

        $test_mixes_content = json_decode($decryption,TRUE);
        // dd($test_mixes_content);
        if(!$test_mixes_content){
            return redirect()->back()->with('message', 'Something wrong!');
        }

        //import to DB
        $council_turn = CouncilTurn::where('code',$code)->first();
        if(!$council_turn){
            return redirect()->back()->with('message', 'Something wrong!');
        }

        $header = $test_mixes_content['header'];
        $test_group = $header['test_group'];
        if(!(TestGroup::where('id',$test_group['id'])->count())){
            $test_group['encryption_file'] = $filename;
            // dd($test_group);
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
            $test_mix['council_code'] = $council_turn->council_code;
            // $test_mix['council_turn_code'] = $council_turn->code;
            // dd($test_mix);
            if(!(TestMix::where('id',$test_mix['id'])->count())){
                TestMix::create($test_mix);
            }
            if(!(CouncilTurnTestMix::where('council_code',$council_turn->council_code)->where('council_turn_code',$council_turn->code)->where('test_mix_id',$test_mix['id'])->count())){
                CouncilTurnTestMix::create([
                    'test_mix_id' => $test_mix['id'],
                    'subject_id' => $test_mix['subject_id'],
                    'council_code' => $council_turn->council_code,
                    'council_turn_code' => $council_turn->code,
                ]);
            }
        }
        $question_array = $body['questions'];
        foreach($question_array as $question){
            $question['question_mark_id'] = 1;
            $question['competency_id'] = 1;
            $question['taxonomy_id'] = 1;
            $question['topic_id'] = 1;
            $question['difficult_id'] = 1;
            // dd($question);
            if(!(Question::where('id',$question['id'])->count())){
                // dd($question);
                Question::create($question);
            }
        }

        // return response()->json($test_mixes_content, 200);
        return redirect()->back()->with('message', 'File uploaded successfully.');
    }
}
