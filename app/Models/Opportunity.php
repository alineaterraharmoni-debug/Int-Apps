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
        'customer_id',
        'category',
        'tcv',
        'gp_percentage',
        'rating',
        'expected_closing_date',
        'stage',
        'closed_at',
        'lost_category',
        'lost_reason',
        'won_category',
        'won_reason',
        'sales_id',
        'presales_id',
        'next_action',
        'next_action_checklist',
        'notes',
    ];

    protected $casts = [
        'tcv' => 'decimal:2',
        'gp_percentage' => 'decimal:2',
        'expected_closing_date' => 'date',
        'closed_at' => 'date',
        'next_action_checklist' => 'array',
    ];

    // Checklist "next action" nyesuain stage — flow bisnis Alinea:
    // Leads butuh cari harga ke Disti/Vendor, Develop butuh Quotation ATAU PO,
    // WON butuh BAST ATAU Invoice, Lost gak butuh apa-apa. "Atau" artinya
    // salah satu aja cukup buat dianggap "ada progress", gak wajib dua-duanya.
    const NEXT_ACTION_ITEMS = [
        'leads' => [
            'cari_harga_disti' => 'Cari harga dari Disti/Vendor',
        ],
        'develop' => [
            'create_quotation' => 'Create Quotation',
            'create_po' => 'Create PO',
        ],
        'won' => [
            'create_bast' => 'Create BAST',
            'create_invoice' => 'Create Invoice',
        ],
        'lost' => [],
    ];

    const STAGES = [
        'leads' => 'Leads',
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

    const LOST_CATEGORIES = [
        'harga' => 'Harga Kalah Kompetitif',
        'kompetitor' => 'Kalah dari Kompetitor',
        'budget' => 'Customer Gak Ada Budget',
        'batal' => 'Project Dibatalkan Customer',
        'timeline' => 'Timeline Gak Sesuai',
        'internal' => 'Kendala Internal Alinea',
        'lainnya' => 'Lainnya',
    ];

    const WON_CATEGORIES = [
        'harga' => 'Harga Kompetitif',
        'relasi' => 'Relasi / Trust Existing',
        'produk' => 'Kecocokan Produk / Solusi',
        'timing' => 'Timing Pas',
        'presales' => 'Presales & Demo Kuat',
        'lainnya' => 'Lainnya',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

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

    public function getLostCategoryLabelAttribute(): ?string
    {
        return $this->lost_category ? (self::LOST_CATEGORIES[$this->lost_category] ?? $this->lost_category) : null;
    }

    public function getWonCategoryLabelAttribute(): ?string
    {
        return $this->won_category ? (self::WON_CATEGORIES[$this->won_category] ?? $this->won_category) : null;
    }

    // Opty dianggap "telat" kalau ekspektasi closing-nya udah lewat tapi
    // masih nyangkut di Leads/Develop (belum di-close WON atau LOST).
    public function getIsOverdueAttribute(): bool
    {
        return $this->expected_closing_date !== null
            && $this->expected_closing_date->isPast()
            && ! in_array($this->stage, ['won', 'lost'], true);
    }

    // Item checklist next-action yang relevan buat stage opty ini sekarang.
    public function nextActionItems(): array
    {
        return self::NEXT_ACTION_ITEMS[$this->stage] ?? [];
    }

    // "Belum ada progress sama sekali" — dipake buat summary di Home.
    // Bukan "belum semua checklist tercentang", soalnya "atau" artinya
    // satu tercentang aja udah dianggap ada progress.
    public function hasPendingChecklist(): bool
    {
        $items = $this->nextActionItems();
        if (empty($items)) {
            return false;
        }

        $checklist = $this->next_action_checklist ?? [];

        foreach (array_keys($items) as $key) {
            if (! empty($checklist[$key])) {
                return false;
            }
        }

        return true;
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($filters['stage'] ?? null, fn ($q, $v) => $q->where('stage', $v))
            ->when($filters['rating'] ?? null, fn ($q, $v) => $q->where('rating', $v))
            ->when($filters['customer_id'] ?? null, fn ($q, $v) => $q->where('customer_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    }
}
