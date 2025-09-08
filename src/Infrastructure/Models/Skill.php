<?php
namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes;

    protected $table = 'skills';

    protected $fillable = [
        'name', 'description'
    ];

    public function technicians()
    {
        return $this->belongsToMany(Technician::class, 'technician_skills')
            ->withPivot('proficiency')
            ->withTimestamps();
    }
}
