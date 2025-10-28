<?php
namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobAssignment extends Model
{
    use SoftDeletes;

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;
    protected $table = 'job_assignments';

    protected $fillable = [
        'job_id', 'technician_id', 'assigned_at'
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }
}
