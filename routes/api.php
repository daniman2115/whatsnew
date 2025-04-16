<?php
// use App\Http\Controllers\GoogleAuthController;
// use App\Models\User;
// use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request;
// use Laravel\Socialite\Facades\Socialite;

// Route::get('/auth/google/redirect', function  (Request $request){
//         return Socialite::driver('google')->redirect();
    
//     });
    
//     Route::get('/auth/google/callback', function  (Request $request){
//         $googleUser = Socialite::driver('google')->stateless()->user();
    
    
//         $user = User::updateOrCreate(
//             ['google_id' => $googleUser->id,
//             'name' => $googleUser->name,
//             'email' => $googleUser->email,
//             'password' => bcrypt(Str::random(12)),
//         ]
//         );
//         dd($user);
//     });
    

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



use App\Http\Controllers\AuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthentController;
use App\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuController::class, 'register']);
Route::post('/login', [AuController::class, 'login'])->name('login');


// Route::post('/forgot-password', [AuController::class, 'forgotPassword'])->name('auths.forgetPassword');
// Route::post('/forgot-password', [AuController::class, 'passwordForgetForm'])->name('auths.passwordForgetForm');


// Route::post('/password/reset', [AuController::class, 'resetPassword'])->name('-');;
// Route::post('/password/reset', [AuController::class, 'resetPassword']);

// Protected routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuController::class, 'logout']);
    Route::get('/user', [AuController::class, 'user']);
});


// Route::get('/forgot-password', [AuthentController::class, 'showForgetPasswordForm'])->name('auth.forgetpassword');
Route::post('/forgot-password', [AuthentController::class, 'submitForgetPasswordForm'])->name('auth.submitforgetpassword');


// Route::get('/reset-password/{token}', [AuthentController::class, 'showResetPasswordForm'])->name('auth.showresetpassword');


Route::post('/reset-password', [AuthentController::class, 'submitResetPasswordForm'])->name('auth.submitresetpassword');