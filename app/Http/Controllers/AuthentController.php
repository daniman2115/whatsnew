<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon; 
use Mail;
use DB;
use Illuminate\Support\Facades\Hash;
use Socialite;
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
            'username' => 'required|string|min:8|unique:users',
            'user_type' => 'required|in:news_enthusiast,content_creator,admin'

        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $request->username,
            'user_type' => $request->user_type
        ]);

             return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user
            ], 201);
        

       

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
    $user = User::where('email', $request->email)->first();
   
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Invalid credentials.'],
        ]);
    }

    return response()->json([
        "message" => "loged in successfully!",
        'token' => $user->createToken('video-news-token')->plainTextToken,
        'user' => $user,
    ]);

    
 }

 
 public function dashboard(){
    return view("dashboard.dashboard");
}


public function logout(Request $request){
    
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully!']);
    

}

    // Get authenticated user data
    public function user(Request $request)
    {
        return response()->json($request->user());
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