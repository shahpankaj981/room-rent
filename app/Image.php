<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table    = 'images';
    protected $fillable = [
        'filename',
        'mime',
        'original_filename',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
//
//    public function posts()
//    {
//        return $this->belongsTo
//    }
}
