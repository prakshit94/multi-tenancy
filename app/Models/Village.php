<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Village.php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Village extends Model
{
    use LogsActivity;

    protected $fillable = [
        'village_name',
        'pincode',
        'post_so_name',
        'taluka_name',
        'district_name',
        'state_name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
