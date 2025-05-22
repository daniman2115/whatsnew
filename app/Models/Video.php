<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory;

    // protected $withCount = ['likes'];
    protected $appends = ['comment_count'];




    protected $fillable = [
        'name',
        'path',
        'title',
        'thumbnail_path',
        'thumbnail_url',
        'description',
        'url',
        'file',
        'bio',
        'language',
        'transcribed_text',
        'credibility_score',
        'last_credibility_check',

    ];


    public function likes(): HasMany
{
    return $this->HasMany(Like::class);
}

public function comments(): HasMany
{
    return $this->hasMany(Comment::class)->with('user');
}



public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}



public function getCommentCountAttribute()
{
    return $this->comments()->count();
}

}




