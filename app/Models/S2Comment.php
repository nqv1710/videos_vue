<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class S2Comment extends Model
{
    protected $table = 's2_comment';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'message',
        'created_at',
        'user_id',
        'video_id',
        'type',
    ];

    public function video()
    {
        return $this->belongsTo(S2Video::class, 'video_id', 'id');
    }
}
