<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;
use Carbon\Carbon; 
use App\Models\Userdevice;
use Mail;
use DB;
use Hash;
use Socialite;
use App\Mail\SendOtpCodeMail;
use Illuminate\Support\Facades\session;


class AuthentController extends Controller
{

public function signup(){
    return view("auth.register");
}

public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:news_enthusiast,content_creator,admin'
        ]);

        $user = new User();
             $user->name =  $request->name;
             $user->email =  $request->email;
             $user->password =  Hash::make($request->password);
             $user->user_type =  $request->user_type;
             $data=$user->save();
             if($data){
                return redirect()->route('login')->with('success','registered successfully');
             }else{
                return back()->with('error','somethin went wrong');
             }

        // Log the user in after registration
        // Auth::login($user);

        // return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }

    public function signin(){
        return view("auth.login");
    }

 public function login(Request $request){
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    $auth = $request->only('email','password');
    if(Auth::attempt($auth)){
      return redirect()->route('dashboard')->with('success','registered successfully');
    }else{
      return back()->with('error', 'something went wrong'); 
    }

    
 }

 
 public function dashboard(){
    return view("dashboard.dashboard");
}


public function logout(Request $request){
    session::flush();
    Auth::logout();
    return redirect()->route('login')->with('success','registered successfully');

}




public function showForgetPasswordForm(Request $request){
    return view('auth.forgetPassword');
}



public function submitForgetPasswordForm(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users']);

        $token = Str::random(64);
        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
        DB::table('password_reset_tokens')->insert([
            'email'=> $request->email,
            'token'=> $token,
            'created_at' =>Carbon::now()
        ]);



            Mail::send('auth.email', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password');
            });

            return redirect()->route('login');


}


public function showResetPasswordForm($token){
    return view('auth.resetPassword', ['token' => $token]);
}


public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'email'=> 'required|email|exists:users',
            // 'token'=> 'required|string|min:6',
            'password'=> 'required|string|min:6|confirmed',
            'password_confirmation'=> 'required',
        ]);

        $updatePassword = DB::table('password_reset_tokens')->where(
            ['email' => $request->email, 
                     'token' => $request->token])->first();

        if(!$updatePassword) {
            
            return back()->withInput()->with('error', "Invalid Token");
        }

        $user = User::where('email', $request->email)
                ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();

        return redirect()->route('login');

    }



}