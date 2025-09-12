<?php
namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $table = 'quotes';

    protected $fillable = [
        'customer_id',
        'customer_address_id',
        'cart_id',
        'total_amount',
        'status',
        'valid_until',
    ];

    /**
     * Relationships
     */

    // A quote belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // A quote is tied to a customer address
    public function customerAddress()
    {
        return $this->belongsTo(CustomerAddress::class);
    }

    // A quote can be based on a cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // A quote can later become a job
    public function job()
    {
        return $this->hasOne(Job::class);
    }
}
