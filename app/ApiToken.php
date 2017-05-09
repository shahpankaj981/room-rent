<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $table = 'apiTokens';

    protected $fillable = [
        'userId', 'apiToken', 'deviceToken', 'deviceType'
        ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
