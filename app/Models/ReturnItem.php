<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReturnItem extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];
    protected $casts = [
        'quantity' => 'decimal:3',
        'quantity_received' => 'decimal:3',
    ];

    public function returnOrder()
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
