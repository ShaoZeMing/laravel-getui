<?php
namespace GeTui\App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

/**
 * App\Entities\UserUnion
 *
 * @mixin \Eloquent
 */
class MessagePush extends Model
{

    protected $table = 'message_pushes';

    protected $fillable = [
        'title',
        'content',
        'title',
        'origin_url',
        'short_url',
        'uid',
        'type',
        'status',
        'is_sent_all',
        'created_at',
        'msg_id',
        'updated_at',
        'lesson_id',
        'video_id',
    ];

    // public $timestamps = false;
}