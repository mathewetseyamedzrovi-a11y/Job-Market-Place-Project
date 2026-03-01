<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'job_id',
        'worker_id',
        'cover_message',
        'quoted_price',
        'status'
    ];

    /**
     * Get the job that this application belongs to.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the worker (user) that owns this application.
     */
    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
}