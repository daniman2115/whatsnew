<?php

namespace App\Http\Controllers;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }


public function googleLogin(){
        return Socialite::driver('google')->redirect();
    }

// public function googleAuthentication(Request $request){
//     try {
//         $googleUser = Socialite::driver('google')->user();
//         $user = User::where('email', $googleUser->email)->first();

//         if($user) {
//             Auth::login($user);
//             $token = $user->createToken('authToken')->plainTextToken;
//         } else {
//             $userData = User::create([
//                 'name' => $googleUser->name,
//                 'email' => $googleUser->email,
//                 'username' => Str::random(10),
//                 'password' => Hash::make('password@1234'),
//                 'google_id' => $googleUser->id,
//             ]);
            
//             Auth::login($userData);
//             $token = $userData->createToken('authToken')->plainTextToken;
//         }

//         // Redirect back to frontend with token
//         return redirect(config('app.frontend_url').'/auth/callback?'.http_build_query([
//             'token' => $token,
//             'user' => json_encode(Auth::user()),
//             'message' => 'Login successful'
//         ]));

//     } catch(Exception $e) {
//         return redirect(config('app.frontend_url').'/login?'.http_build_query([
//             'error' => $e->getMessage()
//         ]));
//     }
// }




public function googleAuthentication(Request $request)
{
    try {
        $googleUser = Socialite::driver('google')->user();
        
        // Your existing user find/create logic
        $user = User::where('email', $googleUser->email)->first();
        
        if (!$user) {
            // Create new user logic
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'username' => Str::random(10),
                'password' => Hash::make('password@1234'),
            ]);
        }

        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Redirect back to frontend with token and user data
        return redirect(env('FRONTEND_URL').'/auth/callback?'.http_build_query([
            'token' => $token,
            'user' => json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
            ])
        ]));
        
    } catch (\Exception $e) {
        return redirect(env('FRONTEND_URL').'/login?'.http_build_query([
            'error' => 'Google authentication failed'
        ]));
    }
}
}