<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Opportunity;
use App\Models\Vendor;
use App\Support\DocumentTerms;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DocumentForm extends Component
{
    public ?int $editingId = null;
    public string $type = 'quotation';
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

    public function render()
    {
        return view('livewire.document-form', [
            'types' => Document::TYPES,
            'customers' => Customer::orderBy('name')->get(),
            'vendors' => Vendor::orderBy('name')->get(),
            'opportunities' => Opportunity::with('customer')->orderByDesc('created_at')->limit(100)->get(),
            'previewNumber' => $this->number ?? Document::generateNumber($this->type),
            'grandTotal' => $this->calculateTotal(),
        ]);
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

    public function selectOpportunity(): void
    {
        if (! $this->opportunity_id) {
            return;
        }

        $opty = Opportunity::find($this->opportunity_id);
        if ($opty && $this->type !== 'po') {
            $this->customer_id = $opty->customer_id;
        }
    }

    private function calculateTotal(): float
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['qty'] ?? 0) * (float) ($i['unit_price'] ?? 0));
    }

    public function save(): void
    {
        $rules = [
            'type' => 'required|in:quotation,invoice,po,bast',
            'doc_date' => 'required|date',
            'contact_name' => 'nullable|string|max:150',
            'terms' => 'nullable|string',
            'signatory_name' => 'required|string|max:150',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];

        if ($this->type === 'po') {
            $rules['vendor_id'] = 'required|exists:vendors,id';
        } else {
            $rules['customer_id'] = 'required|exists:customers,id';
        }

        $data = $this->validate($rules);

        $data['number'] = $this->number ?? Document::generateNumber($this->type);
        $data['total'] = $this->calculateTotal();
        $data['opportunity_id'] = $this->opportunity_id;
        $data['ref_quotation_number'] = $this->ref_quotation_number ?: null;
        $data['ref_po_number'] = $this->ref_po_number ?: null;
        $data['ref_invoice_number'] = $this->ref_invoice_number ?: null;

        if ($this->type !== 'po') {
            $data['vendor_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        unset($data['items']);

        if ($this->editingId) {
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

        session()->flash('saved', $doc->number);
        $this->redirect(route('documents.edit', $doc->id), navigate: false);
    }
}
