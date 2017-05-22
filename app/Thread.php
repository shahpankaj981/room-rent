<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Thread extends Model
{
    use Notifiable;

    protected $fillable = [
      'senderId',
        'recipientId'
    ];

    public function notifications()
    {
        return $this->hasMany('App\Notification','threadId');
    }

    public function user()
    {
        return $this->belongsTo('App\User','recipientId');
    }

    public function visibleNotifications($id)
    {
        return $this->notifications()->where('visibility', $id);
    }
}
