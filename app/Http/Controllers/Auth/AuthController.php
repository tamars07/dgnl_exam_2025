<?php
  
namespace App\Http\Controllers\Auth;
  
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
  
class AuthController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index(): View
    {
        return view('auth._login');
    }  
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    // public function registration(): View
    // {
    //     return view('auth.registration');
    // }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
   
        $credentials = $request->only('email', 'password');
        // dd(Auth::attempt($credentials));
        if (auth()->guard('web')->attempt($credentials)) {
            $user = auth()->guard('web')->user();
            // dd($user->roles);
            foreach($user->roles as $role){
                // dd($role);
                switch($role->name){
                    case 'ADMIN': case 'EDITOR':
                        return redirect('/qbank');
                        break;
                    case 'MODERATOR': case 'CHAIRMAN':
                        return redirect('/exam');
                        break;
                }
            }
            // return redirect()->intended('dashboard')
            //             ->withSuccess('You have Successfully loggedin');
        }
  
        return redirect("login")->withError('Tài khoản hoặc mật khẩu chưa chính xác!');
    }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    // public function postRegistration(Request $request): RedirectResponse
    // {  
    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users',
    //         'password' => 'required|min:6',
    //     ]);
           
    //     $data = $request->all();
    //     $user = $this->create($data);
            
    //     Auth::login($user); 

    //     return redirect("dashboard")->withSuccess('Great! You have Successfully loggedin');
    // }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function dashboard()
    {
        if(Auth::check()){
            return Redirect('dashboard');
        }
  
        return redirect("login")->withSuccess('Opps! You do not have access');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function logout(Request $request): RedirectResponse
    {
        // Session::flush();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
  
        return redirect('/login');
    }
}