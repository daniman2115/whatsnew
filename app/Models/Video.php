<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory;



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


}
