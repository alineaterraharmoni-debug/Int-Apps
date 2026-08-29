<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'type', 'status', 'number', 'doc_date', 'opportunity_id', 'customer_id', 'vendor_id',
        'contact_name', 'ref_quotation_number', 'ref_po_number', 'ref_invoice_number',
        'terms', 'signatory_name', 'total',
    ];

    protected $casts = [
        'doc_date' => 'date',
        'total' => 'decimal:2',
    ];

    const TYPES = [
        'quotation' => 'Quotation',
        'invoice' => 'Invoice',
        'po' => 'Purchase Order',
        'bast' => 'BAST (Berita Acara Serah Terima)',
    ];

    const TYPE_CODE = [
        'quotation' => 'QUO',
        'invoice' => 'INV',
        'po' => 'PO',
        'bast' => 'BAST',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'final' => 'Final',
    ];

    // Korelasi jenis dokumen -> key checklist Next Action di Opportunity
    // (lihat Opportunity::NEXT_ACTION_ITEMS). Dokumen Quotation/PO = progress
    // stage Develop, BAST/Invoice = progress stage WON.
    const CHECKLIST_KEY_MAP = [
        'quotation' => 'create_quotation',
        'po' => 'create_po',
        'bast' => 'create_bast',
        'invoice' => 'create_invoice',
    ];

    protected static function booted(): void
    {
        // Checklist Next Action di Opty otomatis ke-centang/ke-uncheck
        // ngikutin ADA-GAKNYA dokumen final yang nge-link ke opty itu — bukan
        // dicentang manual. Draft SENGAJA gak ikut ngecentang (belum "beneran
        // jadi"), cuma status 'final' yang dihitung.
        static::saved(function (Document $document) {
            $document->syncOpportunityChecklist($document->opportunity_id, $document->type);
        });

        static::deleted(function (Document $document) {
            $document->syncOpportunityChecklist($document->opportunity_id, $document->type);
        });
    }

    public function syncOpportunityChecklist(?int $opportunityId, string $type): void
    {
        if (! $opportunityId) {
            return;
        }

        $key = self::CHECKLIST_KEY_MAP[$type] ?? null;
        if (! $key) {
            return;
        }

        $opty = Opportunity::find($opportunityId);
        if (! $opty) {
            return;
        }

        $hasFinalDoc = self::where('opportunity_id', $opportunityId)
            ->where('type', $type)
            ->where('status', 'final')
            ->exists();

        $checklist = $opty->next_action_checklist ?? [];
        $checklist[$key] = $hasFinalDoc;
        $opty->next_action_checklist = $checklist;
        $opty->saveQuietly();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DocumentItem::class)->orderBy('sort_order');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getRecipientNameAttribute(): ?string
    {
        return $this->type === 'po'
            ? $this->vendor?->name
            : $this->customer?->name;
    }

    /**
     * Generate nomor dokumen sesuai pola existing Alinea: 006-QUO/AD/05/26
     * Sequence auto-increment per jenis dokumen (bukan reset tiap bulan).
     */
    public static function generateNumber(string $type): string
    {
        $seq = self::where('type', $type)->count() + 1;
        $code = self::TYPE_CODE[$type] ?? strtoupper($type);

        return sprintf('%03d-%s/AD/%s/%s', $seq, $code, now()->format('m'), now()->format('y'));
    }
}
