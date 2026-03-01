<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'skills',
        'experience_years',
        'id_photo_path',
        'availability',
        'hourly_rate',
        'location',
        'verified',
        'completed_jobs_count'
    ];

    protected $casts = [
        'verified' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'experience_years' => 'integer',
        'completed_jobs_count' => 'integer'
    ];

    /**
     * Get the user that owns this profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment the completed jobs count.
     */
    public function incrementCompletedJobs()
    {
        $this->increment('completed_jobs_count');
    }
}