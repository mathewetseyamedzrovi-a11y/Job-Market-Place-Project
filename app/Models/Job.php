<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'marketplace_jobs';

    protected $fillable = [
        'poster_id', 
        'title', 
        'description', 
        'category_id',
        'budget', 
        'location', 
        'latitude', 
        'longitude',
        'urgency', 
        'duration', 
        'status'
    ];

    /**
     * Get the user who posted this job.
     */
    public function poster()
    {
        return $this->belongsTo(User::class, 'poster_id');
    }

    /**
     * Get the category of this job.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all applications for this job.
     */
    public function applications()
    {
        return $this->hasMany(Application::class, 'job_id');
    }

    /**
     * Get all ratings for this job.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Mark job as completed and update worker stats.
     */
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->save();
        
        // Increment worker's completed jobs count
        $application = $this->applications()->where('status', 'hired')->first();
        if ($application && $application->worker && $application->worker->workerProfile) {
            $application->worker->workerProfile->incrementCompletedJobs();
        }
    }
}