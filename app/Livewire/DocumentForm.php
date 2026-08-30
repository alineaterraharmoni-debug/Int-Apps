<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Opportunity;
use App\Models\Vendor;
use App\Support\DocumentTerms;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DocumentForm extends Component
{
    public ?int $editingId = null;
    public string $type = 'quotation';
    // Nomor SENGAJA gak di-generate di awal lagi — Draft gak punya nomor
    // sama sekali. Nomor cuma keisi otomatis pas beneran di-finalisasi
    // ("Simpan Dokumen"), atau kalau user ngetik manual sebelum itu.
    public ?string $number = null;
    public string $doc_date;

    public ?int $opportunity_id = null;
    public ?int $customer_id = null;
    public ?int $vendor_id = null;
    public string $contact_name = '';

    public string $ref_quotation_number = '';
    public string $ref_po_number = '';
    public string $ref_invoice_number = '';

    public string $terms = '';
    public string $signatory_name = 'Teddy Syach';
    public string $signatory_title = '';

    public array $items = [];

    public function mount(?int $id = null, ?string $type = null): void
    {
        $this->doc_date = now()->toDateString();

        if ($id) {
            $doc = Document::with('items')->findOrFail($id);
            $this->editingId = $doc->id;
            $this->type = $doc->type;
            $this->number = $doc->number;
            $this->doc_date = $doc->doc_date->toDateString();
            $this->opportunity_id = $doc->opportunity_id;
            $this->customer_id = $doc->customer_id;
            $this->vendor_id = $doc->vendor_id;
            $this->contact_name = $doc->contact_name ?? '';
            $this->ref_quotation_number = $doc->ref_quotation_number ?? '';
            $this->ref_po_number = $doc->ref_po_number ?? '';
            $this->ref_invoice_number = $doc->ref_invoice_number ?? '';
            $this->terms = $doc->terms ?? '';
            $this->signatory_name = $doc->signatory_name;
            $this->signatory_title = $doc->signatory_title ?? '';
            $this->items = $doc->items->map(fn ($i) => [
                'group_label' => $i->group_label,
                'product_type' => $i->product_type,
                'description' => $i->description,
                'qty' => (string) $i->qty,
                'unit' => $i->unit,
                'credits_required' => $i->credits_required,
                'unit_price' => (string) $i->unit_price,
            ])->toArray();
        } else {
            $this->type = in_array($type, array_keys(Document::TYPES)) ? $type : 'quotation';
            $this->terms = DocumentTerms::default($this->type);
            $this->addItem();
        }
    }

    // Dipanggil dari typeahead Customer di blade lewat method langsung
    // (bukan $wire.set) — biar dijamin ke-commit seketika, gak ada resiko
    // "kepilih tapi datanya gak keisi" kayak yang kemarin kejadian.
    public function pickCustomer(?int $id): void
    {
        $this->customer_id = $id;
        $this->contact_name = '';
    }

    public function pickVendor(?int $id): void
    {
        $this->vendor_id = $id;
        $this->contact_name = '';
    }

    public function render()
    {
        return view('livewire.document-form', [
            'types' => Document::TYPES,
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
            'opportunities' => $this->opportunityOptions(),
            'contactOptions' => $this->contactOptions(),
        ]);
    }

    /**
     * Opty yang bisa di-link nyesuain jenis dokumen:
     * - Quotation & PO  : opty yang masih jalan (Leads/Develop) — belum Closing.
     * - Invoice & BAST  : opty yang udah Closing WON, ATAU yang udah pernah
     *                     dibuatin Quotation/PO (walau masih di stage Develop).
     * - Lost SELALU dikecualikan dari semua jenis dokumen.
     * Opty yang lagi ke-link ke dokumen ini (pas edit) SELALU ikut muncul
     * di daftar apapun stage-nya, biar gak "ilang" pas buka form edit lama.
     */
    private function opportunityOptions()
    {
        return Opportunity::with('customer:id,name')
            ->where(function ($q) {
                if (in_array($this->type, ['quotation', 'po'], true)) {
                    $q->whereIn('stage', ['leads', 'develop']);
                } elseif (in_array($this->type, ['invoice', 'bast'], true)) {
                    $q->where('stage', '!=', 'lost')
                        ->where(function ($qq) {
                            $qq->where('stage', 'won')
                                ->orWhereHas('documents', fn ($qd) => $qd->whereIn('type', ['quotation', 'po']));
                        });
                } else {
                    $q->where('stage', '!=', 'lost');
                }

                // Opty yang lagi ke-link (pas edit) selalu ikut muncul, apapun
                // stage-nya sekarang — biar gak "ilang" dari dropdown pas
                // buka form edit dokumen lama.
                if ($this->opportunity_id) {
                    $q->orWhere('id', $this->opportunity_id);
                }
            })
            ->orderByDesc('created_at')
            ->limit(150)
            ->get(['id', 'title', 'customer_id']);
    }

    /**
     * Kontak yang bisa dipilih buat "Contact Name" — narik dari data
     * Customer (pic_name, satu doang) atau Vendor (contacts[], bisa banyak).
     * Selalu ada opsi kosong di awal buat "gak ada PIC".
     */
    private function contactOptions(): array
    {
        if ($this->type === 'po' && $this->vendor_id) {
            $vendor = Vendor::find($this->vendor_id);

            return collect($vendor?->contacts ?? [])
                ->pluck('name')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if ($this->type !== 'po' && $this->customer_id) {
            $customer = Customer::find($this->customer_id);

            return $customer?->pic_name ? [$customer->pic_name] : [];
        }

        return [];
    }

    public function addItem(): void
    {
        $this->items[] = [
            'group_label' => '',
            'product_type' => '',
            'description' => '',
            'qty' => '1',
            'unit' => '',
            'credits_required' => '',
            'unit_price' => '0',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // Dipanggil dari typeahead Opty di blade — gabungin set id + auto-isi
    // customer jadi satu network call.
    public function pickOpportunity(?int $id): void
    {
        $this->opportunity_id = $id;

        if (! $id) {
            return;
        }

        $opty = Opportunity::find($id);
        if ($opty && $this->type !== 'po') {
            $this->customer_id = $opty->customer_id;
        }

        // Invoice narik nomor referensi Quotation/PO final punya opty ini
        // secara OTOMATIS, plus nyalin item dari Quotation-nya (tetep bisa
        // diedit manual abis itu, gak ke-lock). Kalau PO-nya gak ada,
        // field referensinya dibiarin kosong aja.
        if ($this->type === 'invoice') {
            $quotation = Document::where('opportunity_id', $id)
                ->where('type', 'quotation')
                ->where('status', 'final')
                ->latest('doc_date')
                ->first();

            $po = Document::where('opportunity_id', $id)
                ->where('type', 'po')
                ->where('status', 'final')
                ->latest('doc_date')
                ->first();

            $this->ref_quotation_number = $quotation?->number ?? '';
            $this->ref_po_number = $po?->number ?? '';

            if ($quotation) {
                $quotation->load('items');
                $this->items = $quotation->items->map(fn ($i) => [
                    'group_label' => $i->group_label,
                    'product_type' => $i->product_type,
                    'description' => $i->description,
                    'qty' => (string) $i->qty,
                    'unit' => $i->unit,
                    'credits_required' => $i->credits_required,
                    'unit_price' => (string) $i->unit_price,
                ])->toArray();

                if (empty($this->items)) {
                    $this->addItem();
                }
            }
        }
    }

    private function calculateTotal(): float
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['qty'] ?? 0) * (float) ($i['unit_price'] ?? 0));
    }

    /**
     * Tombol utama "Simpan Dokumen" = finalisasi. Nomor OTOMATIS di-generate
     * di sini kalau masih kosong (draft yang belum ada nomor, atau dokumen
     * baru langsung difinalisasi tanpa lewat draft dulu).
     */
    public function save(): void
    {
        if (! $this->number) {
            $this->number = Document::generateNumber($this->type, $this->doc_date);
        }

        $this->persist('final');
    }

    /**
     * Ini yang jalan kalau user pencet Enter di form (default submit) —
     * sengaja dibikin "aman" (gak langsung final). Nomor SENGAJA dikosongin
     * total tiap kali disimpen sebagai Draft — termasuk kalau sebelumnya
     * dokumen ini udah Final terus diubah balik ke Draft, nomornya ikut
     * kehapus (bukan cuma dibiarin nyantol).
     */
    public function saveDraft(): void
    {
        $this->number = null;
        $this->persist('draft');
    }

    private function persist(string $status): void
    {
        $rules = [
            'type' => 'required|in:quotation,invoice,po,bast',
            'number' => ['nullable', 'string', 'max:100', Rule::unique('documents', 'number')->ignore($this->editingId)],
            'doc_date' => 'required|date',
            'contact_name' => 'nullable|string|max:150',
            'terms' => 'nullable|string',
            'signatory_name' => 'required|string|max:150',
            'signatory_title' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];

        // PO tetep ditujukan ke Vendor, tapi Customer terkait (siapa yang
        // butuh barang ini) SEKARANG ikut disimpen juga.
        $rules['vendor_id'] = $this->type === 'po' ? 'required|exists:vendors,id' : 'nullable|exists:vendors,id';
        $rules['customer_id'] = $this->type !== 'po' ? 'required|exists:customers,id' : 'nullable|exists:customers,id';
        // Invoice WAJIB nge-link ke opty — soalnya referensi Quo/PO & item-nya
        // narik otomatis dari situ.
        $rules['opportunity_id'] = $this->type === 'invoice' ? 'required|exists:opportunities,id' : 'nullable|exists:opportunities,id';

        $data = $this->validate($rules, [], [
            'number' => 'Nomor Dokumen',
            'vendor_id' => 'Vendor',
            'customer_id' => 'Customer',
            'opportunity_id' => 'Link ke Opty',
        ]);

        $data['status'] = $status;
        $data['total'] = $this->calculateTotal();
        $data['opportunity_id'] = $this->opportunity_id;
        $data['ref_quotation_number'] = $this->ref_quotation_number ?: null;
        $data['ref_po_number'] = $this->ref_po_number ?: null;
        $data['ref_invoice_number'] = $this->ref_invoice_number ?: null;
        $data['signatory_title'] = $this->signatory_title ?: null;

        unset($data['items']);

        // Kalau lagi edit dan opty/jenis dokumennya DIGANTI, checklist Next
        // Action di opty yang LAMA perlu di-re-sync juga. Opty yang BARU
        // otomatis ke-sync sendiri lewat event 'saved' di model.
        $oldOpportunityId = null;
        $oldType = null;
        if ($this->editingId) {
            $existing = Document::find($this->editingId);
            $oldOpportunityId = $existing?->opportunity_id;
            $oldType = $existing?->type;

            $doc = Document::findOrFail($this->editingId);
            $doc->update($data);
            $doc->items()->delete();
        } else {
            $doc = Document::create($data);
        }

        foreach ($this->items as $i => $item) {
            $doc->items()->create([
                'group_label' => $item['group_label'] ?: null,
                'product_type' => $item['product_type'] ?: null,
                'description' => $item['description'],
                'qty' => $item['qty'],
                'unit' => $item['unit'] ?: null,
                'credits_required' => $item['credits_required'] !== '' ? $item['credits_required'] : null,
                'unit_price' => $item['unit_price'],
                'amount' => (float) $item['qty'] * (float) $item['unit_price'],
                'sort_order' => $i,
            ]);
        }

        if ($oldOpportunityId && ($oldOpportunityId !== $doc->opportunity_id || $oldType !== $doc->type)) {
            $doc->syncOpportunityChecklist($oldOpportunityId, $oldType);
        }

        // Balik ke List, langsung ke-filter ke jenis dokumen yang baru
        // disimpen (misal abis bikin Quotation, balik ke tab Quotation).
        $this->redirect(route('documents.index', ['type' => $doc->type]), navigate: false);
    }

    public function delete(): void
    {
        if (! auth()->user()->hasPermission('document.manage')) {
            abort(403, 'Akun lo gak punya izin hapus dokumen.');
        }

        if (! $this->editingId) {
            return;
        }

        $type = $this->type;
        Document::findOrFail($this->editingId)->delete();

        $this->redirect(route('documents.index', ['type' => $type]), navigate: false);
    }
}
