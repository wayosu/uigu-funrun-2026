<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RaceCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'quota' => 'integer',
        'collective_size' => 'integer',
        'bib_start' => 'integer',
        'bib_end' => 'integer',
        'bib_current' => 'integer',
        'is_active' => 'boolean',
        'registration_open_at' => 'datetime',
        'registration_close_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function sequence()
    {
        return $this->hasOne(RegistrationSequence::class);
    }
}
