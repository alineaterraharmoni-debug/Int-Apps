<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'roles',
        'is_active',
    ];

    protected $casts = [
        'roles' => 'array',
        'is_active' => 'boolean',
    ];

    public function opportunitiesAsSales(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'sales_id');
    }

    public function opportunitiesAsPresales(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'presales_id');
    }

    public function opportunitiesAsEngineer(): BelongsToMany
    {
        return $this->belongsToMany(Opportunity::class, 'opportunity_engineer');
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? [], true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->whereJsonContains('roles', $role);
    }
}
