<?php
use App\Http\Controllers\AudioExtractionController;
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
        
        Route::get('/show', [VideoController::class,"show"]);
        // Route::get('/show/{id}', [VideoController::class,"showProfile"]);

        
        Route::post('/like/{video}', [LikeController::class,"like"]);
        Route::post('/unlike/{video}', [LikeController::class,"unLike"]);
        
        Route::post('/comment/{video}', [CommentController::class,"comment"]);
        Route::get('/showcomments/{video}', [CommentController::class,"showComments"]);

        Route::post('/follow/{user}', [FollowController::class,"follow"]);
        Route::post('/unfollow/{user}', [FollowController::class,"unfollow"]);
        
        Route::get('/feed/for-you', [VideoController::class, 'forYou']);
        Route::get('/feed/following/{id}', [VideoController::class, 'following']);
        Route::get('/feed/discover/{id}', [VideoController::class, 'discover']);

        Route::get('/profile/{id}', [ProfileController::class,"showProfile"]);
        Route::post('/edit/{id}', [ProfileController::class,"editProfile"]);
        // Route::get('/profile/{user}', [ProfileController::class, 'show']);

        Route::get('/profile/followers/{id}', [FollowController::class,"showFollowers"]);
        Route::get('/profile/following/{id}', [FollowController::class,"showFollowing"]);

        Route::get('/showtotallikes/{id}', [LikeController::class, 'showUserLikes']);

        Route::post('/videos/search', [VideoController::class, 'search']);


        // Route::post('/check-fake-news/{id}', [VideoController::class, 'checkVideo']);

        // Route::post('/recommend-videos', [VideoController::class, 'getRecommendedVideos']);

        
        // Route::post('/extractaudio', [VideoController::class, 'extractAudio']);

        Route::post('/extract-audio', [AudioExtractionController::class, 'extractAudio']);

Route::post('/videos/update-credibility', [VideoController::class, 'updateCredibility'])
    ->middleware('auth:api');
        

});



