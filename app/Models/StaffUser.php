<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;


class StaffUser extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    use SoftDeletes;


    protected $primaryKey = 'staff_user_id';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'locale',
        // 'timezone_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // format created_at, updated_at and fix timezone issue
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // public function timezone()
    // {
    //     return $this->belongsTo(Timezone::class, 'timezone_id', 'timezone_id');
    // }
}
