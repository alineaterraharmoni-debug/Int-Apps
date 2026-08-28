<?php

namespace App\Livewire;

use App\Models\Opportunity;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Home extends Component
{
    public function render()
    {
        $user = auth()->user();
        $canCrm = $user->hasPermission('crm.view');
        $canReport = $user->hasPermission('report.view');
        $canDocument = $user->hasPermission('document.view');

        // Modul yang beneran ada (dibedain dari "coming soon" yang emang belum dibangun).
        $modules = [
            [
                'key' => 'crm',
                'name' => 'CRM',
                'desc' => 'Tracking opty & pipeline',
                'icon' => 'users',
                'route' => $canCrm ? route('crm.board') : null,
                'available' => $canCrm,
                'color' => 'sky',
                'stat' => $canCrm
                    ? Opportunity::whereNotIn('stage', ['won', 'lost'])->count().' opty aktif'
                    : 'Gak ada akses',
            ],
            [
                'key' => 'document',
                'name' => 'Dokumen',
                'desc' => 'Quotation, invoice, PO, BAST',
                'icon' => 'file-text',
                'route' => $canDocument ? route('documents.index') : null,
                'available' => $canDocument,
                'color' => 'amber',
                'stat' => $canDocument ? 'Quotation · Invoice · PO · BAST' : 'Gak ada akses',
            ],
            [
                'key' => 'report',
                'name' => 'Report Bisnis',
                'desc' => 'Business review M/Q/Y',
                'icon' => 'chart-bar',
                'route' => $canReport ? route('crm.report') : null,
                'available' => $canReport,
                'color' => 'violet',
                'stat' => $canReport ? 'Monthly · Quarterly · Yearly' : 'Gak ada akses',
            ],
        ];

        // Modul yang belum dibangun — dipisah biar gak numpang makan slot grid
        // yang sama gedenya kayak modul yang beneran jalan.
        $comingSoon = [
            [
                'key' => 'project',
                'name' => 'Project & Tiket',
                'desc' => 'Lisensi, maintenance, tiket',
                'icon' => 'briefcase',
            ],
        ];

        // Insight singkat buat hero: opty yang closing-nya minggu ini.
        $closingSoonCount = $canCrm
            ? Opportunity::whereNotIn('stage', ['won', 'lost'])
                ->whereNotNull('expected_closing_date')
                ->whereBetween('expected_closing_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->count()
            : 0;

        // Summary "Next Action belum di-checklist" per stage — cuma buat yang
        // punya akses lihat Board (crm.view). Dihitung dari opty yang masih
        // "hidup" (bukan Lost, Lost emang gak butuh checklist apa-apa).
        $checklistSummary = [];
        if ($canCrm) {
            $openOpties = Opportunity::whereIn('stage', ['leads', 'develop', 'won'])
                ->select('id', 'stage', 'next_action_checklist')
                ->get();

            $counts = ['leads' => 0, 'develop' => 0, 'won' => 0];
            foreach ($openOpties as $o) {
                if ($o->hasPendingChecklist()) {
                    $counts[$o->stage]++;
                }
            }

            $stageLabels = ['leads' => 'Leads', 'develop' => 'Develop', 'won' => 'Closing WON'];
            foreach ($counts as $stageKey => $count) {
                if ($count > 0) {
                    $checklistSummary[] = ['stage' => $stageLabels[$stageKey], 'count' => $count];
                }
            }
        }

        // Quick action di hero — satu aksi paling relevan sesuai role, biar
        // gak perlu masuk modul dulu buat kerjaan paling sering dilakuin.
        $canManageOpty = $user->hasPermission('crm.manage') || $user->hasPermission('crm.manage_mql_only');
        $canManageDocument = $user->hasPermission('document.manage');

        $quickAction = null;
        if ($canManageOpty) {
            $quickAction = ['label' => 'Opty Baru', 'url' => route('crm.board', ['new' => 1])];
        } elseif ($canManageDocument) {
            $quickAction = ['label' => 'Dokumen Baru', 'url' => route('documents.create', ['type' => 'quotation'])];
        }

        return view('livewire.home', [
            'modules' => $modules,
            'comingSoon' => $comingSoon,
            'firstName' => explode(' ', $user->name)[0],
            'closingSoonCount' => $closingSoonCount,
            'canCrm' => $canCrm,
            'quickAction' => $quickAction,
            'checklistSummary' => $checklistSummary,
        ]);
    }
}
