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
        'terms', 'signatory_name', 'signatory_title', 'total',
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
     * Sekarang PER-BULAN-PER-TAHUN (bukan running total sepanjang waktu) —
     * ngikutin tanggal yang diisi di form ($docDate), bukan tanggal hari ini.
     * Nyari angka urut TERKECIL yang belum kepake buat kombinasi jenis+bulan+
     * tahun itu, jadi kalau ada nomor yang diubah manual (misal 003), nomor
     * otomatis berikutnya bakal ngisi gap yang masih kosong dulu (002) baru
     * lompat ke abis yang udah kepake (004).
     */
    public static function generateNumber(string $type, ?string $docDate = null): string
    {
        $date = $docDate ? \Illuminate\Support\Carbon::parse($docDate) : now();
        $month = $date->format('m');
        $year = $date->format('y');
        $code = self::TYPE_CODE[$type] ?? strtoupper($type);
        $suffix = sprintf('-%s/AD/%s/%s', $code, $month, $year);

        $usedSeqs = self::where('type', $type)
            ->where('number', 'like', '%'.$suffix)
            ->pluck('number')
            ->map(function ($n) use ($suffix) {
                $prefix = \Illuminate\Support\Str::before($n, $suffix);

                return (int) $prefix;
            })
            ->all();

        $seq = 1;
        while (in_array($seq, $usedSeqs, true)) {
            $seq++;
        }

        return sprintf('%03d%s', $seq, $suffix);
    }

    /**
     * Parse terms jadi baris-baris terstruktur (nomor + teks kepisah), biar
     * di PDF bisa dirender pake hanging-indent yang rapi — sebelumnya teks
     * panjang yang wrap ke baris ke-2 nempel rata kiri, keliatan kayak mulai
     * poin baru padahal masih poin yang sama.
     */
    public function getTermsLinesAttribute(): array
    {
        if (! $this->terms) {
            return [];
        }

        return collect(explode("\n", $this->terms))
            ->map(function ($line) {
                $line = rtrim($line);
                if (preg_match('/^\s*(\d+)\.\s*(.*)$/', $line, $m)) {
                    return ['num' => $m[1], 'text' => $m[2]];
                }

                return ['num' => null, 'text' => $line];
            })
            ->all();
    }

    // Kolom Credit di tabel item PDF cuma ditampilin kalau MINIMAL SATU item
    // beneran isi credits_required — kalau semua item cuma pake Unit biasa,
    // kolom Credit disembunyiin total biar tabel gak ada kolom kosong.
    public function getHasCreditsAttribute(): bool
    {
        return $this->items->contains(fn ($i) => $i->credits_required !== null && $i->credits_required !== '');
    }
}
