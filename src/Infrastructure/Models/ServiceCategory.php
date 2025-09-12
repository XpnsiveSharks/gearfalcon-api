<?php
namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use SoftDeletes;

    protected $table = 'service_categories';

    protected $fillable = [
        'name', 'description'
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
