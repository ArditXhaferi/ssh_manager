<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SshConnection extends Model
{
    protected $fillable = [
        'name',
        'host',
        'username',
        'port',
        'private_key',
        'password',
        'locked'
    ];

    protected $casts = [
        'port' => 'integer',
    ];
}