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
use App\Models\Monitor;
use App\Models\Council;
use App\Models\CouncilTurn;
use App\Models\CouncilTurnRoom;
use App\Models\Examinee;
use App\Models\ExamineeAnswer;
use App\Models\ExamineeTestMix;
use App\Models\TestMix;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExamineeImport;
use App\Imports\RoomImport;
use App\Exports\MonitorExport;
use App\Models\ActivityLog;
use App\Models\CouncilTurnTestMix;
//service
use App\Services\PhpWordService;

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
define('_ORG','organizations');
define('_ROOM','rooms');
define('_MONITOR','monitors');
define('_COUNCIL','councils');
define('_TURN','council_turns');
define('_TURN_ROOM','council_turn_rooms');
define('_EXAMINEE','examinees');
define('_TURN_TEST','test_mixes');
define('_TURN_TEST_MIX','council_turn_test_mixes');

class CouncilController extends Controller
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
        return view('pages.council_crud_template', [
            'title' => $title,
            'cat' => $cat,
            'sub_cat' => $subcat,
            'data' => $data,
            'output' => $output,
            'css_files' => $css_files,
            'js_files' => $js_files
        ]);
    }

    private function getRandomString($n){
        $characters = '0123456789';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = random_int(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public function __construct()
	{
		//parent::__construct();
		// Your own constructor code
		$this->database = $this->_getDatabaseConnection();
        // dd($this->database);
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
     * Organization management
     */
    public function organization()
    {
        $title = 'Địa điểm thi';
        $cat = 'config';
        $subcat = 'org';
        $table = _ORG;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'name', 'address','status'])
            ->setRead()
            ->addFields(['code', 'name', 'address','status'])
            ->setClone()
            ->cloneFields(['code', 'name', 'address','status'])
            ->editFields(['code', 'name', 'address','status'])
            ->requiredFields(['code', 'name'])
            ->displayAs([
                'code' => 'Mã',
                'name' => 'Tên',
                'address' => 'Địa chỉ',
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
     * room management
     */
    public function room()
    {
        $title = 'Phòng thi';
        $cat = 'config';
        $subcat = 'room';
        $table = _ROOM;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('rooms.deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['organization_code', 'code', 'name', 'desc', 'no_slots', 'status'])
            ->setRead()
            ->addFields(['organization_code', 'code', 'name', 'desc', 'no_slots', 'status'])
            ->setClone()
            ->cloneFields(['organization_code', 'code', 'name', 'desc', 'no_slots', 'status'])
            ->editFields(['organization_code', 'code', 'name', 'desc', 'no_slots', 'status'])
            ->requiredFields(['organization_code', 'code', 'name', 'no_slots'])
            ->displayAs([
                'code' => 'Mã phòng',
                'name' => 'Tên phòng',
                'desc' => 'Diễn giải',
                'no_slots' => 'Số máy',
                'organization_code' => 'Địa điểm thi',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->setPrimaryKey('code', 'organizations')
            ->setRelation('organization_code', 'organizations', '{code} - {name}')
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
        
        // return $this->_example_output($output, $title, $cat, $subcat);
        $form_url = url('/exam/import-room/');
        $organizations = Organization::where('status',1)->get();
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'form_url' => $form_url,
            'organizations' => $organizations,
            'data' => 'room_data'
        ]);
    }

    /**
     * monitor account management
     */
    public function monitor()
    {
        $title = 'Tài khoản';
        $cat = 'config';
        $subcat = 'monitor';
        $table = _MONITOR;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where('monitors.deleted_at IS NULL AND monitors.role_id <> 1 AND monitors.role_id <> 8')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['code', 'password' , 'name', 'role_id', 'organization_id', 'status'])
            ->setRead()
            ->addFields(['code', 'name', 'password', 'role_id', 'organization_id', 'status'])
            ->setClone()
            ->cloneFields(['code', 'name', 'password', 'role_id', 'organization_id', 'status'])
            ->editFields(['code', 'name', 'password', 'role_id', 'organization_id', 'status'])
            ->requiredFields(['code', 'name', 'role_id'])
            ->displayAs([
                'code' => 'Tên tài khoản',
                'name' => 'Họ và tên',
                'password' => 'Mật khẩu',
                'role_id' => 'Vai trò',
                'organization_id' => 'Nơi công tác',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('password', 'password')
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('user_id', 'hidden')
            ->setRelation('role_id', 'roles', 'desc')
            ->setRelation('organization_id', 'organizations', 'name')
			->unsetReadFields(['deleted_at'])
			->unsetAddFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            $data['organization_id'] = 1;
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            if(!$stateParameters->data['password']){
                $stateParameters->data['password'] = $this->getRandomString(6);
            }
            $user = User::create([
                'name' => $stateParameters->data['name'],
                'email' => $stateParameters->data['code'],
                'email_verified_at' => now(),
                'password' => Hash::make($stateParameters->data['password']),
                'remember_token' => Str::random(10),
                'status' => 1
            ]);
            $user->roles()->attach($stateParameters->data['role_id']);
            $stateParameters->data['user_id'] = $user->id;
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            User::where('id',$stateParameters->data['user_id'])
                ->update([
                    'name' => $stateParameters->data['name'],
                    'email' => $stateParameters->data['code'],
                    'password' => Hash::make($stateParameters->data['password']),
                ]);
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
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
     * council management
     */
    public function council()
    {
        $title = 'Hội đồng thi';
        $cat = 'exam';
        $subcat = 'council';
        $table = _COUNCIL;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            // ->where('councils.deleted_at IS NULL')
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'organization_code', 
                'code', 
                'desc', 
                'no_turns', 
                'start_at', 
                'finish_at', 
                'monitor_id', 
                'is_autostart', 
                'status'
            ])
            // ->setRead()
            ->addFields([
                'organization_code', 
                'code', 
                'desc', 
                'no_turns', 
                'start_at', 
                'finish_at', 
                'monitor_id', 
                'is_autostart', 
                'import_testdata_before_time',
                'status'
                ])
            ->editFields(['organization_code', 'code', 'desc', 'start_at', 'finish_at', 'monitor_id', 'is_autostart', 'import_testdata_before_time', 'status'])
            ->requiredFields(['organization_code', 'code', 'start_at', 'finish_at', 'monitor_id'])
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
            // ->setPrimaryKey('code', 'monitors')
            ->setRelation('monitor_id', 'monitors', 'name', ['role_id' => 7])
			->unsetAddFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
            ->unsetDelete()
			->unsetPrint()->unsetExport();
        
        $crud->callbackColumn('no_turns', function ($value, $row) {
            return "
            <div class=\"gc-data-container-text\">Số lượng ca thi: " . $value . "</div><br>
            <div class=\"gc-data-container-text\">
                <div class=\"cl_manage_parts\">
                    <a class=\"btn btn-outline-dark r20\" href=\"" . url('exam/council-turns/' . $row->code) . "\">Quản trị các ca thi</a>
                </div>
            </div>
            ";
        });

        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            $data['is_autostart'] = 0;
            $data['import_testdata_before_time'] = 60;
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackAfterInsert(function ($stateParameters) {
            $data = $stateParameters->data;
            for($i = 1; $i <= $data['no_turns']; $i++){
                CouncilTurn::create([
                    'code' => $data['code'] . "_" . ($i),
                    'name' => "Ca thi " . $i,
                    'start_at' => $data['start_at'],
                    'council_code' => $data['code'],
                ]);
            }
            
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
            DB::table($table)
                ->where('code', $stateParameters->primaryKeyValue)
                ->update(['status' => 0]);
            DB::table($table)
                ->where('code', $stateParameters->primaryKeyValue)
                ->delete();
            return $stateParameters;
        });

        $crud->setActionButton('Export', 'fa fa-download', function ($row) {
            return url('/exam/export-council/' . $row->id);
        }, true);
        // $crud->setActionButton('Xuất CBCT', 'fa fa-users', function ($row) {
        //     return '/export/docx/monitor-council/' . $row->id;
        // }, true);
        $crud->setActionButton('Xuất CBCT', 'fa fa-file-excel-o', function ($row) {
            return url('/export/xlsx/monitor-council/' . $row->id);
        }, true);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        // return $this->_example_output($output, $title, $cat, $subcat);

        $form_url = url('/exam/import-council');
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'form_url' => $form_url,
            'data' => 'council_data'
        ]);
    }

    /**
     * council turns management
     */
    public function councilTurn($council_code = '')
    {
        $title = 'Ca thi';
        $cat = 'exam';
        $subcat = 'council';
        $table = _TURN;
        // DB::connection()->enableQueryLog();
        $council = Council::where('code',$council_code)->first();
        // $council_turns = CouncilTurn::where('council_code',$council_code)->whereNowOrFuture('start_at')->get();
        // $queries = DB::getQueryLog();
        // dd($council_turns);
        $organization = Organization::where('code',$council->organization_code)->first();
        $title .= ' - Hội đồng thi ' . $council->code . ' - ' . $council->desc;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['council_code' => $council_code, 'status' => 1])
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'code', 
                'name', 
                'no_rooms', 
                'rooms', 
                'start_at', 
                // 'is_active', 
                // 'status'
            ])
            ->addFields(['code', 'name', 'start_at', 'rooms', 'status'])
            ->editFields(['code', 'name', 'start_at', 'rooms', 'status'])
            ->requiredFields(['code', 'name', 'start_at', 'status'])
            ->displayAs([
                'council_code' => 'HĐ thi',
                'code' => 'Mã ca thi',
                'name' => 'Ca thi',
                'desc' => 'Tên ca thi',
                'no_rooms' => 'Số phòng thi',
                'rooms' => 'Phòng thi',
                'start_at' => 'Ngày giờ thi',
                'is_active' => 'Bắt đầu thi',
                'status' => 'Trạng thái',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('is_active', 'dropdown_search',[
                1 => 'Dã thi',
                0 => 'Chưa thi'
            ])
            ->fieldType('status', 'dropdown_search',[
                1 => 'Sử dụng',
                0 => 'Ẩn'
            ])
            ->fieldType('start_at','datetime')
            ->setPrimaryKey('code', 'rooms')
            // ->setPrimaryKey('code', 'council_turns')
            ->setRelationNtoN('rooms', 'council_turn_rooms', 'rooms', 'council_turn_code', 'room_code', 'code')
			->unsetAdd()
			->unsetDelete()
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetPrint()->unsetExport();
        
        $crud->setActionButton('Sao lưu', 'fa fa-download', function ($row) {
            return url('/exam/export-council-turn/' . $row->code);
        }, true);
        // $crud->setActionButton('Dọn dẹp', 'fa fa-trash', function ($row) {
        //     return '/exam/flush-council-turn/' . $row->code;
        // }, true);
        $crud->setActionButton('Dọn dẹp', 'fa fa-trash', function ($row) {
            $url = url('exam/flush-council-turn/' . $row->code);
            return "javascript:if(confirm('Bạn có chắc chắn muốn dọn dẹp kỳ {$row->code}?')){ window.location.href='$url'; }";
        }, false);
        $crud->callbackColumn('no_rooms', function ($value, $row) use($council) {
            $html = "<div class=\"gc-data-container-text\">Số lượng phòng thi: " . $value . "</div><br>";
            $html .= "<div class=\"gc-data-container-text\">Giờ hiện tại: " . now() . "</div><br>";
            $now = now();
            if($row['start_at'] < $now ){
                //Đã qua thời gian bđ của ca thi
                if(strtotime($row['start_at']) + (90 * 60) < time()){
                    $html .= "<div class=\"gc-data-container-text my-3\">
                        <div class=\"cl_manage_parts\">
                            <a class=\"btn btn-outline-secondary r20\" href=\"#\">Đã thi xong</a>
                        </div>
                    </div>";
                }else{
                    $html .= "<div class=\"gc-data-container-text my-3\">
                        <div class=\"cl_manage_parts\">
                            <a class=\"btn btn-outline-success r20\" href=\"#\">Đang thi</a>
                        </div>
                    </div>";
                }
                
            }elseif(strtotime($row['start_at']) > (time() + ($council->import_testdata_before_time * 60))){
                //Còn quá sớm để đổ đề
                $html .= "<div class=\"gc-data-container-text my-3\">
                    <div class=\"cl_manage_parts\">
                        <a class=\"btn btn-outline-warning r20\" href=\"#\">Chưa đến thời gian thi</a>
                    </div>
                </div>";
            }else{
                $html .= "<div class=\"gc-data-container-text my-3\">
                    <div class=\"cl_manage_parts\">
                        <a class=\"btn btn-outline-danger r20\" target=\"_blank\" href=\"" . url('exam/council-turn-test-mixes/' . $row->code) . "\">Nhận Đề thi</a>
                    </div>
                </div>";
            }
            $html .= "<div class=\"gc-data-container-text my-3\">
                <div class=\"cl_manage_parts\">
                    <a class=\"btn btn-outline-dark r20\" href=\"" . url('exam/council-turn-rooms/' . $row->code) . "\">Quản trị các phòng thi</a>
                </div>
            </div>
            ";
            return $html;
        });

        $crud->callbackColumn('rooms', function ($value, $row) use ($council_code){
            if($value){
                return "<div class=\"gc-data-container-text\">" . $value . "</div>";
            }else{
                return "
                <div class=\"gc-data-container-text bg-warning\">Chưa gán phòng thi</div><br>
                <a class=\"btn btn-outline-dark r20\" href=\"" . url('exam/council-turns/' . $council_code . '#/edit/' . $row->code) . "\">Thiết lập ca thi</a>
                ";
            }
        });

        // $crud->setActionButton('Xuất CBCT', 'fa fa-download', function ($row) {
        //     return '/exam/export-council/' . $row->code;
        // }, true);

        $crud->callbackAddForm(function ($data) use ($council) {
            $data['status'] = 1;
            $data['is_active'] = 0;
            $data['code'] = $council->code . '_';
            $data['council_code'] = $council->code;
            $data['start_at'] = $council->start_at;
            return $data;
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackAfterInsert(function ($stateParameters) use($organization) {
            $data = $stateParameters->data;
            Council::where('code',$data['council_code'])->update([
                'no_turns' => CouncilTurn::where('council_code',$data['council_code'])->count() 
            ]);
            $council_turn_rooms = CouncilTurnRoom::where('council_turn_code',$data['code'])->get();
            CouncilTurn::where('code',$data['code'])->update([
                'no_rooms' => CouncilTurnRoom::where('council_turn_code',$data['code'])->count()
            ]);

            //gán CBCT vào các phòng thi
            $council_turn_rooms = CouncilTurnRoom::where('council_turn_code',$data['code'])
                                                ->whereNull('monitor_code')
                                                ->get();
            foreach($council_turn_rooms as $item){
                if($item->monitor_code) continue;
                $i = 0;
                $monitor_code = null;
                do{
                    if($i == Monitor::where('organization_id',$organization->id)->count()) break;
                    //lấy ngẫu nhiên 1 giám thị
                    $monitor = Monitor::where('role_id',4)
                                    ->where('organization_id',$organization->id)
                                    ->inRandomOrder()
                                    ->first();
                    $monitor_code = $monitor->code;
                    //kiểm tra giám thị này đã được phân công trong ca thi hay chưa?
                    $check = CouncilTurnRoom::where('monitor_code',$monitor_code)
                                            ->where('council_turn_code',$data['code'])
                                            ->count();
                }while($check); //nếu đã phân công rồi thì tìm giám thị khác
                if($monitor_code){
                    //gán giám thị vào phòng thi
                    $item->monitor_code = $monitor_code;
                    $item->save();
                    //đổi mật khẩu cho giám thị
                    // $password = $this->getRandomString(6);
                    // Monitor::where('code',$monitor_code)
                    //     ->update(['password' => $password]);
                    // User::where('email',$monitor_code)
                    //     ->update(['password' => Hash::make($password)]);
                }
            }

            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            $stateParameters->data['updated_at'] = now();
            // $stateParameters->pk_value = $stateParameters->data['code'];
            return $stateParameters;
        });
        
        $crud->callbackAfterUpdate(function ($stateParameters) use($organization) {
            $data = $stateParameters->data;
            $council_turn_rooms = CouncilTurnRoom::where('council_turn_code',$data['code'])->get();
            CouncilTurn::where('code',$data['code'])->update([
                'no_rooms' => count($council_turn_rooms)
            ]);

            //gán CBCT vào các phòng thi
            $council_turn_rooms = CouncilTurnRoom::where('council_turn_code',$data['code'])
                                                ->whereNull('monitor_code')
                                                ->get();
            foreach($council_turn_rooms as $item){
                if($item->monitor_code) continue;
                $i = 0;
                $monitor_code = null;
                do{
                    if($i == Monitor::where('organization_id',$organization->id)->count()) break;
                    //lấy ngẫu nhiên 1 giám thị
                    $monitor = Monitor::where('role_id',4)
                                    ->where('organization_id',$organization->id)
                                    ->inRandomOrder()
                                    ->first();
                    $monitor_code = $monitor->code;
                    //kiểm tra giám thị này đã được phân công trong ca thi hay chưa?
                    $check = CouncilTurnRoom::where('monitor_code',$monitor_code)
                                            ->where('council_turn_code',$data['code'])
                                            ->count();
                }while($check); //nếu đã phân công rồi thì tìm giám thị khác
                if($monitor_code){
                    //gán giám thị vào phòng thi
                    $item->monitor_code = $monitor_code;
                    $item->save();
                    //đổi mật khẩu cho giám thị
                    // $password = $this->getRandomString(6);
                    // Monitor::where('code',$monitor_code)
                    //     ->update(['password' => $password]);
                    // User::where('email',$monitor_code)
                    //     ->update(['password' => Hash::make($password)]);
                }
            }

            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
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
     * council turn test mixes management
     */
    public function councilTurnTestMixes($code = '')
    {
        $title = 'Đề thi';
        $cat = 'exam';
        $subcat = 'council';
        // $table = _TURN_TEST;
        $table = _TURN_TEST_MIX;

        $council_turn = CouncilTurn::where('code',$code)->first();
        $title .= ' - ' . $council_turn->name . ' - ' . $council_turn->code;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['council_turn_code' => $code])
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'test_mix_id', 
                // 'council_code', 
                // 'council_turn_code', 
                'subject_id', 
                'used_time', 
                'is_used'
            ])
            ->displayAs([
                'test_mix_id' => 'Mã đề',
                'council_code' => 'HĐ thi',
                'council_turn_code' => 'Ca thi',
                'subject_id' => 'Môn thi',
                'used_time' => 'Thời gian sử dụng',
                'is_used' => 'Tình trạng'
            ])
            ->fieldType('is_used', 'dropdown_search',[
                1 => 'Đã thi',
                0 => 'Chưa thi'
            ])
            ->setRelation('subject_id', 'subjects', 'desc')
			->unsetAdd()
			->unsetEdit()
			->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->callbackColumn('test_mix_id', function ($value, $row) {
            if($value){
                $test_mix = TestMix::find($value);
                return "
                <div class=\"gc-data-container-text\">" . $test_mix->code . "</div>
                ";
            }else{
                return "
                <div class=\"gc-data-container-text bg-warning\">Chưa gán đề</div><br>";
            }
        });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();

        $form_url = '/test/import-testdata/' . $code;
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'form_url' => $form_url,
            'code' => $code,
            'data' => 'test_data'
        ]);
    }

    /**
     * council turn rooms management
     */
    public function councilTurnRoom($code = '')
    {
        $title = 'Phòng thi';
        $cat = 'exam';
        $subcat = 'council';
        $table = _TURN_ROOM;

        $council_turn = CouncilTurn::where('code',$code)->first();
        $title .= ' - ' . $council_turn->name . ' - ' . $council_turn->code;

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where(['council_turn_code' => $code])
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns(['room_code', 'council_turn_code', 'monitor_code', 'is_active'])
            ->displayAs([
                'room_code' => 'Phòng thi',
                'council_turn_code' => 'Ca thi',
                'monitor_code' => 'Cán bộ coi thi',
                'is_active' => 'Bắt đầu thi'
            ])
            ->fieldType('is_active', 'dropdown_search',[
                1 => 'Đã kích hoạt',
                0 => 'Chưa kích hoạt'
            ])
            ->setRelation('monitor_code', 'monitors', 'code')
			->unsetAdd()
			->unsetEdit()
			->unsetDelete()
			->unsetPrint()->unsetExport();

        // $crud->setActionButton('Đổi mật khẩu', 'fa fa-key', function ($row) {
        //     return '/view_avatar/' . $row->council_turn_code . '/' . $row->room_code;
        // }, true);
        $crud->setActionButtonMultiple('Kích hoạt', 'fa fa-unlock', url('/exam/active-rooms'), false);
        $crud->setActionButton('Kích hoạt', 'fa fa-unlock', function ($row) use ($code) {
            if($row->is_active) return url('/exam/council-turn-rooms/' . $code);
            else return url('/exam/active-rooms?id[]=' . $row->id);
        }, false);
        $crud->setActionButton('Xem DS thí sinh', 'fa fa-users', function ($row) {
            return url('/exam/examinee?turn=' . $row->council_turn_code . '&room=' . $row->room_code);
        }, true);
        $crud->setActionButton('Xuất phiếu TK', 'fa fa-file-word-o', function ($row) {
            return url('/export/docx/examinee?turn=' . $row->council_turn_code . '&room=' . $row->room_code);
        }, true);

        $crud->callbackColumn('monitor_code', function ($value, $row) {
            if($value){
                $monitor = Monitor::where('code',$value)->first();
                return "
                <div class=\"gc-data-container-text\">" . $monitor->name . "</div><br>
                <div class=\"gc-data-container-text\">Tài khoản: " . $value . " / " . $monitor->password . "</div>
                ";
            }else{
                return "
                <div class=\"gc-data-container-text bg-warning\">Chưa gán CBCT</div><br>";
            }
        });

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();
        
        return $this->_example_output($output, $title, $cat, $subcat);
    }

    /**
     * examinee account management
     */
    public function examinee(Request $request)
    {
        $title = 'Tài khoản thí sinh';
        $cat = 'management';
        $subcat = 'examinee';
        $table = _EXAMINEE;
        $turn_code = $room_code = null;

        $where_clause = 'examinees.role_id = 8';
        if($request->query('turn')) {
            $turn_code = $request->query('turn');
            $where_clause .= " AND examinees.council_turn_code = '" . $turn_code . "'";
        }
        if($request->query('room')) {
            $room_code = $request->query('room');
            $where_clause .= " AND examinees.room_code = '" . $room_code . "'";
        }

        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where($where_clause)
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'status', 
                'code', 
                'user_id', 
                'password', 
                // 'id_card_number', 
                'lastname', 
                'firstname', 
                // 'birthday', 
                'subject_id', 
                // 'council_code', 
                'council_turn_code', 
                'room_code', 
                'is_backup'
                ])
            // ->setRead()
            ->addFields(['code', 'user_id', 'password', 'id_card_number', 'lastname', 'firstname', 'birthday', 'subject_id', 'council_code', 'council_turn_code', 'room_code', 'seat_number', 'is_backup', 'role_id'])
            // ->setClone()
            // ->cloneFields(['code', 'password', 'id_card_number', 'lastname', 'firstname', 'birthday', 'subject_id', 'council_code', 'council_turn_code', 'room_code', 'seat_number', 'is_backup', 'role_id'])
            ->editFields(['code', 'password', 'id_card_number', 'lastname', 'firstname', 'birthday', 'subject_id', 'council_code', 'council_turn_code', 'room_code', 'seat_number', 'is_backup', 'role_id', 'user_id'])
            ->requiredFields(['code', 'id_card_number', 'lastname', 'firstname', 'subject_id', 'council_code', 'council_turn_code', 'room_code'])
            ->displayAs([
                'code' => 'SBD',
                'user_id' => 'Tên tài khoản',
                'password' => 'Mật khẩu',
                'id_card_number' => 'Số CCCD',
                'lastname' => 'Họ lót',
                'firstname' => 'Tên',
                'birthday' => 'Ngày sinh',
                'subject_id' => 'Môn thi',
                'council_code' => 'HĐ thi',
                'council_turn_code' => 'Ca thi',
                'room_code' => 'Phòng thi',
                'seat_number' => 'Vị trí',
                'status' => 'IP',
                'is_backup' => 'Dự phòng',
                'role_id' => 'Vai trò',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('password', 'password')
            ->fieldType('is_backup', 'checkbox_boolean')
            ->fieldType('birthday', 'date')
            // ->fieldType('user_id', 'hidden')
            ->setRelation('user_id', 'users', 'email')
            ->setRelation('role_id', 'roles', 'desc')
            ->setRelation('subject_id', 'subjects', 'desc')
            ->setPrimaryKey('code', 'councils')
            ->setRelation('council_code', 'councils', 'code')
            ->setRelation('council_turn_code', 'council_turns', 'name')
            ->setPrimaryKey('code', 'rooms')
            ->setRelation('room_code', 'rooms', 'code')
            ->setDependentRelation('council_turn_code', 'council_code', 'council_code')
			->unsetReadFields(['deleted_at'])
			->unsetAddFields(['created_at', 'updated_at', 'deleted_at'])
			->unsetEditFields(['created_at', 'updated_at', 'deleted_at'])
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->callbackAddForm(function ($data) {
            $data['role_id'] = 8;
            $data['is_backup'] = 0;
            return $data;
        });

        $crud->callbackColumn('status', function ($value, $row) {
            $user = User::find($row->user_id);
            $status = "CHƯA ĐĂNG NHẬP";
            $status_bg = "bg-secondary";
            if($user && $user->ip_address){
                $examinee_test = ExamineeTestMix::where('examinee_account',$user->email)->first();
                if(!$examinee_test){
                    $status = "CHỜ THI";
                    $status_bg = "bg-warning";
                }else{
                    $status = "ĐANG THI";
                    $status_bg = "bg-success text-white";
                    
                    if($examinee_test->finish_time){
                        $status = "NỘP BÀI";
                        $status_bg = "bg-danger text-white";
                    }
                }
                return "
                <div class=\"gc-data-container-text\"><strong class=\"" . $status_bg . "\">" . $status . "</strong></div><br>
                <div class=\"gc-data-container-text\">IP: " . $user->ip_address . "</div>
                ";
            }else{
                return "
                <div class=\"gc-data-container-text\">" . $status . "</div>
                ";
            }
            
        });
        
        $crud->callbackBeforeInsert(function ($stateParameters) {
            if(!$stateParameters->data['password']){
                $stateParameters->data['password'] = $this->getRandomString(6);
            }
            $user = User::create([
                'name' => $stateParameters->data['lastname'] . ' ' . $stateParameters->data['firstname'],
                'email' => $stateParameters->data['code'],
                'email_verified_at' => now(),
                'password' => Hash::make($stateParameters->data['password']),
                'remember_token' => Str::random(10),
                'status' => 1
            ]);
            $user->roles()->attach($stateParameters->data['role_id']);
            $stateParameters->data['user_id'] = $user->id;
            // $room = CouncilTurnRoom::find($stateParameters->data['room_code']);
            // $stateParameters->data['room_code'] = $room->room_code;
            $stateParameters->data['created_at'] = now();
            return $stateParameters;
        });
        $crud->callbackBeforeUpdate(function ($stateParameters) {
            User::where('id',$stateParameters->data['user_id'])
                ->update([
                    'name' => $stateParameters->data['lastname'] . ' ' . $stateParameters->data['firstname'],
                    'email' => $stateParameters->data['code'],
                    'password' => Hash::make($stateParameters->data['password']),
                ]);
            $stateParameters->data['updated_at'] = now();
            return $stateParameters;
        });
        $crud->callbackDelete(function ($stateParameters) use ($table) {
            DB::table($table)
                ->where('code', $stateParameters->primaryKeyValue)
                ->update(['status' => 0]);
            DB::table($table)
                ->where('code', $stateParameters->primaryKeyValue)
                ->delete();
            return $stateParameters;
        });

        $crud->setActionButton('Phục hồi', 'fa fa-retweet', function ($row) {
            return '/examinee/reset/' . $row->id;
        }, true);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();

        // $form_url = '/exam/import-examinee';
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            // 'form_url' => $form_url,
            'councils' => Council::all(),
            'council_turns' => CouncilTurn::all(),
            'rooms' => Room::all(),
            'turn_code' => $turn_code,
            'room_code' => $room_code,
            'data' => ''
        ]);
    }

    public function examineeImport(Request $request)
    {
        $title = 'Tài khoản thí sinh';
        $cat = 'management';
        $subcat = 'examineeImport';
        $table = _EXAMINEE;
        $turn_code = $room_code = null;

        $where_clause = 'examinees.role_id = 8';
        if($request->query('turn')) {
            $turn_code = $request->query('turn');
            $where_clause .= " AND examinees.council_turn_code = '" . $turn_code . "'";
        }
        if($request->query('room')) {
            $room_code = $request->query('room');
            $where_clause .= " AND examinees.room_code = '" . $room_code . "'";
        }


        $crud = new GroceryCrud($this->config, $this->database);
        $crud->setTable($table)
            ->where($where_clause)
            ->setSubject($title, 'Quản lý ' . $title)
            ->columns([
                'code', 
                'user_id', 
                'password', 
                // 'id_card_number', 
                'lastname', 
                'firstname', 
                // 'birthday', 
                'subject_id', 
                // 'council_code', 
                'council_turn_code', 
                'room_code', 
                'status', 
                'is_backup'
                ])
            // ->setRead()
            ->addFields(['code', 'user_id', 'password', 'id_card_number', 'lastname', 'firstname', 'birthday', 'subject_id', 'council_code', 'council_turn_code', 'room_code', 'seat_number', 'is_backup', 'role_id'])
            // ->setClone()
            // ->cloneFields(['code', 'password', 'id_card_number', 'lastname', 'firstname', 'birthday', 'subject_id', 'council_code', 'council_turn_code', 'room_code', 'seat_number', 'is_backup', 'role_id'])
            ->editFields(['code', 'password', 'id_card_number', 'lastname', 'firstname', 'birthday', 'subject_id', 'council_code', 'council_turn_code', 'room_code', 'seat_number', 'is_backup', 'role_id', 'user_id'])
            ->requiredFields(['code', 'id_card_number', 'lastname', 'firstname', 'subject_id', 'council_code', 'council_turn_code', 'room_code'])
            ->displayAs([
                'code' => 'SBD',
                'user_id' => 'Tên tài khoản',
                'password' => 'Mật khẩu',
                'id_card_number' => 'Số CCCD',
                'lastname' => 'Họ lót',
                'firstname' => 'Tên',
                'birthday' => 'Ngày sinh',
                'subject_id' => 'Môn thi',
                'council_code' => 'HĐ thi',
                'council_turn_code' => 'Ca thi',
                'room_code' => 'Phòng thi',
                'seat_number' => 'Vị trí',
                'is_backup' => 'Dự phòng',
                'role_id' => 'Vai trò',
                'created_at' => 'Ngày tạo',
                'updated_at' => 'Ngày cập nhật'
            ])
            ->fieldType('password', 'password')
            ->fieldType('is_backup', 'checkbox_boolean')
            ->fieldType('birthday', 'date')
            // ->fieldType('user_id', 'hidden')
            ->setRelation('user_id', 'users', 'email')
            ->setRelation('role_id', 'roles', 'desc')
            ->setRelation('subject_id', 'subjects', 'desc')
            ->setPrimaryKey('code', 'councils')
            ->setRelation('council_code', 'councils', 'code')
            ->setRelation('council_turn_code', 'council_turns', 'name')
            ->setPrimaryKey('code', 'rooms')
            ->setRelation('room_code', 'rooms', 'code')
            ->setDependentRelation('council_turn_code', 'council_code', 'council_code')
			->unsetReadFields(['deleted_at'])
			->unsetAdd()
			->unsetEdit()
            ->unsetDelete()
			->unsetPrint()->unsetExport();

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        $output = $crud->render();

        $form_url = url('/exam/import-examinee');
        
        return $this->_example_output($output, $title, $cat, $subcat, [
            'form_url' => $form_url,
            'councils' => Council::all(),
            'council_turns' => CouncilTurn::all(),
            'rooms' => Room::all(),
            'turn_code' => $turn_code,
            'room_code' => $room_code,
            'data' => 'examinee_data'
        ]);
    }

    public function importExaminee(Request $request){
        ini_set('max_execution_time', 0);
        $council_code = $request->input('council_code');
        $council_turn_code = $request->input('council_turn_code');
        $room_code = $request->input('room_code');
        // $examinee_file = $request->file('file');
        // Validate incoming request data
        $request->validate([
            'file' => 'required|max:2048',
        ]);

        Excel::import(new ExamineeImport($council_code, $council_turn_code, $room_code), $request->file('file'));

        return redirect()->back()->with('message', 'File uploaded successfully.');
    }

    public function importRoom(Request $request){
        $organization_code = $request->input('organization_code');
        $data_file = $request->file('file');
        // Validate incoming request data
        $request->validate([
            'file' => 'required|max:2048',
        ]);

        Excel::import(new RoomImport($organization_code), $data_file);

        return redirect()->back()->with('message', 'File uploaded successfully.');
    }

    public function exportExaminee2Docx(Request $request){
        $council_turn_code = $request->input('turn');
        $room_code = $request->input('room');

        $examinees = Examinee::where('council_turn_code',$council_turn_code)->where('room_code',$room_code)->get();
        $data = [];
        foreach($examinees as $num => $examinee){
            $user = User::find($examinee->user_id);
            $council_turn = CouncilTurn::where('code',$council_turn_code)->first();
            $room = CouncilTurn::where('code',$council_turn_code)->first();
            $data[] = [
                'num' => ($num+1),
                'last_name' => $examinee->lastname,
                'first_name' => $examinee->firstname,
                'id_card_number' => $examinee->id_card_number,
                'username' => $user->email,
                'password' => $examinee->password,
                'date' => date('d/m/Y', strtotime($council_turn->start_at)),
                'time' => date('H:i', strtotime($council_turn->start_at)),
                'room' => $room_code
            ];
        }

        $filename = $council_turn->council_code . '-' . $council_turn->name . '-' . $room_code . '-Phiếu tài khoản thí sinh.docx';
        $filepath = Storage::path('export/docx/' . $filename);
        $filepath = ('storage/export/docx/' . $filename);
        PhpWordService::createFromTemplate($data,'assets/word-template/Examinee_template.docx',$filename);
        // dd($filepath);
        // return response()->download($filepath)->deleteFileAfterSend(true);
        return Storage::download('export/docx/' . $filename);
    }

    public function exportMonitor2Docx(Request $request){
        $council_turn_code = $request->input('turn');

        $council_turn_rooms = CouncilTurnRoom::where('council_turn_code',$council_turn_code)->get();
        $data = [];
        foreach($council_turn_rooms as $num => $council_turn_room){
            $council_turn = CouncilTurn::where('code',$council_turn_code)->first();
            $monitor = Monitor::where('code',$council_turn_room->monitor_code)->first();
            $data[] = [
                'num' => ($num+1),
                'username' => $monitor->code,
                'password' => $monitor->password,
                'date' => date('d/m/Y', strtotime($council_turn->start_at)),
                'time' => date('H:i', strtotime($council_turn->start_at)),
                'room' => $council_turn_room->room_code
            ];
        }

        $filename = $council_turn->council_code . '-' . $council_turn->name . '-Phiếu tài khoản CBCT.docx';
        $filepath = Storage::path('export/docx/' . $filename);
        $filepath = ('storage/export/docx/' . $filename);
        PhpWordService::createFromTemplate($data,'assets/word-template/Monitor_template.docx',$filename);
        // return response()->download($filepath)->deleteFileAfterSend(true);
        return Storage::download('export/docx/' . $filename);
    }

    public function exportMonitorByCouncil(Request $request, $council_id = 0){
        if(!$council_id){
            return redirect()->back();
        }
        $council = Council::find($council_id);
        if(!$council){
            return redirect()->back();
        }

        $council_turn_rooms = CouncilTurnRoom::whereLike('council_turn_code','%' . $council->code . '%')->get();
        $data = [];
        $chairman = Monitor::where('user_id',$council->monitor_id)->first();
        $data[] = [
            'num' => 0,
            'username' => $chairman->code,
            'password' => $chairman->password,
            'date' => '',
            'time' => '',
            'room' => ''
        ];
        foreach($council_turn_rooms as $num => $council_turn_room){
            $council_turn = CouncilTurn::where('code',$council_turn_room->council_turn_code)->first();
            $monitor = Monitor::where('code',$council_turn_room->monitor_code)->first();
            $room = Room::where('code',$council_turn_room->room_code)->first();
            $data[] = [
                'num' => ($num+1),
                'username' => $monitor->code,
                'password' => $monitor->password,
                'date' => date('d/m/Y', strtotime($council_turn->start_at)),
                'time' => date('H:i', strtotime($council_turn->start_at)),
                'room' => $room->code
            ];
        }

        $filename = $council->code . '-Phiếu tài khoản CBCT.docx';
        $filepath = Storage::path('export/docx/' . $filename);
        $filepath = ('storage/export/docx/' . $filename);
        PhpWordService::createFromTemplate($data,'assets/word-template/Monitor_template.docx',$filename);
        // return response()->download($filepath)->deleteFileAfterSend(true);
        return Storage::download('export/docx/' . $filename);
    }

    public function exportMonitorByCouncil2Excel(Request $request, $council_id) 
    {
        $council= Council::find($council_id);
        return Excel::download(new MonitorExport($council_id), 'Tài khoản giám thị - HĐ thi '. $council->code . '.xlsx');
    }

    //download council
    public function exportCouncil(Request $request, $id){
        // try {
        //     if (! $user = JWTAuth::parseToken()->authenticate()) {
        //         return response()->json(['error' => 'User not found'], 404);
        //     }
        // } catch (JWTException $e) {
        //     return response()->json(['error' => 'Invalid token'], 400);
        // }
        $council_id = $id;
        $council = Council::find($council_id);
        if(!$council){
            return response()->json([
                'success' => false,
                'message' => 'council not found',
            ], 404);
        }
        
        $chairman = User::find($council->monitor_id);
        $council_turns = CouncilTurn::where('council_code',$council->code)
                            ->where('status',1)
                            ->get();
        $examinees = Examinee::where('council_code',$council->code)->get();

        $council_turn_arr = [];
        $council_turn_room_arr = [];
        $room_arr = [];
        $subject_arr = [];
        $monitor_arr = [];
        $user_arr = [];
        $examinee_arr = [];
        $account_arr = [];

        $monitor = Monitor::where('user_id',$chairman->id)->first();
        $monitor_arr[] = [
            'id' => $monitor->id,
            'code' => $monitor->code,
            'name' => $monitor->name,
            'password' => $monitor->password,
            'status' => $monitor->status,
            'role_id' => $monitor->role_id,
            'user_id' => $monitor->user_id,
            'organization_id' => $monitor->organization_id,
        ];
        $rooms = Room::where('organization_code',$council->organization_code)->where('no_slots','>',0)->get();
        foreach($rooms as $room){
            $room_arr[] = [
                'id' => $room->id,
                'code' => $room->code,
                'name' => $room->name,
                'desc' => $room->desc,
                'no_slots' => $room->no_slots,
                'status' => $room->status,
                'organization_code' => $room->organization_code,
            ];
        }
        $subjects = Subject::where('status',1)->get();
        foreach($subjects as $subject){
            $subject_arr[] = [
                'id' => $subject->id,
                'code' => $subject->code,
                'short_code' => $subject->short_code,
                'code_number' => $subject->code_number,
                'name' => $subject->name,
                'desc' => $subject->desc,
                'status' => $subject->status,
            ];
        }
        foreach($council_turns as $council_turn){
            $council_turn_rooms = CouncilTurnRoom::where('council_turn_code',$council_turn->code)->get();
            $council_turn_arr[] = [
                'code' => $council_turn->code,
                'name' => $council_turn->name,
                'start_at' => $council_turn->start_at,
                'no_rooms' => count($council_turn_rooms),
                'is_active' => $council_turn->is_active,
                'status' => $council_turn->status,
                'council_code' => $council_turn->council_code,
            ];
            foreach($council_turn_rooms as $council_turn_room){
                $council_turn_room_arr[] = [
                    'is_active' => $council_turn_room->is_active,
                    'monitor_code' => $council_turn_room->monitor_code,
                    'council_turn_code' => $council_turn_room->council_turn_code,
                    'room_code' => $council_turn_room->room_code,
                ];
    
                $monitor = Monitor::where('code',$council_turn_room->monitor_code)->first();
                $monitor_arr[] = [
                    'id' => $monitor->id,
                    'code' => $monitor->code,
                    'name' => $monitor->name,
                    'password' => $monitor->password,
                    'status' => $monitor->status,
                    'role_id' => $monitor->role_id,
                    'user_id' => $monitor->user_id,
                    'organization_id' => $monitor->organization_id,
                ];
    
                $user = User::where('email',$council_turn_room->monitor_code)->first();
                $user_arr[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'password' => $user->password,
                    'status' => $user->status,
                    'remember_token' => $user->remember_token,
                ];
            }
        }

        foreach($examinees as $examinee){
            $examinee_arr[] = [
                'code' => $examinee->code,
                'id_card_number' => $examinee->id_card_number,
                'lastname' => $examinee->lastname,
                'firstname' => $examinee->firstname,
                'birthday' => $examinee->birthday,
                'password' => $examinee->password,
                'seat_number' => $examinee->seat_number,
                'subject_id' => $examinee->subject_id,
                'council_code' => $examinee->council_code,
                'council_turn_code' => $examinee->council_turn_code,
                'room_code' => $examinee->room_code,
                'user_id' => $examinee->user_id,
                'role_id' => $examinee->role_id,
                'is_backup' => $examinee->is_backup,
                'status' => $examinee->status,
            ];
            $user = User::find($examinee->user_id);
            $account_arr[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'status' => $user->status,
                'remember_token' => $user->remember_token,
            ];
        }

        $packaged_time = time();
        $package = array(
            'council' => [
                'code' => $council->code,
                'desc' => $council->desc,
                'no_turns' => $council->no_turns,
                'start_at' => $council->start_at,
                'finish_at' => $council->finish_at,
                'is_autostart' => $council->is_autostart,
                'import_testdata_before_time' => $council->import_testdata_before_time,
                'is_backup' => $council->is_backup,
                'is_clear' => $council->is_clear,
                'status' => $council->status,
                'organization_code' => $council->organization_code,
                'monitor_id' => $council->monitor_id,
            ],
            'chairman' => [
                'id' => $chairman->id,
                'name' => $chairman->name,
                'email' => $chairman->email,
                'email_verified_at' => $chairman->email_verified_at,
                'password' => $chairman->password,
                'status' => $chairman->status,
                'remember_token' => $chairman->remember_token,
            ],
            'council_turns' => $council_turn_arr,
            'council_turn_rooms' => $council_turn_room_arr,
            'rooms' => $room_arr,
            'subjects' => $subject_arr,
            'monitors' => $monitor_arr,
            'users' => $user_arr,
            'examinees' => $examinee_arr,
            'accounts' => $account_arr,
            'packaged_time' => $packaged_time,
        );

        $plain = json_encode($package);
        $filename = $packaged_time. '_' . $council->code . '.hd';

        //write file
        Storage::put('councildata/' . $filename,$plain);

        return Storage::download('councildata/' . $filename);
    }

    public function importCouncil(Request $request){
        $import_time = time();
        $path = 'councildata/';
        $filename = $import_time . '.dat';
        $file = $request->file('file');
        if(!$file){
            return redirect()->back()->with('message', 'Something wrong!');
        }
        $path = $file->storeAs(
            $path, $filename
        );

        $data = Storage::get($path);

        $council_data = json_decode($data,TRUE);
        // dd($test_mixes_content);
        if(!$council_data){
            return redirect()->back()->with('message', 'Something wrong!');
        }

        //import to DB
        //rooms
        foreach($council_data['rooms'] as $item){
            $check = Room::where('code',$item['code'])->count();
            if(!$check){
                Room::create($item);
            }
        }
        //subjects
        if(isset($council_data['subjects'])){
            foreach($council_data['subjects'] as $item){
                $check = Subject::where('code',$item['code'])->count();
                if(!$check){
                    Subject::create($item);
                }
            }
        }
        //monitor-users
        $check = User::where('email',$council_data['chairman']['email'])->count();
        if(!$check){
            $user = User::create($council_data['chairman']);
            $user->roles()->attach(7);
        }
        foreach($council_data['users'] as $item){
            $check = User::where('email',$item['email'])->count();
            if(!$check){
                $user = User::create($item);
                $user->roles()->attach(4);
            }
        }
        //monitors
        foreach($council_data['monitors'] as $item){
            $check = Monitor::where('code',$item['code'])->count();
            if(!$check){
                Monitor::create($item);
            }
        }
        //council
        $check = Council::where('code',$council_data['council']['code'])->first();
        if(!$check){
            Council::create($council_data['council']);
        }
        //council-turns
        foreach($council_data['council_turns'] as $item){
            $check = CouncilTurn::where('code',$item['code'])->count();
            if(!$check){
                CouncilTurn::create($item);
            }
        }
        //council-turn-rooms
        foreach($council_data['council_turn_rooms'] as $item){
            $check = CouncilTurnRoom::where('council_turn_code',$item['council_turn_code'])
                                    ->where('monitor_code',$item['monitor_code'])
                                    ->where('room_code',$item['room_code'])
                                    ->count();
            if(!$check){
                CouncilTurnRoom::create($item);
            }
        }
        //examinee-users
        foreach($council_data['accounts'] as $item){
            $check = User::where('email',$item['email'])->count();
            if(!$check){
                $user = User::create($item);
                $user->roles()->attach(8);
            }
        }
        //examinees
        foreach($council_data['examinees'] as $item){
            $check = Examinee::where('user_id',$item['user_id'])->count();
            if(!$check){
                Examinee::create($item);
            }
        }

        return redirect()->back()->with('message', 'File uploaded successfully.');
    }

    //download council turn - sao lưu ca thi
    public function exportCouncilTurn(Request $request, $code){
        $council_turn = CouncilTurn::where('code',$code)->first();
        $council = Council::where('code',$council_turn->council_code)->first();
        if(!$council){
            return response()->json([
                'success' => false,
                'message' => 'council not found',
            ], 404);
        }
        
        $chairman = User::find($council->monitor_id);
        $council_turns = CouncilTurn::where('council_code',$council->code)
                            ->where('status',1)
                            ->get();
        $examinees = Examinee::where('council_turn_code',$code)->get();

        $council_turn_arr = [];
        $council_turn_room_arr = [];
        $room_arr = [];
        $user_arr = [];
        $monitor_arr = [];
        $account_arr = [];
        $examinee_arr = [];
        $examinee_test_mix_arr = [];
        $test_mix_id_arr = [];
        $examinee_answer_arr = [];

        $monitor = Monitor::where('user_id',$chairman->id)->first();
        $monitor_arr[] = [
            'id' => $monitor->id,
            'code' => $monitor->code,
            'name' => $monitor->name,
            'password' => $monitor->password,
            'status' => $monitor->status,
            'role_id' => $monitor->role_id,
            'user_id' => $monitor->user_id,
            'organization_id' => $monitor->organization_id,
        ];
        $rooms = Room::where('organization_code',$council->organization_code)->where('no_slots','>',0)->get();
        foreach($rooms as $room){
            $room_arr[] = [
                'id' => $room->id,
                'code' => $room->code,
                'name' => $room->name,
                'desc' => $room->desc,
                'no_slots' => $room->no_slots,
                'status' => $room->status,
                'organization_code' => $room->organization_code,
            ];
        }
        foreach($council_turns as $council_turn){
            $council_turn_rooms = CouncilTurnRoom::where('council_turn_code',$council_turn->code)->get();
            $council_turn_arr[] = [
                'code' => $council_turn->code,
                'name' => $council_turn->name,
                'start_at' => $council_turn->start_at,
                'no_rooms' => count($council_turn_rooms),
                'is_active' => $council_turn->is_active,
                'status' => $council_turn->status,
                'council_code' => $council_turn->council_code,
            ];
            foreach($council_turn_rooms as $council_turn_room){
                $council_turn_room_arr[] = [
                    'is_active' => $council_turn_room->is_active,
                    'monitor_code' => $council_turn_room->monitor_code,
                    'council_turn_code' => $council_turn_room->council_turn_code,
                    'room_code' => $council_turn_room->room_code,
                ];
    
                $monitor = Monitor::where('code',$council_turn_room->monitor_code)->first();
                $monitor_arr[] = [
                    'id' => $monitor->id,
                    'code' => $monitor->code,
                    'name' => $monitor->name,
                    'password' => $monitor->password,
                    'status' => $monitor->status,
                    'role_id' => $monitor->role_id,
                    'user_id' => $monitor->user_id,
                    'organization_id' => $monitor->organization_id,
                ];
    
                $user = User::where('email',$council_turn_room->monitor_code)->first();
                $user_arr[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'password' => $user->password,
                    'status' => $user->status,
                    'remember_token' => $user->remember_token,
                ];
            }
        }

        foreach($examinees as $examinee){
            $examinee_arr[] = [
                'code' => $examinee->code,
                'id_card_number' => $examinee->id_card_number,
                'lastname' => $examinee->lastname,
                'firstname' => $examinee->firstname,
                'birthday' => $examinee->birthday,
                'password' => $examinee->password,
                'seat_number' => $examinee->seat_number,
                'subject_id' => $examinee->subject_id,
                'council_code' => $examinee->council_code,
                'council_turn_code' => $examinee->council_turn_code,
                'room_code' => $examinee->room_code,
                'user_id' => $examinee->user_id,
                'role_id' => $examinee->role_id,
                'is_backup' => $examinee->is_backup,
                'status' => $examinee->status,
            ];
            $user = User::find($examinee->user_id);
            $account_arr[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'status' => $user->status,
                'remember_token' => $user->remember_token,
            ];
            $examinee_test_mix = ExamineeTestMix::where('examinee_account',$user->email)->first();
            if(!$examinee_test_mix) continue;
            $examinee_test_mix_arr[] = [
                'examinee_test_uuid' => $examinee_test_mix->examinee_test_uuid,
                'examinee_code' => $examinee_test_mix->examinee_code,
                'examinee_account' => $examinee_test_mix->examinee_account,
                'test_mix_id' => $examinee_test_mix->test_mix_id,
                'subject_id' => $examinee_test_mix->subject_id,
                'council_code' => $examinee_test_mix->council_code,
                'council_turn_code' => $examinee_test_mix->council_turn_code,
                'room_code' => $examinee_test_mix->room_code,
                'start_time' => $examinee_test_mix->start_time,
                'expected_finish_time' => $examinee_test_mix->expected_finish_time,
                'finish_time' => $examinee_test_mix->finish_time,
                'remaining_time' => $examinee_test_mix->remaining_time,
                'bonus_time' => $examinee_test_mix->bonus_time,
                'answer_logs' => $examinee_test_mix->answer_logs,
            ];
            $test_mix_id_arr[] = $examinee_test_mix->test_mix_id;

            $examinee_answers = ExamineeAnswer::where('examinee_account',$user->email)->get();
            foreach($examinee_answers as $answer){
                $examinee_answer_arr[] = [
                    'examinee_test_uuid' => $answer->examinee_test_uuid,
                    'examinee_answer_uuid' => $answer->examinee_answer_uuid,
                    'examinee_code' => $answer->examinee_code,
                    'examinee_account' => $answer->examinee_account,
                    'test_mix_id' => $answer->test_mix_id,
                    'subject_id' => $answer->subject_id,
                    'question_id' => $answer->question_id,
                    'question_type_id' => $answer->question_type_id,
                    'question_number' => $answer->question_number,
                    'answer_detail' => $answer->answer_detail,
                    'submitting_time' => $answer->submitting_time,
                    'remaining_time' => $answer->remaining_time,
                ];
            }
        }
        sort($test_mix_id_arr);
        $packaged_time = time();
        $package = array(
            'council_turn_code' => $code,
            'council' => [
                'code' => $council->code,
                'desc' => $council->desc,
                'no_turns' => $council->no_turns,
                'start_at' => $council->start_at,
                'finish_at' => $council->finish_at,
                'is_autostart' => $council->is_autostart,
                'import_testdata_before_time' => $council->import_testdata_before_time,
                'is_backup' => $council->is_backup,
                'is_clear' => $council->is_clear,
                'status' => $council->status,
                'organization_code' => $council->organization_code,
                'monitor_id' => $council->monitor_id,
            ],
            'chairman' => [
                'id' => $chairman->id,
                'name' => $chairman->name,
                'email' => $chairman->email,
                'email_verified_at' => $chairman->email_verified_at,
                'password' => $chairman->password,
                'status' => $chairman->status,
                'remember_token' => $chairman->remember_token,
            ],
            'council_turns' => $council_turn_arr,
            'council_turn_rooms' => $council_turn_room_arr,
            'rooms' => $room_arr,
            'users' => $user_arr, //monitor account
            'monitors' => $monitor_arr, //monitor info
            // 'council_turn_test_mixes' => CouncilTurnTestMix::where('council_turn_code',$code)->where('is_used',1)->get(),
            'examinee_test_mixes' => $examinee_test_mix_arr,
            'accounts' => $account_arr, //examinee account
            'examinees' => $examinee_arr, //examinee info
            'examinee_answers' => $examinee_answer_arr,
            // 'questions' => [],
            // 'test_groups' => [],
            'test_mix_ids' => (array_unique($test_mix_id_arr)),
            // 'activity_logs' => ActivityLog::where('council_turn_code',$code)->get(),
            'packaged_time' => $packaged_time,
        );

        $plain = json_encode($package);
        $filename = $packaged_time. '_' . $code . '.bak';

        CouncilTurn::where('code',$code)->update([
            'is_backup' => true,
            'bk_file_name' => 'bkdata/' . $filename,
        ]);

        //write file
        Storage::put('bkdata/' . $filename,$plain);

        return Storage::download('bkdata/' . $filename);
    }

    public function flushCouncilTurn(Request $request){
        DB::table('activity_logs')->truncate();
        DB::table('examinee_answers')->truncate();
        DB::table('examinee_test_mixes')->truncate();

        return redirect()->back()->with('message', 'Flush council turn data successfully.');
    }

    public function activeRooms(Request $request){
        $ids = $request->input('id');
        // dd($ids);
        CouncilTurnRoom::whereIn('id',$ids)->update([
            'is_active' => true
        ]);
        return redirect()->back();
        // foreach($ids as $id){
        //     CouncilTurnRoom::whereIn($ids)
        // }
    }
}