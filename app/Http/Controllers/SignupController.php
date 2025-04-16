<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;





class SignupController extends Controller
{
    // Show registration form
    public function showRegistrationForm()
    {
        return view('auth.signup', [
            'userTypes' => ['news_enthusiast', 'content_creator']
        ]);
    }

    // Handle registration
    public function register(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:255|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'user_type' => 'required|in:news_enthusiast,content_creator'
    ]);

    $user = User::create([
        'username' => $request->username,
        'name' => $request->username, // or add a name field to your form
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'user_type' => $request->user_type
    ]);

    event(new Registered($user));

    return redirect()->route('verification.notice');
}
}