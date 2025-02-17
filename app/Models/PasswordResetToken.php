<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'resettable_id',
        'resettable_type',
        'token',
        'expires_on',
    ];

    protected $casts = [
        'expires_on' => 'datetime:Y-m-d H:i:s',
    ];

}
