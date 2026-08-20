<?php

namespace App\Livewire;

use App\Models\Document;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DocumentList extends Component
{
    public string $typeFilter = '';
    public string $search = '';

    public function render()
    {
        $documents = Document::with(['customer', 'vendor'])
            ->when($this->typeFilter, fn ($q, $v) => $q->where('type', $v))
            ->when($this->search, function ($q, $v) {
                $q->where(function ($qq) use ($v) {
                    $qq->where('number', 'like', "%{$v}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$v}%"))
                        ->orWhereHas('vendor', fn ($c) => $c->where('name', 'like', "%{$v}%"));
                });
            })
            ->orderByDesc('doc_date')
            ->orderByDesc('id')
            ->get();

        return view('livewire.document-list', [
            'documents' => $documents,
            'types' => Document::TYPES,
            'canManage' => auth()->user()->hasPermission('document.manage'),
        ]);
    }
}
