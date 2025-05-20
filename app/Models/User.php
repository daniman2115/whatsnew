<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'username',
        'user_type',
        'account_tier',
        'profile_picture',
        'bio',
        'language'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];



    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }




// Users that this user is following
public function following(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
}

// Users that follow this user
public function followers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
}



// app/Models/User.php
// app/Models/User.php
public function isFollowing(User $user): bool
{
    return $this->following()
        ->where('following_id', $user->id)
        ->exists();
}



public function isFollowedBy(User $user)
{
    return $this->followers()->where('follower_id', $user->id)->exists();
}



        

}
