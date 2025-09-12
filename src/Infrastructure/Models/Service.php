<?php
namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $table = 'services';

    protected $fillable = [
        'category_id', 'name', 'description', 'base_price'
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
