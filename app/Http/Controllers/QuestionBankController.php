<?php

namespace App\Http\Controllers;

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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GroceryCrud\Core\GroceryCrud;
use Ramsey\Uuid\Uuid;

use Illuminate\Support\Facades\Auth;

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

class QuestionBankController extends Controller
{
    //
    private $database;
    private $config;
    private $user;
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
    private function _example_output($heading = '', $output = null, $title = '', $cat = '', $subcat = '') {
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
            'heading' => $heading,
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
        $this->user = Auth::guard('web')->user();
	}

    public function index()
    {
        $title = 'Quản lý ngân hàng câu hỏi';
        $cat = $subcat = '';
        $output = (object)[
            'css_files' => [],
            'js_files' => [],
            'output' => '<div class="bg-red fg-white p-2">Vui lòng chọn chức năng.</div>'
        ];
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    private function is_duplicated($options){
        $duplicated = false;
        foreach($options as $key => $option){
            if ($duplicated) break;
            foreach($options as $key_search => $option_search){
                if ($option == $option_search) {
                    if ($key != $key_search) {
                        $duplicated = true;
                        break;
                    }
                }
            }
        }
        
        return $duplicated;
    }

    public function questionTN1($cloze_id = 0, $type = 'TN1', $no_options = 4)
    {
        $title = 'Quản lý ngân hàng câu hỏi';
        $cat = 'qbank';
        $subcat = $type;
        $table = _QUESTION;
        $heading = '';
        $q_type = QuestionType::where('code',$type)->first();
        $type_id = $q_type?$q_type->id:0;

        $options = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H',
        );
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
            ->where("questions.question_type_id = $type_id AND cloze_id IS NULL")
            ->setSubject('TN 1 Phương án đúng', 'Câu hỏi trắc nghiệm 4 phương án lựa chọn, 1 phương án đúng')
            ->columns(['code', 'matrix_location', 'subject_id', 'content', 'is_shuffled', 'no_options', 'no_used', 'user_id','status'])
            ->fields([
                'code', 
                'matrix_location', 
                'subject_id',
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                // 'pre_content', 
                'content', 
                // 'post_content', 
                'no_options', 
                'is_shuffled', 
                'question_mark_id', 
                'answer_key', 
                'option_1', 
                'option_2', 
                'option_3', 
                'option_4', 
                // 'option_5', 
                // 'option_6', 
                // 'option_7', 
                // 'option_8',
                // 'user_id',
                'status',
                ])
            ->requiredFields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                // 'answer_key', 
                // 'question_mark_id',
                'content', 
                'option_1', 
                'option_2', 
                'option_3', 
                'option_4', 
                // 'user_id', 
                ])
            ->setRelation('subject_id', 'subjects', '{code} - {name}', ['status' => 1], ['id' => 'asc'])
            ->setRelation('competency_id', 'competencies', '{code} - {name}')
			->setRelation('taxonomy_id', 'taxonomies', '{code} - {name}', ['status' => 1])
			->setRelation('topic_id', 'topics', '{code} - {name}', ['status' => 1])
			->setRelation('difficult_id', 'difficults', 'name', ['status' => 1], ['id' => 'asc'])
			->setRelation('user_id', 'users', '{name}')
			->setRelation('question_mark_id', 'question_marks', '{name} - {value}', ['status' => 1], ['id' => 'asc'])
            ->setDependentRelation('competency_id', 'subject_id', 'subject_id')
            ->setDependentRelation('taxonomy_id', 'competency_id', 'competency_id')
			->setDependentRelation('topic_id', 'taxonomy_id', 'taxonomy_id')
            ->setTexteditor([
                'pre_content', 
                'content', 
                'post_content', 
                'option_1', 
                'option_2', 
                'option_3', 
                'option_4',
                // 'option_5', 
                // 'option_6', 
                // 'option_7', 
                // 'option_8',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('is_shuffled', 'dropdown_search',[
                1 => 'Đảo',
                0 => 'Không'
            ])
            ->fieldType('matrix_location', 'dropdown_search', $multi_select_50)
            ->fieldType('answer_key', 'dropdown_search', $options)
            ->displayAs([
                'uuid' => 'UUID',
                'code' => 'Mã',
                'question_type_id' => 'Kiểu câu hỏi',
                'cloze_id' => 'Câu hỏi ngữ cảnh',
                'subject_id' => 'Môn',
                'competency_id' => 'NLKT',
                'taxonomy_id' => 'NDKT',
                'topic_id' => 'ĐVKT',
                'difficult_id' => 'Độ khó',
                'pre_content' => 'Nội dung trước',
                'content' => 'Nội dung chính',
                'post_content' => 'Gợi ý, tham khảo',
                'option_1' => 'Lựa chọn 1 (A)',
                'option_2' => 'Lựa chọn 2 (B)',
                'option_3' => 'Lựa chọn 3 (C)',
                'option_4' => 'Lựa chọn 4 (D)',
                'option_5' => 'Lựa chọn 1 (E)',
                'option_6' => 'Lựa chọn 2 (F)',
                'option_7' => 'Lựa chọn 3 (G)',
                'option_8' => 'Lựa chọn 4 (H)',
                'answer_type' => 'Kiểu đáp án',
                'answer_key' => 'Đáp án',
                'question_mark_id' => 'Điểm',
                'matrix_location' => 'Vị trí ma trận',
                'order' => 'Thứ tự',
                'no_options' => 'Số lượng phương án',
                'is_shuffled' => 'Đảo phương án',
                'no_used' => 'Số lần sử dụng',
                'user_id' => 'Người nhập',
                'status' => 'Trạng thái',
                'created_at' => 'Tạo',
                'updated_at' => 'Cập nhật lần cuối',
                'deleted_at' => 'Xóa'
            ])
            ->defaultOrdering('status', 'desc')
            // ->setRead()
			->unsetReadFields(['deleted_at'])
            ->unsetAddFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
            ->setActionButton('Xem trước', 'mif-eye', function ($row) {
				return url('qbank/preview/'. $row->id);
			}, true)
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            $data['question_mark_id'] = 1;
            $data['no_options'] = 4;
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) use ($type_id, $no_options) {
            $stateParameters->data['created_at'] = now();
            //Kiểm tra các phương án trùng nhau
            $q_options = array(
                md5($stateParameters->data['option_1']), 
                md5($stateParameters->data['option_2']), 
                md5($stateParameters->data['option_3']), 
                md5($stateParameters->data['option_4']),
                // md5($stateParameters->data['option_5']), 
                // md5($stateParameters->data['option_6']), 
                // md5($stateParameters->data['option_7']), 
                // md5($stateParameters->data['option_8']),
            );
            if ($this->is_duplicated($q_options)) {
                    // The error message as a return parameter is only available at Enterprise version
                    $errorMessage = new \GroceryCrud\Core\Error\ErrorMessage();
                    return $errorMessage->setMessage("Dữ liệu các phương án nhập vào đang có phương án trùng nhau. Hãy kiểm tra lại!\n");
            }
            //(string) Uuid::uuid4()
            $stateParameters->data['uuid'] = (string) Uuid::uuid4();
            $stateParameters->data['question_type_id'] = $type_id;
            $stateParameters->data['question_store_id'] = 6;
            $stateParameters->data['no_options'] = $no_options;
            $stateParameters->data['user_id'] = $this->user->id;
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
        return $this->_example_output($heading, $output, $title, $cat, $subcat);
    }

    public function questionTNN($cloze_id = 0, $type = 'TNN', $no_options = 4)
    {
        $title = 'Quản lý ngân hàng câu hỏi';
        $cat = 'qbank';
        $subcat = $type;
        $table = _QUESTION;
        $heading = '';
        $q_type = QuestionType::where('code',$type)->first();
        $type_id = $q_type?$q_type->id:0;

        $options = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H',
            9 => 'I',
            10 => 'J'
        );
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
            ->where("questions.question_type_id = $type_id AND cloze_id IS NULL")
            ->setSubject('TN Nhiều Phương án đúng', 'Câu hỏi trắc nghiệm 4 phương án lựa chọn, nhiều phương án đúng')
            ->columns(['code', 'matrix_location', 'subject_id','topic_id', 'content', 'is_shuffled', 'no_options', 'no_used','status'])
            ->fields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                // 'pre_content', 
                'content', 
                // 'post_content', 
                'no_options', 
                'is_shuffled', 
                'question_mark_id', 
                'answer_key', 
                'option_1', 
                'option_2', 
                'option_3', 
                'option_4', 
                'option_5', 
                'option_6', 
                // 'option_7', 
                // 'option_8',
                'status'
            ])
            ->requiredFields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                // 'answer_key', 
                // 'question_mark_id',
                'content', 
                'option_1', 
                'option_2', 
                'option_3', 
                'option_4', 
            ])
            ->setRelation('subject_id', 'subjects', '{code} - {name}', ['status' => 1], ['id' => 'asc'])
            ->setRelation('competency_id', 'competencies', '{code} - {name}')
			->setRelation('taxonomy_id', 'taxonomies', '{code} - {name}', ['status' => 1])
			->setRelation('topic_id', 'topics', '{code} - {name}', ['status' => 1])
			->setRelation('difficult_id', 'difficults', 'name', ['status' => 1], ['id' => 'asc'])
			->setRelation('user_id', 'users', '{name}')
			->setRelation('question_mark_id', 'question_marks', '{name} - {value}', ['status' => 1], ['id' => 'asc'])
            ->setDependentRelation('competency_id', 'subject_id', 'subject_id')
            ->setDependentRelation('taxonomy_id', 'competency_id', 'competency_id')
			->setDependentRelation('topic_id', 'taxonomy_id', 'taxonomy_id')
            ->setTexteditor([
                'pre_content', 
                'content', 
                'post_content', 
                'option_1', 
                'option_2', 
                'option_3', 
                'option_4', 
                'option_5', 
                'option_6', 
                // 'option_7', 
                // 'option_8'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('is_shuffled', 'dropdown_search',[
                1 => 'Đảo',
                0 => 'Không'
            ])
            ->fieldType('matrix_location', 'dropdown_search', $multi_select_50)
            ->fieldType('answer_key', 'multiselect_searchable', $multi_select_10)
            ->displayAs([
                'uuid' => 'UUID',
                'code' => 'Mã',
                'question_type_id' => 'Kiểu câu hỏi',
                'cloze_id' => 'Câu hỏi ngữ cảnh',
                'subject_id' => 'Môn',
                'competency_id' => 'NLKT',
                'taxonomy_id' => 'NDKT',
                'topic_id' => 'ĐVKT',
                'difficult_id' => 'Độ khó',
                'pre_content' => 'Nội dung trước',
                'content' => 'Nội dung chính',
                'post_content' => 'Gợi ý, tham khảo',
                'option_1' => 'Lựa chọn 1 (A)',
                'option_2' => 'Lựa chọn 2 (B)',
                'option_3' => 'Lựa chọn 3 (C)',
                'option_4' => 'Lựa chọn 4 (D)',
                'option_5' => 'Lựa chọn 5 (E)',
                'option_6' => 'Lựa chọn 6 (F)',
                'option_7' => 'Lựa chọn 7 (G)',
                'option_8' => 'Lựa chọn 8 (H)',
                'answer_type' => 'Kiểu đáp án',
                'answer_key' => 'Đáp án',
                'question_mark_id' => 'Điểm',
                'matrix_location' => 'Vị trí ma trận',
                'order' => 'Thứ tự',
                'no_options' => 'Số lượng phương án',
                'is_shuffled' => 'Đảo phương án',
                'no_used' => 'Số lần sử dụng',
                'user_id' => 'Người tạo',
                'status' => 'Trạng thái',
                'created_at' => 'Tạo',
                'updated_at' => 'Cập nhật lần cuối',
                'deleted_at' => 'Xóa'
            ])
            ->defaultOrdering('status', 'desc')
            // ->setRead()
			->unsetReadFields(['deleted_at'])
            ->unsetAddFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
            ->setActionButton('Xem trước', 'mif-eye', function ($row) {
				return url('qbank/preview/'. $row->id);
			}, true)
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            $data['question_mark_id'] = 1;
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) use ($type_id, $no_options) {
            $stateParameters->data['created_at'] = now();
            //Kiểm tra các phương án trùng nhau
            $q_options = array(md5($stateParameters->data['option_1']), md5($stateParameters->data['option_2']), md5($stateParameters->data['option_3']), md5($stateParameters->data['option_4']));
            if ($this->is_duplicated($q_options)) {
                    // The error message as a return parameter is only available at Enterprise version
                    $errorMessage = new \GroceryCrud\Core\Error\ErrorMessage();
                    return $errorMessage->setMessage("Dữ liệu các phương án nhập vào đang có phương án trùng nhau. Hãy kiểm tra lại!\n");
            }
            //(string) Uuid::uuid4()
            $stateParameters->data['uuid'] = (string) Uuid::uuid4();
            $stateParameters->data['question_type_id'] = $type_id;
            $stateParameters->data['question_store_id'] = 6;
            $stateParameters->data['no_options'] = $no_options;
            $stateParameters->data['user_id'] = $this->user->id;
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
        return $this->_example_output($heading, $output, $title, $cat, $subcat);
    }

    public function questionTLN($cloze_id = 0, $type = 'TLN')
    {
        $title = 'Quản lý ngân hàng câu hỏi';
        $cat = 'qbank';
        $subcat = $type;
        $table = _QUESTION;
        $heading = '';
        $q_type = QuestionType::where('code',$type)->first();
        $type_id = $q_type?$q_type->id:0;

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
            ->where("questions.question_type_id = $type_id AND cloze_id IS NULL")
            ->setSubject('Trả lời ngắn', 'Câu hỏi điền đáp số')
            ->columns(['code', 'matrix_location', 'subject_id','topic_id', 'content', 'answer_type', 'no_used','status'])
            ->fields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                // 'pre_content', 
                'content', 
                // 'post_content', 
                // 'no_options', 
                // 'is_shuffled', 
                'question_mark_id', 
                'answer_type', 
                'answer_key', 
                // 'option_1', 
                // 'option_2', 
                // 'option_3', 
                // 'option_4', 
                // 'option_5', 
                // 'option_6', 
                // 'option_7', 
                // 'option_8',
                'status'
            ])
            ->requiredFields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                // 'pre_content', 
                'content', 
                // 'post_content', 
                // 'no_options', 
                // 'is_shuffled', 
                // 'question_mark_id', 
                'answer_type', 
                // 'answer_key', 
            ])
            ->setRelation('subject_id', 'subjects', '{code} - {name}', ['status' => 1], ['id' => 'asc'])
            ->setRelation('competency_id', 'competencies', '{code} - {name}')
			->setRelation('taxonomy_id', 'taxonomies', '{code} - {name}', ['status' => 1])
			->setRelation('topic_id', 'topics', '{code} - {name}', ['status' => 1])
			->setRelation('difficult_id', 'difficults', 'name', ['status' => 1], ['id' => 'asc'])
			->setRelation('user_id', 'users', '{name}')
			->setRelation('question_mark_id', 'question_marks', '{name} - {value}', ['status' => 1], ['id' => 'asc'])
            ->setDependentRelation('competency_id', 'subject_id', 'subject_id')
            ->setDependentRelation('taxonomy_id', 'competency_id', 'competency_id')
			->setDependentRelation('topic_id', 'taxonomy_id', 'taxonomy_id')
            ->setTexteditor([
                'pre_content', 
                'content', 
                'post_content'
            ])
            ->fieldType('answer_type', 'dropdown_search',[
                'INTEGER_NUMBER' => 'Dạng số NGUYÊN',
                'DOUBLE_NUMBER' => 'Dạng số THỰC',
                'UNORDER_LIST' => 'Dạng liệt kê KHÔNG thứ tự',
                'ORDER_LIST' => 'Dạng liệt kê CÓ thứ tự',
                'COORDINATE' => 'Dạng TỌA ĐỘ',
                'MIX' => 'Dạng hỗn hợp chữ số',
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('matrix_location', 'dropdown_search', $multi_select_50) 
            ->displayAs([
                'uuid' => 'UUID',
                'code' => 'Mã',
                'question_type_id' => 'Kiểu câu hỏi',
                'cloze_id' => 'Câu hỏi ngữ cảnh',
                'subject_id' => 'Môn',
                'competency_id' => 'NLKT',
                'taxonomy_id' => 'NDKT',
                'topic_id' => 'ĐVKT',
                'difficult_id' => 'Độ khó',
                'pre_content' => 'Nội dung trước',
                'content' => 'Nội dung chính',
                'post_content' => 'Gợi ý, tham khảo',
                'option_1' => 'Lựa chọn 1 (A)',
                'option_2' => 'Lựa chọn 2 (B)',
                'option_3' => 'Lựa chọn 3 (C)',
                'option_4' => 'Lựa chọn 4 (D)',
                'option_5' => 'Lựa chọn 1 (E)',
                'option_6' => 'Lựa chọn 2 (F)',
                'option_7' => 'Lựa chọn 3 (G)',
                'option_8' => 'Lựa chọn 4 (H)',
                'answer_type' => 'Kiểu đáp án',
                'answer_key' => 'Đáp án',
                'question_mark_id' => 'Điểm',
                'matrix_location' => 'Vị trí ma trận',
                'order' => 'Thứ tự',
                'no_options' => 'Số lượng phương án',
                'is_shuffled' => 'Đảo phương án',
                'no_used' => 'Số lần sử dụng',
                'user_id' => 'Người tạo',
                'status' => 'Trạng thái',
                'created_at' => 'Tạo',
                'updated_at' => 'Cập nhật lần cuối',
                'deleted_at' => 'Xóa'
            ])
            ->defaultOrdering('status', 'desc')
            ->setRead()
			->unsetReadFields(['deleted_at'])
            ->unsetAddFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
            ->setActionButton('Xem trước', 'mif-eye', function ($row) {
				return url('qbank/preview/'. $row->id);
			}, true)
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            $data['question_mark_id'] = 1;
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) use ($type_id) {
            $stateParameters->data['created_at'] = now();
            //(string) Uuid::uuid4()
            $stateParameters->data['uuid'] = (string) Uuid::uuid4();
            $stateParameters->data['question_type_id'] = $type_id;
            $stateParameters->data['question_store_id'] = 6;
            $stateParameters->data['user_id'] = $this->user->id;
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
        return $this->_example_output($heading, $output, $title, $cat, $subcat);
    }

    public function questionLNN($cloze_id = 0, $type = 'LNN')
    {
        $title = 'Quản lý ngân hàng câu hỏi';
        $cat = 'qbank';
        $subcat = $type;
        $table = _QUESTION;
        $heading = '';
        $q_type = QuestionType::where('code',$type)->first();
        $type_id = $q_type?$q_type->id:0;

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
            ->where("questions.question_type_id = $type_id AND cloze_id IS NULL")
            ->setSubject('Câu hỏi tự luận', 'Câu hỏi tự luận')
            ->columns(['code', 'matrix_location', 'subject_id','topic_id', 'content', 'max_words', 'no_used','status'])
            ->fields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                // 'pre_content', 
                'content', 
                // 'post_content', 
                // 'no_options', 
                // 'is_shuffled', 
                'question_mark_id', 
                'max_words', 
                // 'min_words', 
                // 'option_1', 
                // 'option_2', 
                // 'option_3', 
                // 'option_4', 
                // 'option_5', 
                // 'option_6', 
                // 'option_7', 
                // 'option_8',
                'status'
            ])
            ->requiredFields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                // 'pre_content', 
                'content', 
                // 'post_content', 
                // 'no_options', 
                // 'is_shuffled', 
                // 'question_mark_id', 
                'max_words', 
                // 'min_words', 
            ])
            ->setRelation('subject_id', 'subjects', '{code} - {name}', ['status' => 1], ['id' => 'asc'])
            ->setRelation('competency_id', 'competencies', '{code} - {name}')
			->setRelation('taxonomy_id', 'taxonomies', '{code} - {name}', ['status' => 1])
			->setRelation('topic_id', 'topics', '{code} - {name}', ['status' => 1])
			->setRelation('difficult_id', 'difficults', 'name', ['status' => 1], ['id' => 'asc'])
			->setRelation('user_id', 'users', '{name}')
			->setRelation('question_mark_id', 'question_marks', '{name} - {value}', ['status' => 1], ['id' => 'asc'])
            ->setDependentRelation('competency_id', 'subject_id', 'subject_id')
            ->setDependentRelation('taxonomy_id', 'competency_id', 'competency_id')
			->setDependentRelation('topic_id', 'taxonomy_id', 'taxonomy_id')
            ->setTexteditor([
                'pre_content', 
                'content', 
                'post_content'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('matrix_location', 'dropdown_search', $multi_select_50) 
            ->displayAs([
                'uuid' => 'UUID',
                'code' => 'Mã',
                'question_type_id' => 'Kiểu câu hỏi',
                'cloze_id' => 'Câu hỏi ngữ cảnh',
                'subject_id' => 'Môn',
                'competency_id' => 'NLKT',
                'taxonomy_id' => 'NDKT',
                'topic_id' => 'ĐVKT',
                'difficult_id' => 'Độ khó',
                'pre_content' => 'Nội dung trước',
                'content' => 'Nội dung chính',
                'post_content' => 'Gợi ý, tham khảo',
                'max_words' => 'Số chữ tối đa',
                'answer_type' => 'Kiểu đáp án',
                'answer_key' => 'Đáp án',
                'question_mark_id' => 'Điểm',
                'matrix_location' => 'Vị trí ma trận',
                'order' => 'Thứ tự',
                'no_used' => 'Số lần sử dụng',
                'user_id' => 'Người tạo',
                'status' => 'Trạng thái',
                'created_at' => 'Tạo',
                'updated_at' => 'Cập nhật lần cuối',
                'deleted_at' => 'Xóa'
            ])
            ->defaultOrdering('status', 'desc')
            ->setRead()
			->unsetReadFields(['deleted_at'])
            ->unsetAddFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
            ->setActionButton('Xem trước', 'mif-eye', function ($row) {
				return url('qbank/preview/'. $row->id);
			}, true)
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            $data['question_mark_id'] = 1;
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) use ($type_id) {
            $stateParameters->data['created_at'] = now();
            //(string) Uuid::uuid4()
            $stateParameters->data['uuid'] = (string) Uuid::uuid4();
            $stateParameters->data['question_type_id'] = $type_id;
            $stateParameters->data['question_store_id'] = 6;
            $stateParameters->data['user_id'] = $this->user->id;
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
        return $this->_example_output($heading, $output, $title, $cat, $subcat);
    }

    public function questionTNC($cloze_id = 0, $type = 'TNC', $no_options = 4)
    {
        $title = 'Quản lý ngân hàng câu hỏi';
        $cat = 'qbank';
        $subcat = $type;
        $table = _QUESTION;
        $heading = '';
        $q_type = QuestionType::where('code',$type)->first();
        $type_id = $q_type?$q_type->id:0;
        // dd($type_id);
        $options = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D'
        );
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
            ->where("questions.question_type_id = $type_id AND cloze_id IS NULL")
            ->setSubject('TNC Câu hỏi ngữ cảnh', 'Câu hỏi trắc nghiệm theo ngữ cảnh')
            ->columns(['code', 'matrix_location', 'no_options', 'subject_id','topic_id', 'content', 'no_used','status'])
            ->fields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                'pre_content', 
                'content', ])
            ->requiredFields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                'content'
                ])
            ->setRelation('subject_id', 'subjects', '{code} - {name}', ['status' => 1], ['id' => 'asc'])
            ->setRelation('competency_id', 'competencies', '{code} - {name}')
			->setRelation('taxonomy_id', 'taxonomies', '{code} - {name}', ['status' => 1])
			->setRelation('topic_id', 'topics', '{code} - {name}', ['status' => 1])
			->setRelation('difficult_id', 'difficults', 'name', ['status' => 1], ['id' => 'asc'])
			->setRelation('user_id', 'users', '{name}')
            ->setDependentRelation('competency_id', 'subject_id', 'subject_id')
            ->setDependentRelation('taxonomy_id', 'competency_id', 'competency_id')
			->setDependentRelation('topic_id', 'taxonomy_id', 'taxonomy_id')
            ->setTexteditor([
                'pre_content', 
                'content', 
                'post_content'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('matrix_location', 'dropdown_search', $multi_select_50)
            ->displayAs([
                'uuid' => 'UUID',
                'code' => 'Mã',
                'question_type_id' => 'Kiểu câu hỏi',
                'cloze_id' => 'Câu hỏi ngữ cảnh',
                'subject_id' => 'Môn',
                'competency_id' => 'NLKT',
                'taxonomy_id' => 'NDKT',
                'topic_id' => 'ĐVKT',
                'difficult_id' => 'Độ khó',
                'pre_content' => 'Nội dung trước',
                'content' => 'Nội dung chính',
                'post_content' => 'Gợi ý, tham khảo',
                'option_1' => 'Lựa chọn 1 (A)',
                'option_2' => 'Lựa chọn 2 (B)',
                'option_3' => 'Lựa chọn 3 (C)',
                'option_4' => 'Lựa chọn 4 (D)',
                'answer_type' => 'Kiểu đáp án',
                'answer_key' => 'Đáp án',
                'question_mark_id' => 'Điểm',
                'matrix_location' => 'Vị trí ma trận',
                'order' => 'Thứ tự',
                'no_options' => 'Số câu hỏi phụ',
                'no_used' => 'Số lần sử dụng',
                'user_id' => 'Người tạo',
                'status' => 'Trạng thái',
                'created_at' => 'Tạo',
                'updated_at' => 'Cập nhật lần cuối',
                'deleted_at' => 'Xóa'
            ])
            ->defaultOrdering('status', 'desc')
            ->setRead()
			->unsetReadFields(['deleted_at'])
            ->unsetAddFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
            ->setActionButton('Xem trước', 'mif-eye', function ($row) {
				return url('qbank/preview/'. $row->id);
			}, true)
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            // $data['question_mark_id'] = 1;
            return $data;
        });

        $crud->callbackColumn('no_options', function ($value, $row) {
            $questions = Question::where('cloze_id', $row->id)->get();
            $quantity = count($questions);
            return "
                    <div class=\"cl_current_questions \"><span class=\"cl_current_question_not_equals bg-green fg-white\">Số câu hỏi hiện có: {$quantity}</span></div>
                    <div class=\"cl_create_sub_question mt-3\">
                        <a class=\"btn btn-outline-danger r20\" href=\"" . url('qbank/question/TNC-detail/'. $row->id) . "\">Quản trị câu hỏi phụ</a>
                    </div>";
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) use ($type_id, $no_options) {
            $stateParameters->data['created_at'] = now();
            $stateParameters->data['uuid'] = (string) Uuid::uuid4();
            $stateParameters->data['question_type_id'] = $type_id;
            $stateParameters->data['question_store_id'] = 6;
            $stateParameters->data['no_options'] = $no_options;
            $stateParameters->data['user_id'] = $this->user->id;
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
        return $this->_example_output($heading, $output, $title, $cat, $subcat);
    }

    public function questionTNCDetails($cloze_id = 0, $type = 'TN1', $no_options = 4)
    {
        $title = 'Quản lý ngân hàng câu hỏi';
        $cat = 'qbank';
        $subcat = $type;
        $table = _QUESTION;
        $heading = '';
        $q_type = QuestionType::where('code',$type)->first();
        $type_id = $q_type?$q_type->id:0;
        $context_question = Question::find($cloze_id);
        $heading = $context_question->content;
        // dd($heading);
        $options = array(
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D'
        );
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
            ->where("questions.cloze_id = $cloze_id")
            ->setSubject('Câu hỏi thành phần ngữ cảnh', 'Câu hỏi trắc nghiệm 4 phương án lựa chọn, 1 phương án đúng')
            ->columns(['code', 'matrix_location', 'context_order', 'subject_id','topic_id', 'content', 'is_shuffled', 'no_used','status'])
            ->fields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'competency_id',
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                'question_type_id', 
                'context_order',
                // 'pre_content', 
                'content', 
                // 'post_content', 
                'no_options', 
                'max_words', 
                'is_shuffled', 
                'question_mark_id', 
                'answer_key', 
                'option_1', 
                'option_2', 
                'option_3', 
                'option_4', 
                // 'option_5', 
                // 'option_6', 
                // 'option_7', 
                // 'option_8',
                'status'
            ])
            ->requiredFields([
                'code', 
                'matrix_location', 
                'subject_id', 
                'taxonomy_id', 
                'topic_id', 
                'difficult_id', 
                'question_type_id', 
                'context_order',
                // 'answer_key', 
                // 'question_mark_id',
                'content', 
                // 'option_1', 
                // 'option_2', 
                // 'option_3', 
                // 'option_4', 
            ])
            ->setRelation('subject_id', 'subjects', '{code} - {name}', ['status' => 1], ['id' => 'asc'])
            ->setRelation('competency_id', 'competencies', '{code} - {name}')
			->setRelation('taxonomy_id', 'taxonomies', '{code} - {name}', ['status' => 1])
			->setRelation('topic_id', 'topics', '{code} - {name}', ['status' => 1])
			->setRelation('difficult_id', 'difficults', 'name', ['status' => 1], ['id' => 'asc'])
			->setRelation('question_type_id', 'question_types', 'name', ['status' => 1], ['id' => 'asc'])
			->setRelation('user_id', 'users', '{name}')
			->setRelation('question_mark_id', 'question_marks', '{name} - {value}', ['status' => 1], ['id' => 'asc'])
            ->setDependentRelation('competency_id', 'subject_id', 'subject_id')
            ->setDependentRelation('taxonomy_id', 'competency_id', 'competency_id')
			->setDependentRelation('topic_id', 'taxonomy_id', 'taxonomy_id')
            ->setTexteditor([
                'pre_content', 
                'content', 
                'post_content', 
                'option_1', 
                'option_2', 
                'option_3', 
                'option_4'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('is_shuffled', 'dropdown_search',[
                1 => 'Đảo',
                0 => 'Không'
            ])
            ->fieldType('matrix_location', 'dropdown_search', $multi_select_50)
            ->fieldType('answer_key', 'dropdown_search', $options)
            ->fieldType('cloze_id', 'hidden')
            ->displayAs([
                'uuid' => 'UUID',
                'code' => 'Mã',
                'question_type_id' => 'Kiểu câu hỏi',
                'cloze_id' => 'Câu hỏi ngữ cảnh',
                'subject_id' => 'Môn',
                'competency_id' => 'NLKT',
                'taxonomy_id' => 'NDKT',
                'topic_id' => 'ĐVKT',
                'difficult_id' => 'Độ khó',
                'question_type_id' => 'Loại câu',
                'context_order' => 'Thứ tự trong câu ngữ cảnh',
                'pre_content' => 'Nội dung trước',
                'content' => 'Nội dung chính',
                'post_content' => 'Gợi ý, tham khảo',
                'option_1' => 'Lựa chọn 1 (A)',
                'option_2' => 'Lựa chọn 2 (B)',
                'option_3' => 'Lựa chọn 3 (C)',
                'option_4' => 'Lựa chọn 4 (D)',
                'option_5' => 'Lựa chọn 1 (E)',
                'option_6' => 'Lựa chọn 2 (F)',
                'option_7' => 'Lựa chọn 3 (G)',
                'option_8' => 'Lựa chọn 4 (H)',
                'answer_type' => 'Kiểu đáp án',
                'answer_key' => 'Đáp án',
                'question_mark_id' => 'Điểm',
                'matrix_location' => 'Vị trí ma trận',
                'order' => 'Thứ tự',
                'no_options' => 'Số lượng phương án',
                'max_words' => 'Số chữ tối đa',
                'is_shuffled' => 'Đảo phương án',
                'no_used' => 'Số lần sử dụng',
                'user_id' => 'Người tạo',
                'status' => 'Trạng thái',
                'created_at' => 'Tạo',
                'updated_at' => 'Cập nhật lần cuối',
                'deleted_at' => 'Xóa'
            ])
            ->defaultOrdering('matrix_location', 'asc')
            ->setRead()
			->unsetReadFields(['deleted_at'])
            ->unsetAddFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['uuid', 'no_used', 'user_id', 'created_at', 'updated_at', 'deleted_at'])
            ->setActionButton('Xem trước', 'mif-eye', function ($row) {
				return url('qbank/preview/'. $row->id);
			}, true)
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) use ($cloze_id) {
            $data['status'] = 1;
            $data['question_mark_id'] = 1;
            $data['no_options'] = 4;
            $data['max_words'] = 0;
            $question = Question::find($cloze_id);
            $data['matrix_location'] = isset($question)?$question->matrix_location:0;
            $data['subject_id'] = $question['subject_id'];
            $data['competency_id'] = $question['competency_id'];
            $data['taxonomy_id'] = $question['taxonomy_id'];
            $data['topic_id'] = $question['topic_id'];
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) use ($cloze_id, $type_id, $no_options) {
            $stateParameters->data['created_at'] = now();
            //Kiểm tra các phương án trùng nhau
            // $q_options = array(
            //     md5($stateParameters->data['option_1']), 
            //     md5($stateParameters->data['option_2']), 
            //     md5($stateParameters->data['option_3']), 
            //     md5($stateParameters->data['option_4'])
            // );
            // if ($this->is_duplicated($q_options)) {
            //         $errorMessage = new \GroceryCrud\Core\Error\ErrorMessage();
            //         return $errorMessage->setMessage("Dữ liệu các phương án nhập vào đang có phương án trùng nhau. Hãy kiểm tra lại!\n");
            // }
            //(string) Uuid::uuid4()
            $stateParameters->data['uuid'] = (string) Uuid::uuid4();
            $stateParameters->data['cloze_id'] = $cloze_id;
            if($stateParameters->data['subject_id'] != 5) {
                $stateParameters->data['question_type_id'] = $type_id;
                $stateParameters->data['max_words'] = 0;
            }
            $stateParameters->data['question_store_id'] = 6;
            $stateParameters->data['no_options'] = $no_options;
            $stateParameters->data['user_id'] = $this->user->id;
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
        return $this->_example_output($heading, $output, $title, $cat, $subcat);
    }

    public function questionPreview($question_id){
        $question = Question::find($question_id);
        $sub_questions = [];
        switch($question->question_type_id){
            case 20:
                $sub_questions = Question::where('cloze_id',$question->id)->get();
                break;
            default:
        }

        return view('pages.question-preview',[
            'question' => $question,
            'sub_questions' => $sub_questions,
            'pre_content' => html_entity_decode($question->pre_content),
            'content' => html_entity_decode($question->content),
        ]);
    }
}
