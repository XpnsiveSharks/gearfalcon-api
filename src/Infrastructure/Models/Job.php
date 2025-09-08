<?php
namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $table = 'jobs';

    protected $fillable = [
        'customer_id', 'customer_address_id', 'service_id',
        'cart_id', 'status', 'scheduled_date', 'completed_date', 'notes'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->belongsTo(CustomerAddress::class, 'customer_address_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function assignments()
    {
        return $this->hasMany(JobAssignment::class);
    }
}
