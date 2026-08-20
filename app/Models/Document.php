<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'type', 'number', 'doc_date', 'opportunity_id', 'customer_id', 'vendor_id',
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
