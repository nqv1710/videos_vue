<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class S2Video extends Model
{
    protected $table = 's2_videos';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'url',
        'created_at',
        'hashtag',
        'topic',
        'description',
        'typeVideo',
        'linkShare',
        'created_by',
    ];

    protected $casts = [
        'topic' => 'array',
    ];

    public function comments()
    {
        return $this->hasMany(S2Comment::class, 'video_id', 'id');
    }
}
