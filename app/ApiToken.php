<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $fillable = [
        'userId',
        'apiToken',
        'deviceToken',
        'deviceType'
        ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
