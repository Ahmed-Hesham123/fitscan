<?php

namespace App\Models;

use App\Services\FilterDataService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'status',
        'locale',
    ];

    protected $casts = [
        'status' => 'boolean',
        'email_verified_at_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $searchableColumns = [
        'user_id' => 'users.user_id',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email',
        'phone' => 'phone',
        'created_at' => 'users.created_at',
        'updated_at' => 'users.updated_at',
    ];
    protected $sortableColumns = [
        'user_id' => 'user_id',
        'user_name' => 'user_name',
        'email' => 'email',
        'phone' => 'phone',
        'status' => 'users.status',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    public function scopeApplyFiltersWithPagination($query, $request)
    {
        return FilterDataService::addFiltering($request, $query, $this->sortableColumns, $this->searchableColumns, true);
    }

    public function scopeApplyFilters($query, $request)
    {
        return FilterDataService::addFiltering($request, $query, $this->sortableColumns, $this->searchableColumns);
    }
}
