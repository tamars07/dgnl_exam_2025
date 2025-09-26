<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MonitorImport;
use App\Imports\UsersImport;
use App\Exports\UsersExport;
use App\Models\User;
// use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function index()
    {
        $users = User::get();
  
        return view('pages.users', compact('users'));
    }

    public function import(Request $request) 
    {
        // try{
        //     Excel::import(new UsersImport, $request->file('file'));
        //     return response()->json(['data'=>'Users imported successfully.',201]);
        // }catch(\Exception $ex){
        //     Log::info($ex);
        //     return response()->json(['data'=>'Some error has occur.',400]);
        // }

        // Validate incoming request data
        $request->validate([
            'file' => 'required|max:2048',
        ]);
  
        // Excel::import(new UsersImport, $request->file('file'));
        Excel::import(new MonitorImport('HCMUE','HCMUE_1','C903'), $request->file('file'));
                 
        return back()->with('success', 'Users imported successfully.');
    }

     /**
    * @return \Illuminate\Support\Collection
    */
    public function export() 
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
