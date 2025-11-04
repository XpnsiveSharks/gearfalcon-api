<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $table = 'users';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_verified',
        'verification_code',
        'verification_code_expires_at',
        'password_reset_code',
        'password_reset_code_expires_at',
        'password_reset_verified',
        'email_verified_at',
    ];

    // Relationships
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function technician()
    {
        return $this->hasOne(Technician::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'customer_id', 'id');
    }
}
