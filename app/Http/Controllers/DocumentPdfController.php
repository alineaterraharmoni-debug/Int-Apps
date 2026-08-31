<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentPdfController extends Controller
{
    public function export(int $id)
    {
        $doc = Document::with(['items', 'customer', 'vendor', 'opportunity', 'taxes', 'paymentTerms'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.document', ['doc' => $doc])->setPaper('a4', 'portrait');

        // Dokumen Draft belum punya nomor sama sekali — kalau dibiarin
        // str_replace(null) hasilnya string kosong, jadi nama file cuma
        // ".pdf" doang (gak ada nama & keliatan kayak gak ke-extract
        // ekstensinya). Draft dikasih nama fallback yang jelas & unik.
        $baseName = $doc->number
            ? str_replace('/', '-', $doc->number)
            : 'Draft-'.(Document::TYPE_CODE[$doc->type] ?? strtoupper($doc->type)).'-'.$doc->id;

        return $pdf->stream($baseName.'.pdf');
    }
}
