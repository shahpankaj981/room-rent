<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'messageBody',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function notifications()
    {
        return $this->hasMany('App\Notification', 'messageId');
    }
}
