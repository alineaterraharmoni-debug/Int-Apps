<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'customer_name',
        'category',
        'tcv',
        'gp_percentage',
        'rating',
        'expected_closing_date',
        'stage',
        'closed_at',
        'sales_id',
        'presales_id',
        'next_action',
        'notes',
    ];

    protected $casts = [
        'tcv' => 'decimal:2',
        'gp_percentage' => 'decimal:2',
        'expected_closing_date' => 'date',
        'closed_at' => 'date',
    ];

    const STAGES = [
        'mql' => 'MQL - Leads Awal',
        'sql' => 'SQL',
        'develop' => 'Develop',
        'won' => 'Closing - WON',
        'lost' => 'Closing - LOST',
    ];

    const CATEGORIES = [
        'cybersecurity' => 'Cybersecurity',
        'cctv' => 'CCTV / Surveillance',
        'data_center_networking' => 'Data Center Networking',
        'enterprise_networking' => 'Enterprise Networking',
        'web_development' => 'Web Development',
        'lainnya' => 'Lainnya',
    ];

    const RATINGS = [
        'high' => 'High',
        'med' => 'Medium',
        'low' => 'Low',
    ];

    public function sales(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'sales_id');
    }

    public function presales(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'presales_id');
    }

    public function engineers(): BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'opportunity_engineer');
    }

    // GP nominal dihitung otomatis dari TCV x GP%
    public function getGpNominalAttribute(): float
    {
        return round(((float) $this->tcv) * ((float) $this->gp_percentage) / 100, 2);
    }

    public function getStageLabelAttribute(): string
    {
        return self::STAGES[$this->stage] ?? $this->stage;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getRatingLabelAttribute(): string
    {
        return self::RATINGS[$this->rating] ?? $this->rating;
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($filters['stage'] ?? null, fn ($q, $v) => $q->where('stage', $v))
            ->when($filters['rating'] ?? null, fn ($q, $v) => $q->where('rating', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    }
}
