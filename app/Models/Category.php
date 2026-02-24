<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 
        'icon', 
        'description'
    ];

    /**
     * Get the jobs in this category.
     */
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}