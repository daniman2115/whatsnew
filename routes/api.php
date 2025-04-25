<?php
use App\Http\Controllers\AuthentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthentController::class, 'register']);

Route::post('/login', [AuthentController::class, 'login']);



// Protected routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthentController::class, 'logout']);
        Route::get('/user', [AuthentController::class, 'user']);
        Route::post('/forgot-password', [AuthentController::class, 'submitForgetPasswordForm'])->name('auth.submitforgetpassword');

        Route::post('/reset-password', [AuthentController::class, 'submitResetPasswordForm'])->name('auth.submitresetpassword');
        
        Route::post('/upload', [VideoController::class,"uploadChunk"]);
        
        Route::get('/list', [VideoController::class,"index"]);
        
        Route::get('/show/{video}', [VideoController::class,"show"]);

        
        Route::post('/like/{video}', [LikeController::class,"like"]);
        
        Route::post('/comment/{video}', [CommentController::class,"comment"]);
        
        Route::get('/feed/for-you', [VideoController::class, 'forYou']);
        Route::get('/feed/following/{id}', [VideoController::class, 'following']);
        Route::get('/feed/discover', [VideoController::class, 'discover']);

        Route::get('/profile/{id}', [ProfileController::class,"showProfile"]);

        Route::get('/profile/followers/{id}', [FollowController::class,"showFollowers"]);
        Route::get('/profile/following/{id}', [FollowController::class,"showFollowing"]);

        Route::post('/videos/search', [VideoController::class, 'search']);
        

});



