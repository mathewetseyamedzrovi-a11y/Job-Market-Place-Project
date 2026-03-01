<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'job_id',
        'payer_id',
        'payee_id',
        'amount',
        'transaction_id',
        'status',
        'momo_response'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'momo_response' => 'array'
    ];

    /**
     * Get the job that this payment belongs to.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the user who made the payment (poster).
     */
    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    /**
     * Get the user who received the payment (worker).
     */
    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}