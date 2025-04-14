<?php

use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


// Protected Home/Dashboard
Route::get('/', function () {
    return view('welcome');
})->middleware('auth');



// Registration Routes
Route::get('/signup', [SignupController::class, 'showRegistrationForm'])->name('signup');
Route::post('/signup', [SignupController::class, 'register']);


