<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Post extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userId','title', 'location', 'latitude', 'longitude', 'numberOfRooms','description','price','postType',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function image()
    {
        return $this->hasMany('App\Image', 'postId');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getTitleAttribute($value)
    {
        return ucfirst($value);
    }

    public function getLocationAttribute($value)
    {
        return ucfirst($value);
    }

    public function getDescriptionAttribute($value)
    {
        return ucfirst($value);
    }
}
