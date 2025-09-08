<?php
namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Technician extends Model
{
    use SoftDeletes;

    protected $table = 'technicians';

    protected $fillable = [
        'user_id', 'specialization', 'certification', 'experience_years'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'technician_skills')
            ->withPivot('proficiency')
            ->withTimestamps();
    }

    public function jobAssignments()
    {
        return $this->hasMany(JobAssignment::class);
    }
}
