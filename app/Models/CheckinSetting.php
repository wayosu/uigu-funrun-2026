<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'checkin_start_time' => 'datetime',
        'checkin_end_time' => 'datetime',
        'allow_duplicate_scan' => 'boolean',
        'require_photo_verification' => 'boolean',
        'auto_print_bib' => 'boolean',
    ];
}
