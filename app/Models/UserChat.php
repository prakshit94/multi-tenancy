<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserChat extends Model
{
    use LogsActivity;

    protected $fillable = [
        "subject",
        "body",
        "attachment",
        "sender_id",
        "group_id",
        "parent_message_id",
        "starred",
        "forward_msg_id",
        "s3_url"
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->hasMany(UserChatRecipient::class, 'message_id');
    }

    public function parentMessage()
    {
        return $this->belongsTo(UserChat::class, 'parent_message_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
