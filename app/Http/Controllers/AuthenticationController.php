<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use DB;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Mail;
use Password;
use Str;
use App\Jobs\SendPasswordResetEmail;
use Validator;
use Carbon\Carbon; 



class AuthenticationController extends Controller
{
    // Register a new user
    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|unique:users',
    //         'password' => 'required|string|min:8|confirmed',
    //         'user_type' => 'required|in:news_enthusiast,content_creator,admin'
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'user_type' => $request->user_type
    //     ]);

    //     return response()->json([
    //         'message' => 'User registered successfully!',
    //         'user' => $user
    //     ], 201);
    // }

    // // Login user and generate token
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         throw ValidationException::withMessages([
    //             'email' => ['Invalid credentials.'],
    //         ]);
    //     }

    //     return response()->json([
    //         'token' => $user->createToken('video-news-token')->plainTextToken,
    //         'user' => $user,
    //     ]);
    // }

    // // Logout user (destroy token)
    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()->delete();
    //     return response()->json(['message' => 'Logged out successfully!']);
    // }

    // // Get authenticated user data
    // public function user(Request $request)
    // {
    //     return response()->json($request->user());
    // }




    public function showRegistrationForm()
    {
        return view('register', [
            'userTypes' => ['news_enthusiast', 'content_creator', 'admin']
        ]);
    }

    // Handle registration
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:news_enthusiast,content_creator,admin'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type
        ]);

        // Log the user in after registration
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }

    // Show login form
    public function showLoginForm()
    {
        return view('layouts.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'Invalid credentials.',
        ]);
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully!');
    }

    // Show user profile
    public function profile(Request $request)
    {
        return view('profile', ['user' => $request->user()]);
    }





    public function passwordForgetForm(Request $request){
        return view('Auths.forgetPassword');
    }




    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists|users']);

        $token = Str::random(64);
        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
        DB::table('password_reset_tokens')->insert([
            'email'=> $request->email,
            'token'=> $token,
            'created_at' =>Carbon::now()
        ]);



            Mail::send('Auths.forgetPassword', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password Notification');
            });

            return redirect()->route('login');


}
    








    // public function forgot_password(Request $request)
    // {
    //     $input = $request->all();

    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email',
    //     ]);
   
    //     if($validator->fails()){

    //         $response['error'] = 'Validation Error';
            
    //         $statusCode = 201;
    //         return response()->json($response, $statusCode);

    //     }

    //     $token = Str::random(64);

    //     $updatePassword = DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();


    //     DB::table('password_reset_tokens')->insert(
    //         ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
    //     );

        

    //     try {
    //         Mail::send('emails.forgetPassword', ['token' => $token, 'app_url' => env('FRONTEND_URL')], function($message) use($request){
    //             $message->to($request->email);
    //             $message->subject('Reset Password Notification');
    //         });
    //     } catch (Exception $e)
    //     {
    //     }
    
    //     $response['message'] = 'Password reset mail sent successfully';
    //     $statusCode = 400;

    //     return response()->json($response, $statusCode);


    // }





    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'=> 'required|email|exists:users',
            'token'=> 'required|string|min:6',
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

    }


    public function passwordResetForm($token){
        return view('Auths.resetPassword', ['token' => $token]);
    }



    /**
     * Handle password reset
     */
    // public function reset(Request $request)
    // {
    //     $request->validate([
    //         'token' => 'required',
    //         'email' => 'required|email',
    //         'password' => [
    //             'required',
    //             'confirmed',
    //             PasswordRule::min(8)
    //                 ->mixedCase()
    //                 ->numbers()
    //                 ->symbols()
    //         ],
    //     ]);

    //     $status = Password::reset(
    //         $request->only('email', 'password', 'password_confirmation', 'token'),
    //         function ($user, $password) {
    //             $user->forceFill([
    //                 'password' => Hash::make($password),
    //                 'remember_token' => Str::random(60),
    //             ])->save();

    //             event(new PasswordReset($user));
    //         }
    //     );

    //     return $status === Password::PASSWORD_RESET
    //         ? response()->json(['message' => __($status)])
    //         : throw ValidationException::withMessages(['email' => __($status)]);
    // }

    





    
}