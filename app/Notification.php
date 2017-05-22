<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'threadId',
        'messageId',
        'visibility',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function message()
    {
        return $this->belongsTo('App\Message', 'messageId');
    }

    public function thread()
    {
        return $this->belongsTo('App\thread', 'threadId');
    }
}
