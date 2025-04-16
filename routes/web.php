<?php

use App\Http\Controllers\AuthentController;
use App\Http\Controllers\AuthenticationController;

// use App\Http\Controllers\HomeController;
// use App\Http\Controllers\SignupController;
// use App\Http\Controllers\LoginController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;







Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [AuthentController::class, 'signup'])->name('user.register');
Route::post('/register/store', [AuthentController::class, 'register'])->name('register.store');


Route::get('/login', [AuthentController::class, 'signin'])->name('login');

Route::post('/login/store', [AuthentController::class, 'login'])->name('login.store');


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthentController::class, 'dashboard'])->name('dashboard');


});


Route::get('/logout', [AuthentController::class, 'logout'])->name('logout');





Route::get('/forgot-password', [AuthentController::class, 'showForgetPasswordForm'])->name('auth.forgetpassword');
Route::post('/forgot-password', [AuthentController::class, 'submitForgetPasswordForm'])->name('auth.submitforgetpassword');


Route::get('/reset-password/{token}', [AuthentController::class, 'showResetPasswordForm'])->name('auth.showresetpassword');


Route::post('/reset-password', [AuthentController::class, 'submitResetPasswordForm'])->name('auth.submitresetpassword');


// Route::get('/upload', [UploadController::class,"upload"])->name("upload");
// Route::post('/upload', [UploadController::class,"uploadPost"])->name("upload.post");



Route::get('/upload', [PageController::class,"index"]);

Route::get('/uploadpage', [PageController::class,"uploadpage"])->name('uploadpage');


Route::post('/uploadproduct', [PageController::class,"store"])->name('uploadproduct');