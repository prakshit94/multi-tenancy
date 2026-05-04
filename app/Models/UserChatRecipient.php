<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserChatRecipient extends Model
{
    use LogsActivity;

    protected $fillable = [
        "message_id",
        "recipient_id",
        "recipient_group_id",
        "is_read",
        "seen_date"
    ];

    protected $casts = [
        'seen_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function message()
    {
        return $this->belongsTo(UserChat::class, 'message_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
