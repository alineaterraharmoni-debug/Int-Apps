<?php

namespace App\Livewire;

use App\Models\Opportunity;
use App\Models\Customer;
use App\Models\TeamMember;
use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class OpportunityBoard extends Component
{
    use WithPagination;

    // ----- Tampilan Board vs List -----
    public string $viewMode = 'board'; // board | list

    // ----- Search & filter Board -----
    public string $boardSearch = '';

    // ----- Search & filter List -----
    public string $listSearch = '';
    public string $listFilterStage = '';
    public string $listFilterRating = '';
    public string $listFilterCategory = '';
    public $listFilterSales = '';
    public $listFilterCustomer = '';
    public bool $showListFilters = false;
    public int $listPerPage = 25;
    public string $listSortBy = 'updated_at';
    public string $listSortDir = 'desc';

    // ----- Modal & form state -----
    public bool $showModal = false;
    public ?int $editingId = null;
    public bool $promptingLostReason = false;
    public bool $promptingWonReason = false;
    public array $missingFieldsNotice = [];
    public string $activeTab = 'info'; // info | stage | tim | catatan

    // ----- View Detail (read-only) — klik nama/kartu opty buka ini dulu,
    // Edit dipindah jadi tombol DI DALEM popup Detail. -----
    public bool $showDetailModal = false;
    public ?int $detailId = null;

    #[Validate('required|string|max:150')]
    public string $title = '';

    #[Validate('required|exists:customers,id')]
    public $customer_id = null;

    public bool $showQuickAddCustomer = false;
    #[Validate('required_if:showQuickAddCustomer,true|string|max:150')]
    public string $new_customer_name = '';
    // Korelasi sama field mandatory di menu Customer Insight — alamat sekarang
    // wajib diisi di sana, jadi quick-add dari sini juga harus konsisten,
    // biar gak ada customer yang kebuat tanpa alamat lewat jalur pintas ini.
    #[Validate('required_if:showQuickAddCustomer,true|string')]
    public string $new_customer_address = '';

    #[Validate('required|in:cybersecurity,cctv,data_center_networking,enterprise_networking,web_development,lainnya')]
    public string $category = 'cybersecurity';

    #[Validate('required|numeric|min:0')]
    public $tcv = '';

    #[Validate('required|numeric|min:0|max:100')]
    public $gp_percentage = '';

    #[Validate('required|in:high,med,low')]
    public string $rating = 'med';

    #[Validate('nullable|date')]
    public ?string $expected_closing_date = null;

    #[Validate('required|in:leads,develop,won,lost')]
    public string $stage = 'leads';

    public string $lost_category = '';
    public string $lost_reason = '';
    public string $won_category = '';
    public string $won_reason = '';

    #[Validate('nullable|exists:team_members,id')]
    public $sales_id = null;

    #[Validate('nullable|exists:team_members,id')]
    public $presales_id = null;

    #[Validate('array')]
    public array $engineer_ids = [];

    #[Validate('nullable|string|max:255')]
    public array $next_action_checklist = [];

    #[Validate('nullable|string')]
    public ?string $notes = null;

    /**
     * Support quick action dari Home: link "+ Opty Baru" (?new=1) langsung
     * buka modal create, tanpa perlu 2 tap (masuk modul dulu baru klik tambah).
     * Guard permission di sini juga (bukan cuma di route), biar user yang
     * cuma view-only gak ke-abort 403 pas mount — query string-nya dicuekin aja.
     */
    public function mount(): void
    {
        if (request()->boolean('new') && ($this->canManageFull() || $this->canManageMqlOnly())) {
            $this->openCreate();
        }
    }

    public function updatingBoardSearch(): void
    {
        // Gak perlu resetPage — board gak dipaginate, tapi biar konsisten
        // sama pola updating* lain di komponen ini.
    }

    public function updatingListSearch(): void
    {
        $this->resetPage();
    }

    public function updatingListPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingListFilterStage(): void
    {
        $this->resetPage();
    }

    public function updatingListFilterRating(): void
    {
        $this->resetPage();
    }

    public function updatingListFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingListFilterSales(): void
    {
        $this->resetPage();
    }

    public function updatingListFilterCustomer(): void
    {
        $this->resetPage();
    }

    public function sortList(string $field): void
    {
        if (! in_array($field, ['title', 'customer', 'stage', 'rating'], true)) {
            return;
        }

        if ($this->listSortBy === $field) {
            $this->listSortDir = $this->listSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->listSortBy = $field;
            $this->listSortDir = 'asc';
        }

        $this->resetPage();
    }

    public function resetListFilters(): void
    {
        $this->reset([
            'listSearch', 'listFilterStage', 'listFilterRating',
            'listFilterCategory', 'listFilterSales', 'listFilterCustomer',
        ]);
        $this->resetPage();
    }

    public function render()
    {
        // Urutan prioritas kartu di tiap kolom board: Rating High duluan,
        // habis itu yang closing date-nya paling deket. Ini gantiin "warning
        // kolom penuh" — daripada nakut-nakutin user, opty yang paling
        // penting otomatis muncul duluan di atas walau kolomnya lagi rame.
        $ratingOrder = ['high' => 0, 'med' => 1, 'low' => 2];

        $opportunities = Opportunity::with(['customer', 'sales', 'presales', 'engineers'])
            ->when($this->boardSearch, fn ($q, $v) => $q->where(fn ($qq) => $qq
                ->where('title', 'like', "%{$v}%")
                ->orWhere('customer_name', 'like', "%{$v}%")))
            ->orderByDesc('updated_at')
            ->get()
            ->sortBy(fn ($o) => $o->expected_closing_date?->timestamp ?? PHP_INT_MAX)
            ->sortBy(fn ($o) => $ratingOrder[$o->rating] ?? 99)
            ->values()
            ->groupBy('stage');

        // Filter + search dipusatkan di satu closure biar query list & query
        // total TCV selalu konsisten — kalau nanti nambah filter baru,
        // cukup edit di sini aja.
        $applyListFilters = fn ($query) => $query
            ->when($this->listSearch, fn ($q, $v) => $q->where(fn ($qq) => $qq
                ->where('title', 'like', "%{$v}%")
                ->orWhere('customer_name', 'like', "%{$v}%")))
            ->when($this->listFilterStage, fn ($q, $v) => $q->where('stage', $v))
            ->when($this->listFilterRating, fn ($q, $v) => $q->where('rating', $v))
            ->when($this->listFilterCategory, fn ($q, $v) => $q->where('category', $v))
            ->when($this->listFilterSales, fn ($q, $v) => $q->where('sales_id', $v))
            ->when($this->listFilterCustomer, fn ($q, $v) => $q->where('customer_id', $v));

        $sortDir = $this->listSortDir === 'desc' ? 'desc' : 'asc';

        $listItems = $applyListFilters(Opportunity::with(['customer']))
            ->when($this->listSortBy === 'title', fn ($q) => $q->orderBy('title', $sortDir))
            ->when($this->listSortBy === 'customer', fn ($q) => $q->orderBy('customer_name', $sortDir))
            ->when($this->listSortBy === 'stage', fn ($q) => $q->orderByRaw(
                "FIELD(stage,'leads','develop','won','lost') ".($sortDir === 'desc' ? 'desc' : 'asc')
            ))
            ->when($this->listSortBy === 'rating', fn ($q) => $q->orderByRaw(
                "FIELD(rating,'low','med','high') ".($sortDir === 'desc' ? 'desc' : 'asc')
            ))
            ->when(! in_array($this->listSortBy, ['title', 'customer', 'stage', 'rating'], true), fn ($q) => $q->orderByDesc('updated_at'))
            ->paginate($this->listPerPage);

        // Total TCV dari SEMUA opty yang match filter (bukan cuma yang lagi
        // kelihatan di halaman ini) — biar tetep akurat walau di-paginate.
        $listTotalTcv = $applyListFilters(Opportunity::query())->sum('tcv');

        $canCreateOrEdit = $this->canManageFull() || $this->canManageMqlOnly();

        $this->dispatch('board-updated');

        return view('livewire.opportunity-board', [
            'stages' => Opportunity::STAGES,
            'categories' => Opportunity::CATEGORIES,
            'ratings' => Opportunity::RATINGS,
            'lostCategories' => Opportunity::LOST_CATEGORIES,
            'wonCategories' => Opportunity::WON_CATEGORIES,
            'grouped' => $opportunities,
            'listItems' => $listItems,
            'listTotalTcv' => $listTotalTcv,
            'customerOptions' => Customer::orderBy('name')->get(),
            'salesOptions' => TeamMember::active()->withRole('sales')->get(),
            'presalesOptions' => TeamMember::active()->withRole('presales')->get(),
            'engineerOptions' => TeamMember::active()->withRole('engineer')->get(),
            'canManageFull' => $this->canManageFull(),
            'canManageMqlOnly' => $this->canManageMqlOnly(),
            'canCreateOrEdit' => $canCreateOrEdit,
            'missingFieldsLabels' => $this->fieldLabels(),
            // Vendor yang lini produknya cocok sama Lini Produk opty yang lagi
            // diisi di form — dipake buat nyaranin di checklist "Cari harga
            // dari Disti/Vendor" (stage Leads), biar sales gak nebak-nebak.
            'suggestedVendors' => Vendor::forCategory($this->category)->orderBy('name')->get(),
            'detailOpty' => $this->detailId
                ? Opportunity::with(['customer', 'sales', 'presales', 'engineers'])->find($this->detailId)
                : null,
        ]);
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['board', 'list'], true) ? $mode : 'board';
    }

    // Klik kartu/nama opty -> buka popup View Detail (read-only, siapapun
    // boleh liat termasuk role view-only). Edit dipindah jadi tombol DI
    // DALEM popup ini, cuma muncul kalau role-nya emang boleh edit opty itu.
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

    public function editFromDetail(int $id): void
    {
        $this->showDetailModal = false;
        $this->openEdit($id);
    }

    private function canManageFull(): bool
    {
        return auth()->user()->hasPermission('crm.manage');
    }

    private function canManageMqlOnly(): bool
    {
        return auth()->user()->hasPermission('crm.manage_mql_only');
    }

    /**
     * Mapping field validasi -> tab tempat field itu berada, dipakai buat
     * auto-switch tab ke yang ada error-nya (lihat try/catch di persist()).
     */
    private function tabForErrors(array $fields): string
    {
        $map = [
            'stage' => ['stage', 'expected_closing_date', 'lost_category', 'lost_reason', 'won_category', 'won_reason'],
            'tim' => ['sales_id', 'presales_id', 'engineer_ids'],
            'catatan' => ['notes'],
        ];

        foreach ($map as $tab => $tabFields) {
            if (array_intersect($fields, $tabFields)) {
                return $tab;
            }
        }

        return 'info';
    }

    /**
     * Label ramah-baca buat field yang dipakai di banner "lengkapi dulu"
     * pas modal kebuka otomatis abis drag & drop pindah stage.
     */
    private function fieldLabels(): array
    {
        return [
            'sales_id' => 'Sales',
            'presales_id' => 'Presales',
            'engineer_ids' => 'Tim Engineer',
            'expected_closing_date' => 'Ekspektasi Closing',
            'won_category' => 'Alasan Menang',
            'lost_category' => 'Alasan Drop',
        ];
    }

    public function openCreate(?string $stage = null): void
    {
        if (! $this->canManageFull() && ! $this->canManageMqlOnly()) {
            abort(403, 'Akun lo cuma bisa lihat pipeline ini, gak bisa nambah opty.');
        }

        $this->resetForm();
        if ($stage) {
            $this->stage = $stage;
        }
        // Role terbatas cuma boleh bikin opty di stage Leads.
        if (! $this->canManageFull() && $this->canManageMqlOnly()) {
            $this->stage = 'leads';
        }
        $this->missingFieldsNotice = [];
        $this->activeTab = 'info';
        $this->showModal = true;
    }

    public function openEdit(int $id, array $missingFields = [], ?string $forcedStage = null): void
    {
        if (! $this->canManageFull() && ! $this->canManageMqlOnly()) {
            abort(403, 'Akun lo cuma bisa lihat pipeline ini, gak bisa edit opty.');
        }

        $opty = Opportunity::with('engineers')->findOrFail($id);

        // Role terbatas Leads-only gak boleh megang opty yang udah lewat Leads.
        if (! $this->canManageFull() && $this->canManageMqlOnly() && $opty->stage !== 'leads') {
            abort(403, 'Opty ini udah lewat stage Leads — akun lo gak bisa edit lagi.');
        }

        $this->editingId = $opty->id;
        $this->title = $opty->title;
        $this->customer_id = $opty->customer_id;
        $this->category = $opty->category;
        $this->tcv = (string) $opty->tcv;
        $this->gp_percentage = (string) $opty->gp_percentage;
        $this->rating = $opty->rating;
        $this->expected_closing_date = optional($opty->expected_closing_date)->format('Y-m-d');
        $this->stage = $forcedStage ?? $opty->stage;
        $this->lost_category = $opty->lost_category ?? '';
        $this->lost_reason = $opty->lost_reason ?? '';
        $this->won_category = $opty->won_category ?? '';
        $this->won_reason = $opty->won_reason ?? '';
        $this->sales_id = $opty->sales_id;
        $this->presales_id = $opty->presales_id;
        $this->engineer_ids = $opty->engineers->pluck('id')->map(fn ($v) => (string) $v)->toArray();
        $this->next_action_checklist = $opty->next_action_checklist ?? [];
        $this->notes = $opty->notes;

        $this->missingFieldsNotice = $missingFields;
        $this->promptingLostReason = in_array('lost_category', $missingFields, true);
        $this->promptingWonReason = in_array('won_category', $missingFields, true);
        $this->activeTab = $missingFields ? $this->tabForErrors($missingFields) : 'info';
        $this->showModal = true;
    }

    public function quickAddCustomer(): void
    {
        $this->validate([
            'new_customer_name' => 'required|string|max:150',
            'new_customer_address' => 'required|string',
        ], [], [
            'new_customer_name' => 'Nama Customer',
            'new_customer_address' => 'Alamat',
        ]);

        $customer = Customer::create([
            'name' => $this->new_customer_name,
            'address' => $this->new_customer_address,
        ]);
        $this->customer_id = $customer->id;
        $this->new_customer_name = '';
        $this->new_customer_address = '';
        $this->showQuickAddCustomer = false;
    }

    /**
     * Logic simpan dipusatkan di sini (dipisah dari save()) biar bisa dipakai
     * bareng sama saveAndAddAnother() tanpa duplikat validasi & persist logic.
     */
    private function persist(): Opportunity
    {
        if (! $this->canManageFull() && ! $this->canManageMqlOnly()) {
            abort(403, 'Akun lo gak punya izin nyimpen opty.');
        }

        // Defense in depth: role Leads-only dipaksa stage-nya tetep 'leads' apapun
        // yang dikirim dari form (jaga-jaga kalau UI-nya ke-bypass).
        if (! $this->canManageFull() && $this->canManageMqlOnly()) {
            $this->stage = 'leads';
        }

        $rules = [
            'title' => 'required|string|max:150',
            'customer_id' => 'required|exists:customers,id',
            'category' => 'required|in:cybersecurity,cctv,data_center_networking,enterprise_networking,web_development,lainnya',
            'tcv' => 'required|numeric|min:0',
            'gp_percentage' => 'required|numeric|min:0|max:100',
            'rating' => 'required|in:high,med,low',
            'stage' => 'required|in:leads,develop,won,lost',
            'notes' => 'nullable|string',
        ];

        // Data mandatory per stage — makin jauh opty-nya di pipeline, makin
        // banyak yang wajib keisi. Berlaku pas create opty baru MAUPUN pas
        // pindah stage lewat form edit:
        // - Leads   : field dasar aja (di atas) + WAJIB ada Sales yang pegang.
        // - Develop : + Ekspektasi Closing wajib ada (biar bisa di-forecast).
        // - WON     : + Alasan Menang, Presales, dan minimal 1 Tim Engineer wajib diisi.
        // - LOST    : + Alasan Drop wajib diisi.
        // (Next Action sekarang checklist, bukan field wajib — checklist-nya
        // sengaja gak nge-block simpen, cuma nge-track buat summary di Home.)
        $rules['sales_id'] = 'required|exists:team_members,id';

        if (in_array($this->stage, ['develop', 'won'], true)) {
            $rules['expected_closing_date'] = 'required|date';
        } else {
            $rules['expected_closing_date'] = 'nullable|date';
        }

        if ($this->stage === 'won') {
            $rules['won_category'] = 'required|string';
            $rules['won_reason'] = 'nullable|string';
            $rules['presales_id'] = 'required|exists:team_members,id';
            $rules['engineer_ids'] = 'required|array|min:1';
        } else {
            $rules['won_category'] = 'nullable|string';
            $rules['won_reason'] = 'nullable|string';
            $rules['presales_id'] = 'nullable|exists:team_members,id';
            $rules['engineer_ids'] = 'array';
        }

        if ($this->stage === 'lost') {
            $rules['lost_category'] = 'required|string';
            $rules['lost_reason'] = 'nullable|string';
        } else {
            $rules['lost_category'] = 'nullable|string';
            $rules['lost_reason'] = 'nullable|string';
        }

        $attributes = [
            'title' => 'Judul Opty',
            'customer_id' => 'Customer',
            'category' => 'Lini Produk',
            'tcv' => 'TCV',
            'gp_percentage' => 'GP',
            'rating' => 'Rating',
            'stage' => 'Stage',
            'sales_id' => 'Sales',
            'presales_id' => 'Presales',
            'engineer_ids' => 'Tim Engineer',
            'expected_closing_date' => 'Ekspektasi Closing',
            'won_category' => 'Kategori Alasan Menang',
            'lost_category' => 'Kategori Alasan Drop',
        ];

        // Kalau validasi gagal, lompat otomatis ke tab yang isinya error, biar
        // user gak bingung nyari kenapa tombol Simpan gak ngefek — soalnya field
        // yang salah bisa aja lagi ada di tab yang gak lagi aktif dilihat.
        try {
            $data = $this->validate($rules, [], $attributes);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->activeTab = $this->tabForErrors(array_keys($e->errors()));
            throw $e;
        }

        $engineerIds = $data['engineer_ids'] ?? [];
        unset($data['engineer_ids']);

        // next_action_checklist gak lewat $rules (bukan field yang divalidasi
        // wajib), jadi ditempel manual ke $data biar ikut kesimpen.
        $data['next_action_checklist'] = $this->next_action_checklist;

        // customer_name dipertahankan sebagai cache tampilan cepat, disinkron dari master
        $data['customer_name'] = Customer::find($data['customer_id'])->name;

        if ($this->stage === 'won') {
            $data['closed_at'] = now()->toDateString();
            $data['lost_category'] = null;
            $data['lost_reason'] = null;
            $data['won_category'] = $this->won_category;
            $data['won_reason'] = $this->won_reason ?: null;
        } elseif ($this->stage === 'lost') {
            $data['closed_at'] = now()->toDateString();
            $data['lost_category'] = $this->lost_category;
            $data['lost_reason'] = $this->lost_reason ?: null;
            $data['won_category'] = null;
            $data['won_reason'] = null;
        } else {
            $data['closed_at'] = null;
            $data['lost_category'] = null;
            $data['lost_reason'] = null;
            $data['won_category'] = null;
            $data['won_reason'] = null;
        }

        if ($this->editingId) {
            $opty = Opportunity::findOrFail($this->editingId);
            $opty->update($data);
        } else {
            $opty = Opportunity::create($data);
        }

        $opty->engineers()->sync($engineerIds);

        return $opty;
    }

    public function save(): void
    {
        $this->persist();
        $this->dispatch('opty-saved');
        $this->closeModal();
    }

    /**
     * Quick action buat input berantai (misal abis event/pameran) — simpen
     * opty yang lagi diisi, terus modal langsung reset ke form kosong baru
     * tanpa perlu ditutup-buka lagi.
     */
    public function saveAndAddAnother(): void
    {
        $this->persist();
        $this->dispatch('opty-saved');

        $this->resetForm();
        $this->promptingLostReason = false;
        $this->promptingWonReason = false;
        $this->missingFieldsNotice = [];
        $this->activeTab = 'info';
        // showModal sengaja gak disentuh — tetep kebuka buat opty berikutnya.
    }

    public function delete(): void
    {
        if (! $this->canManageFull()) {
            abort(403, 'Cuma role dengan akses penuh yang bisa hapus opty.');
        }

        if ($this->editingId) {
            Opportunity::findOrFail($this->editingId)->delete();
        }
        $this->closeModal();
    }

    public function moveStage(int $id, string $stage): void
    {
        if (! array_key_exists($stage, Opportunity::STAGES)) {
            return;
        }

        if (! $this->canManageFull()) {
            if (! $this->canManageMqlOnly()) {
                return; // gak punya izin sama sekali
            }
            // Role Leads-only cuma boleh geser opty yang lagi/mau ke Leads.
            $opty = Opportunity::find($id);
            if (! $opty || $opty->stage !== 'leads' || $stage !== 'leads') {
                return;
            }
        }

        $opty = Opportunity::with('engineers')->findOrFail($id);

        // PENTING: dicek DULU sebelum ada apapun yang ke-simpan. Kalau stage
        // tujuan butuh data yang masih kosong (misal alasan Menang/Kalah),
        // jangan commit stage-nya sama sekali — buka modal dengan stage
        // tujuan udah ke-pre-fill, user lengkapi & klik Simpan baru beneran
        // pindah lewat persist() yang tervalidasi penuh. Drag doang gak
        // cukup buat mindahin opty ke stage yang butuh data wajib.
        $missing = $this->missingFieldsForStage($opty, $stage);
        if ($missing) {
            $this->openEdit($opty->id, missingFields: $missing, forcedStage: $stage);

            return;
        }

        $opty->stage = $stage;
        $opty->closed_at = in_array($stage, ['won', 'lost'], true) ? now()->toDateString() : null;
        if ($stage !== 'lost') {
            $opty->lost_category = null;
            $opty->lost_reason = null;
        }
        if ($stage !== 'won') {
            $opty->won_category = null;
            $opty->won_reason = null;
        }
        $opty->save();
    }

    /**
     * Cek field mandatory yang masih kosong buat stage tertentu. Dipakai
     * bareng sama persist() (via aturan validasi) dan moveStage() (drag & drop
     * gak lewat form, jadi perlu dicek manual di sini).
     */
    private function missingFieldsForStage(Opportunity $opty, string $stage): array
    {
        $missing = [];

        if (! $opty->sales_id) {
            $missing[] = 'sales_id';
        }

        if (in_array($stage, ['develop', 'won'], true)) {
            if (! $opty->expected_closing_date) {
                $missing[] = 'expected_closing_date';
            }
        }

        if ($stage === 'won') {
            if (! $opty->won_category) {
                $missing[] = 'won_category';
            }
            if (! $opty->presales_id) {
                $missing[] = 'presales_id';
            }
            if ($opty->engineers->isEmpty()) {
                $missing[] = 'engineer_ids';
            }
        }

        if ($stage === 'lost' && ! $opty->lost_category) {
            $missing[] = 'lost_category';
        }

        return $missing;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->promptingLostReason = false;
        $this->promptingWonReason = false;
        $this->missingFieldsNotice = [];
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'customer_id', 'tcv', 'gp_percentage',
            'expected_closing_date', 'sales_id', 'presales_id', 'engineer_ids',
            'next_action_checklist', 'notes', 'showQuickAddCustomer', 'new_customer_name', 'new_customer_address',
            'lost_category', 'lost_reason', 'won_category', 'won_reason',
        ]);
        $this->category = 'cybersecurity';
        $this->rating = 'med';
        $this->stage = 'leads';
        $this->resetErrorBag();
    }
}
