<?php

use App\Http\Controllers\AuthentController;
use App\Http\Controllers\AuthenticationController;

// use App\Http\Controllers\HomeController;
// use App\Http\Controllers\SignupController;
// use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;







Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [AuthentController::class, 'signup'])->name('user.register');
Route::post('/register/store', [AuthentController::class, 'register'])->name('register.store');


Route::get('/login', [AuthentController::class, 'signin'])->name('login');

Route::post('/login/store', [AuthentController::class, 'login'])->name('login.store');


Route::middleware('auth')->group(function () {
    Route::post('/dashboard', [AuthentController::class, 'dashboard'])->name('dashboard');


});


Route::get('/logout', [AuthentController::class, 'logout'])->name('logout');





Route::get('/forgot-password', [AuthentController::class, 'showForgetPasswordForm'])->name('auth.forgetpassword');
Route::post('/forgot-password', [AuthentController::class, 'submitForgetPasswordForm'])->name('auth.submitforgetpassword');


Route::get('/reset-password/{token}', [AuthentController::class, 'showResetPasswordForm'])->name('auth.showresetpassword');


Route::post('/reset-password', [AuthentController::class, 'submitResetPasswordForm'])->name('auth.submitresetpassword');


// Authentication Routes
// Route::controller(AuthenticationController::class)->group(function () {
//     // Registration
//     Route::get('/register', 'showRegistrationForm')->name('register');
//     Route::post('/register', 'register')->name('register.post');

//     // Login
//     Route::get('/login', 'showLoginForm')->name('login');
//     Route::post('/login', 'login')->name('login.post');

//     // Logout
//     Route::post('/logout', 'logout')->name('logout');

//     // Profile
//     Route::get('/profile', 'profile')->name('profile')->middleware('auth');
// });

// // Protected Dashboard Route
// Route::middleware('auth')->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });





// Route::get('/forgot-password', [AuthenticationController::class, 'forgotPassword'])->name('Auths.forgetPassword');
// Route::post('/forgot-password', [AuthenticationController::class, 'passwordForgetForm'])->name('Auths.passwordForgetForm');


// Route::get('/reset-password', [AuthenticationController::class, 'resetPassword'])->name('Auths.resetPassword');
// Route::post('/reset-password', [AuthenticationController::class, 'passwordResetForm'])->name('passwordResetForm');



// Route::get('/', [HomeController::class, 'index']);

// // Route::get('/', function () {
// //     dd(auth()->check()); // Shows "true" or "false"
// // })->middleware('auth');



// // Authentication Routes
// Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [LoginController::class, 'login']);
// Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// // Registration Routes
// Route::get('/signup', [SignupController::class, 'showRegistrationForm'])->name('signup');
// Route::post('/signup', [SignupController::class, 'register']);





// use App\Http\Controllers\GoogleAuthController;
// use App\Http\Controllers\SignupController;
// use App\Models\User;
// use Illuminate\Foundation\Auth\EmailVerificationRequest;
// use Illuminate\Support\Facades\Route;
// use Laravel\Socialite\Facades\Socialite;
// use Illuminate\Http\Request;



// Route::get('/', function () {
//     return view('welcome');
// });

// // Authentication Routes
// // routes/api.php
// Route::get('/auth/google/redirect', function  (Request $request){
//     return Socialite::driver('google')->redirect();

// });

// Route::get('/auth/google/callback', function  (Request $request){
//     $googleUser = Socialite::driver('google')->stateless()->user();


//     $user = User::updateOrCreate(
//         ['google_id' => $googleUser->id,
//         'name' => $googleUser->name,
//         'email' => $googleUser->email,
//         'password' => bcrypt(Str::random(12)),
//     ]
//     );
//     dd($user);
// });


// // require './config/auth.php'; 