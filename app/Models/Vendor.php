<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['name', 'type', 'contact_name', 'phone', 'address'];

    protected $casts = [
        'type' => 'array',
    ];

    // Label lini produk yang vendor ini kuasain, dipisah koma — dipake di
    // tampilan list & dropdown biar gampang di-scan.
    public function getTypeLabelsAttribute(): string
    {
        if (empty($this->type)) {
            return '';
        }

        return collect($this->type)
            ->map(fn ($key) => Opportunity::CATEGORIES[$key] ?? $key)
            ->implode(', ');
    }

    // Vendor yang nge-cover kategori tertentu — dipake buat nyaranin vendor
    // relevan di checklist "Cari harga dari Disti/Vendor" (Leads) dan di
    // dropdown vendor form Dokumen (PO).
    public function scopeForCategory($query, ?string $category)
    {
        if (! $category) {
            return $query;
        }

        return $query->whereJsonContains('type', $category);
    }
}
