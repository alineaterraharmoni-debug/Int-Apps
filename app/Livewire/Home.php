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
                'key' => 'project',
                'name' => 'Project & Tiket',
                'desc' => 'Lisensi, maintenance, tiket',
                'icon' => 'briefcase',
                'route' => null,
                'available' => false,
                'color' => 'teal',
                'stat' => 'Segera hadir',
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

        return view('livewire.home', ['modules' => $modules]);
    }
}
