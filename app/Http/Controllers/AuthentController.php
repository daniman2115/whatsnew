<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

public function register(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'username' => 'required|string|min:8|unique:users',
            'user_type' => 'required|in:news_enthusiast,content_creator,admin',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
        ]);



        $userData= [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $request->username,
            'user_type' => $request->user_type
        ];


    if ($request->hasFile('profile_picture')) {
        $image = $request->file('profile_picture');
        $imageName = time().'.'.$image->extension();

                // Delete old image if exists
        if ($user->profile_picture) {
            Storage::delete('public/profile_pictures/'.basename($user->profile_picture));
        }

        $image->move(storage_path('app/public/profile_pictures'), $imageName);
        
        // $image->storeAs('public/profile_pictures', $imageName);
        $url = url('storage/profile_pictures/'.$imageName);
        

        // Save the relative path to the database
        $userData['profile_picture'] = $url;
    }

    $user = User::create($userData);

    return response()->json([
        'message' => 'User registered successfully!',
        'user' => $user
    ], 201);



    }


    public function updateProfile(Request $request, $id)
{
    $user = User::findOrFail($id);
    
    $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|unique:users,email,'.$id,
        'username' => 'sometimes|string|min:8|unique:users,username,'.$id,
        'bio' => 'nullable|string|max:500',
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $updateData = $request->only(['name', 'email', 'username', 'bio']);

    if ($request->hasFile('profile_picture')) {
        // Delete old profile picture if it exists
        if ($user->profile_picture) {
            $oldImagePath = str_replace(url('/'), '', $user->profile_picture);
            $oldImagePath = str_replace('storage/', 'public/', $oldImagePath);
            Storage::delete($oldImagePath);
        }

        $image = $request->file('profile_picture');
        $imageName = time().'.'.$image->extension();
        $image->move(storage_path('app/public/profile_pictures'), $imageName);
        $url = url('storage/profile_pictures/'.$imageName);
        $updateData['profile_picture'] = $url;
    }

    $user->update($updateData);

    return response()->json([
        'message' => 'Profile updated successfully!',
        'user' => $user
    ]);
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


public function forget(Request $request)  
{
    $request->validate([
        'email' => 'required|email|exists:users,email'
    ]);

    $token = Str::random(64);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'token' => $token,
            'created_at' => Carbon::now()
        ]
    );

    $resetUrl = env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

    try {
        Mail::send([], [], function($message) use ($request, $resetUrl) {
            $message->to($request->email)
                   ->subject('Reset Password Notification')
                   ->html("Please click the following link to reset your password: <a href='{$resetUrl}'>Reset Password</a>");
        });
        return response()->json([
            'message' => 'Password reset link sent successfully'
        ], 200);

    } catch (Exception $e) {
        Log::error('Password reset failed: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to send password reset email',
            'error' => $e->getMessage() 
        ], 500);
    }
}
public function reset(Request $request){
  $request->validate(
    [
      'email'=>'email|required|exists:users',
      'password'=>'required|min:6|confirmed',
      'password_confirmation'=>'required'
    ],
    ['token'=>'required|min:6',]
  );
  $updatepassword = DB::table('password_reset_tokens')->where(
    ['email'=>$request->email,'token'=>$request->token]
  )->first();
  if(!$updatepassword){
    $request['error'] = 'Invalid Request';
    $request['message'] = 'This request to reset password is invalid.';
    $statusCode = 400;
    return response()->json($request,$statusCode);
  }
  $user = User::where('email',$request->email)->update(['password'=>Hash::make($request->password)]);
  DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();


  
}

}