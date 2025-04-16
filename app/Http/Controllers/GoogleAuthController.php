<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless() // Essential for API
            ->with([
                'hd' => 'yourdomain.com', // Optional domain restriction
                'access_type' => 'offline',
                'prompt' => 'consent' // Forces fresh token
            ])
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(Str::random(24)),
                    'email_verified_at' => now(),
                    'profile_picture' => $googleUser->avatar ?? null,
                ]
            );

            return response()->json([
                'access_token' => $user->createToken('google-auth')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->profile_picture
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}