<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'job_id',
        'rater_id',
        'ratee_id',
        'rating',
        'review'
    ];

    protected $casts = [
        'rating' => 'integer'
    ];

    /**
     * Get the job that this rating belongs to.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the user who gave the rating.
     */
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    /**
     * Get the user who received the rating.
     */
    public function ratee()
    {
        return $this->belongsTo(User::class, 'ratee_id');
    }
}