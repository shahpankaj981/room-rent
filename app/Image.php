<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'filename', 'mime', 'originalFilename',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

//    public function user()
//    {
//        return $this->belongsTo('App\User');
//    }

}
