<?php

namespace App\Http\Controllers;

use App\Models\User;
use DB;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Jobs\SendPasswordResetEmail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; 

class AuController extends Controller
{
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

        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user
        ], 201);
    }

    // Login user and generate token
    public function login(Request $request)
    {
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
            'token' => $user->createToken('video-news-token')->plainTextToken,
            'user' => $user,
        ]);
    }

    // Logout user (destroy token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully!']);
    }

    // Get authenticated user data
    public function user(Request $request)
    {
        return response()->json($request->user());
    }


    public function forgot_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
   
        // if($validator->fails()){
        //     return response()->json([
        //         'error' => 'Validation Error',
        //         'message' => 'The provided email is invalid or does not exist in our system.'
        //     ], 422); // Changed to 422 for validation errors
        // }

        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email, 
            'token' => $token, 
            'created_at' => Carbon::now()
        ]);

        try {
            Mail::send('emails.forgetPassword', [
                'token' => $token, 
                'app_url' => env('FRONTEND_URL')
            ], function($message) use($request) {
                $message->to($request->email);
                $message->subject('Reset Password Notification');
            });
            
            return response()->json([
                'message' => 'Password reset mail sent successfully'
            ], 200); // Changed to 200 for success
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Mail Error',
                'message' => 'Failed to send password reset email. Please try again later.'
            ], 500); // Server error status code
        }
    }

    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string|min:64|max:64',
            'password' => [
                'required',
                'string',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Check if token exists and is valid
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            return response()->json([
                'error' => 'Invalid Token',
                'message' => 'This password reset token is invalid.'
            ], 400);
        }

        // Check if token is expired (e.g., 60 minutes)
        $tokenCreatedAt = Carbon::parse($tokenData->created_at);
        if ($tokenCreatedAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
                
            return response()->json([
                'error' => 'Expired Token',
                'message' => 'This password reset token has expired. Please request a new one.'
            ], 400);
        }

        // Update user password
        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        // Delete the used token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'message' => 'Password has been successfully reset.'
        ], 200);
    }
}