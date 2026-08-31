<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPaymentTerm extends Model
{
    // Kolom `due_date` masih ada di database (gak dihapus, harmless kalau
    // nganggur) tapi udah gak dipake lagi di form/PDF sesuai permintaan.
    protected $fillable = ['label', 'percentage', 'amount', 'sort_order'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
