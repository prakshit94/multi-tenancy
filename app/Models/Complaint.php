<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Complaint extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'reference_number',
        'order_id',
        'customer_id',
        'user_id',
        'type',
        'subject',
        'description',
        'status',
        'priority',
        'resolution',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Activity Log config
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'priority', 'resolution'])
            ->logOnlyDirty();
    }

    /**
     * Generate a unique reference number when creating a new complaint.
     */
    protected static function booted()
    {
        static::creating(function ($complaint) {
            if (empty($complaint->reference_number)) {
                $prefix = 'CMP-';
                // Generates something like CMP-20260228-A1B2C
                $complaint->reference_number = $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // Support agent/user handling it
    }
}
