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
}