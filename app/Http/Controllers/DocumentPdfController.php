<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentPdfController extends Controller
{
    public function export(int $id)
    {
        $doc = Document::with(['items', 'customer', 'vendor', 'opportunity'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.document', ['doc' => $doc])->setPaper('a4', 'portrait');

        $filename = str_replace('/', '-', $doc->number).'.pdf';

        return $pdf->stream($filename);
    }
}
