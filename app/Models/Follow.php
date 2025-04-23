<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    /** @use HasFactory<\Database\Factories\FollowFactory> */
    use HasFactory;


    public $timestamps = false; // only 'created_at'
    public $incrementing = false; // no auto-increment ID
    protected $primaryKey = ['follower_id', 'following_id'];

    protected $fillable = [
        'follower_id',
        'following_id',
        'created_at',
    ];

}
