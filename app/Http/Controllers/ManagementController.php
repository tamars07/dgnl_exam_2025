<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Difficult;
use App\Models\Grade;
use App\Models\QuestionMark;
use App\Models\QuestionStore;
use App\Models\QuestionType;
use App\Models\Subject;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\Organization;
use App\Models\Room;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use GroceryCrud\Core\GroceryCrud;

/* DB table name */
define('_USER','users');
define('_ROLE','roles');
define('_DIFFCULT','difficults');
define('_GRADE','grades');
define('_SUBJECT','subjects');
define('_COMPETENCY','competencies');
define('_TAXONOMY','taxonomies');
define('_TOPIC','topics');
define('_QUESTION','questions');
define('_QMARK','question_marks');
define('_QTYPE','question_types');
define('_QSTORE','question_stores');
define('_QOPTION','question_options');
define('_ORG','organizations');
define('_ROOM','rooms');

class ManagementController extends Controller
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
    private function _example_output($output = null, $title = '', $cat = '', $subcat = '') {
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
  
        // $title = 'Quản lý dữ liệu';
        $title = 'QBANK';
        $cat = $subcat = '';
        $output = (object)[
            'css_files' => [],
            'js_files' => [],
            'output' => '<div class="bg-red fg-white p-2">Vui lòng chọn chức năng.</div>'
        ];
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * Difficult management
     */
    public function difficult()
    {
        $title = 'Độ khó';
        $cat = 'cate';
        $subcat = 'diff';
        $table = _DIFFCULT;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['name', 'desc','status'])
            ->setRead()
            ->displayAs([
                'name' => 'Tên',
                'desc' => 'Diễn giải',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
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
     * Question type management
     */
    public function questionType()
    {
        $title = 'Loại câu hỏi';
        $cat = 'cate';
        $subcat = 'qtype';
        $table = _QTYPE;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'desc', 'status'])
            ->defaultOrdering('status', 'desc')
            ->setRead()
            ->displayAs([
                'code' => 'Mã',
                'name' => 'Tên',
                'desc' => 'Diễn giải',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
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
     * Subject management
     */
    public function subject()
    {
        $title = 'Môn học';
        $cat = 'cate';
        $subcat = 'subject';
        $table = _SUBJECT;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'desc','status'])
            ->setRead()
            ->displayAs([
                'code' => 'Mã',
                'name' => 'Tên',
                'desc' => 'Diễn giải',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->fields(['code', 'name', 'desc', 'status'])
            ->requiredFields(['code', 'name'])
            ->editFields(['code', 'name', 'desc', 'short_code', 'code_number', 'status'])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
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
     * Question mark management
     */
    public function questionMark()
    {
        $title = 'Điểm';
        $cat = 'cate';
        $subcat = 'qmark';
        $table = _QMARK;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['name', 'desc', 'value','status'])
            ->setRead()
            ->displayAs([
                'name' => 'Tên',
                'desc' => 'Diễn giải',
                'value'=>'Giá trị',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
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
     * Question store management
     */
    public function questionStore()
    {
        $title = 'Trạng thái';
        $cat = 'cate';
        $subcat = 'qstore';
        $table = _QSTORE;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'desc','status'])
            ->setRead()
            ->displayAs([
                'code' => 'Mã',
                'name' => 'Tên',
                'desc' => 'Diễn giải',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
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
     * GRADE management
     */
    public function grade()
    {
        $title = 'Khối';
        $cat = 'cate';
        $subcat = 'grade';
        $table = _GRADE;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['name','status'])
            ->setRead()
            ->displayAs([
                'code'=>'Mã khối',
                'name' => 'Tên khối',
                'desc' => 'Diễn giải',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
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
     * competency management
     */
    public function competency()
    {
        $title = 'Năng lực kiến thức';
        $cat = 'cate';
        $subcat = 'competency';
        $table = _COMPETENCY;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('competencies.deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'subject_id', 'status'])
            // ->setRead()
            ->displayAs([
                'code' => 'Mã năng lực kiến thức',
                'name' => 'Tên năng lực kiến thức',
                'desc' => 'Diễn giải',
                'subject_id' => 'Môn học',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->setRelation('subject_id', 'subjects', 'name', ['status ' => 1])
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
     * taxonomy management
     */
    public function taxonomy()
    {
        $title = 'Nội dung kiến thức';
        $cat = 'cate';
        $subcat = 'taxonomy';
        $table = _TAXONOMY;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('taxonomies.deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'subject_id', 'competency_id', 'status'])
            // ->setRead()
            ->displayAs([
                'code' => 'Mã nội dung kiến thức',
                'name' => 'Tên nội dung kiến thức',
                'desc' => 'Diễn giải',
                'grade_id' => 'Khối học',
                'subject_id' => 'Môn học',
                'competency_id' => 'Năng lực',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            // ->setRelation('grade_id', 'grades', 'name')
            ->setRelation('subject_id', 'subjects', 'name', ['status ' => 1])
            ->setRelation('competency_id', 'competencies', '{code} - {name}')
            ->setDependentRelation('competency_id', 'subject_id', 'subject_id')
			->unsetReadFields(['deleted_at'])
			->unsetAddFields(['grade_id','created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['grade_id'] = 1;
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
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
     * topic management
     */
    public function topic()
    {
        $title = 'Đơn vị kiến thức';
        $cat = 'cate';
        $subcat = 'topic';
        $table = _TOPIC;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('topics. deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'grade_id', 'subject_id','taxonomy_id', 'competency_id', 'status'])
            ->setRead()
            ->displayAs([
                'code' => 'Mã đơn vị kiến thức',
                'name' => 'Tên đơn vị kiến thức',
                'desc' => 'Diễn giải',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật',
                'grade_id' => 'Khối học',
                'subject_id' => 'Môn học',
                'taxonomy_id'=>'Nội dung kiến thức',
                'competency_id' => 'Năng lực',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->setRelation('grade_id', 'grades', 'name')
            ->setRelation('subject_id', 'subjects', 'name', ['status ' => 1])
            ->setRelation('competency_id', 'competencies', '{code} - {name}')
            ->setRelation('taxonomy_id', 'taxonomies', '{code} - {name}')
            ->setDependentRelation('competency_id', 'subject_id', 'subject_id')
            ->setDependentRelation('taxonomy_id', 'competency_id', 'competency_id')
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
}
