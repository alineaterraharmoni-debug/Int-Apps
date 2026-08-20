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
        $modules = [
            [
                'key' => 'crm',
                'name' => 'CRM',
                'desc' => 'Tracking opty & pipeline',
                'icon' => 'ti-users',
                'route' => route('crm.board'),
                'available' => true,
                'color' => 'sky',
                'stat' => Opportunity::whereNotIn('stage', ['won', 'lost'])->count().' opty aktif',
            ],
            [
                'key' => 'project',
                'name' => 'Project & Tiket',
                'desc' => 'Lisensi, maintenance, tiket',
                'icon' => 'ti-briefcase',
                'route' => null,
                'available' => false,
                'color' => 'teal',
                'stat' => 'Segera hadir',
            ],
            [
                'key' => 'document',
                'name' => 'Dokumen',
                'desc' => 'Quotation, invoice, PO, BAST',
                'icon' => 'ti-file-text',
                'route' => null,
                'available' => false,
                'color' => 'amber',
                'stat' => 'Segera hadir',
            ],
            [
                'key' => 'report',
                'name' => 'Report Bisnis',
                'desc' => 'Business review M/Q/Y',
                'icon' => 'ti-chart-bar',
                'route' => route('crm.report'),
                'available' => true,
                'color' => 'violet',
                'stat' => 'Monthly · Quarterly · Yearly',
            ],
        ];

        return view('livewire.home', ['modules' => $modules]);
    }
}
