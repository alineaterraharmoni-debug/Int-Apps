<?php

namespace App\Livewire;

use App\Models\Document;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DocumentList extends Component
{
    use WithPagination;

    public string $typeFilter = '';
    public string $statusFilter = '';
    public string $search = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public bool $showFilters = false;

    public bool $showDetailModal = false;
    public ?int $detailId = null;

    public function mount(): void
    {
        // Abis save dari form, balik ke sini udah langsung ke-filter ke
        // jenis dokumen yang baru disimpen (?type=quotation dst).
        $this->typeFilter = request()->get('type', '');
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function resetListFilters(): void
    {
        $this->reset(['statusFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        // select() cuma narik kolom yang beneran kepake di list (bukan
        // narik terms/notes yang bisa panjang) — biar ringan pas datanya
        // udah banyak. with() dibatesin id+name doang dari relasi.
        $documents = Document::query()
            ->select(['id', 'type', 'status', 'number', 'doc_date', 'customer_id', 'vendor_id', 'total'])
            ->with(['customer:id,name', 'vendor:id,name', 'taxes:id,document_id,amount,direction'])
            ->when($this->typeFilter, fn ($q, $v) => $q->where('type', $v))
            ->when($this->statusFilter, fn ($q, $v) => $q->where('status', $v))
            ->when($this->dateFrom, fn ($q, $v) => $q->whereDate('doc_date', '>=', $v))
            ->when($this->dateTo, fn ($q, $v) => $q->whereDate('doc_date', '<=', $v))
            ->when($this->search, function ($q, $v) {
                $q->where(function ($qq) use ($v) {
                    $qq->where('number', 'like', "%{$v}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$v}%"))
                        ->orWhereHas('vendor', fn ($c) => $c->where('name', 'like', "%{$v}%"));
                });
            })
            ->orderByDesc('doc_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('livewire.document-list', [
            'documents' => $documents,
            'types' => Document::TYPES,
            'statuses' => Document::STATUSES,
            'canManage' => auth()->user()->hasPermission('document.manage'),
            'detailDocument' => $this->detailId
                ? Document::with(['customer', 'vendor', 'opportunity', 'items'])->find($this->detailId)
                : null,
        ]);
    }

    // Klik baris (di luar tombol PDF/Hapus) -> buka popup View Detail
    // read-only. Edit dipindah jadi tombol DI DALEM popup ini.
    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailId = null;
    }

    public function delete(int $id): void
    {
        if (! auth()->user()->hasPermission('document.manage')) {
            abort(403, 'Akun lo gak punya izin hapus dokumen.');
        }

        Document::findOrFail($id)->delete();
    }
}
