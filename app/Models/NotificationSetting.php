<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'delay_seconds' => 'integer',
        'max_send_per_minute' => 'integer',
        'retry_limit' => 'integer',
        'fonnte_token' => 'encrypted', // Encrypt sensitive API token
    ];
}
