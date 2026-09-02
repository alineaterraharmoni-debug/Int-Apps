<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'industry',
        'pic_name',
        'pic_phone',
        'pic_email',
        'pics',
        'address',
        'is_focus',
        'notes',
    ];

    protected $casts = [
        'is_focus' => 'boolean',
        'pics' => 'array',
    ];

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"));
    }

    public function scopeFocus($query)
    {
        return $query->where('is_focus', true);
    }
}