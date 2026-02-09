<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JerseySize extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function raceCategory()
    {
        return $this->belongsTo(RaceCategory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('race_category_id');
    }

    public function scopeForRaceCategory($query, $raceCategoryId)
    {
        return $query->where(function ($q) use ($raceCategoryId) {
            $q->whereNull('race_category_id')
                ->orWhere('race_category_id', $raceCategoryId);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helper Methods
    public function isGlobal(): bool
    {
        return is_null($this->race_category_id);
    }
}
