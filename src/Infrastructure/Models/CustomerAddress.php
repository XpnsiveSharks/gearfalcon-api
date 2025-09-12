<?php
namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use SoftDeletes;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id', 'house_number', 'street', 'barangay',
        'city', 'province', 'region', 'postal_code', 'is_primary'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'customer_address_id');
    }
}
