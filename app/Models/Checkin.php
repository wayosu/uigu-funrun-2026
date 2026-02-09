<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'checkin_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
