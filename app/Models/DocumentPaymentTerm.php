<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPaymentTerm extends Model
{
    protected $fillable = ['label', 'percentage', 'amount', 'due_date', 'sort_order'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
