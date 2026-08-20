<?php

namespace App\Support;

class Permissions
{
    /**
     * Katalog permission, dikelompokin per modul buat ditampilin sebagai
     * checklist di halaman Kelola Role. 'crm.manage' dan 'crm.manage_mql_only'
     * sengaja saling eksklusif secara logic (dicek di kode, bukan di sini) —
     * kalau dua-duanya ke-centang, 'crm.manage' (penuh) yang menang.
     */
    const CATALOG = [
        'CRM / Pipeline Opty' => [
            'crm.view' => 'Lihat pipeline (Board)',
            'crm.manage_mql_only' => 'Bikin & edit opty — dibatasin cuma sampai stage MQL',
            'crm.manage' => 'Bikin & edit opty penuh — semua stage termasuk Closing',
        ],
        'Business Review' => [
            'report.view' => 'Lihat & export Report Bisnis',
        ],
        'Customer Insight' => [
            'customer.view' => 'Lihat daftar & analisa customer',
            'customer.manage' => 'Tambah/edit data customer',
        ],
        'Tim (Sales/Presales/Engineer)' => [
            'team.view' => 'Lihat daftar tim',
            'team.manage' => 'Tambah/edit sales, presales, engineer',
        ],
        'Dokumen' => [
            'document.view' => 'Lihat & download dokumen (quotation/invoice/PO/BAST)',
            'document.manage' => 'Bikin & edit dokumen baru',
        ],
        'Ticketing (segera hadir)' => [
            'ticketing.view' => 'Akses modul tiketing begitu udah rilis',
        ],
        'Akun Login' => [
            'accounts.create' => 'Bisa bikin akun login baru buat orang lain',
        ],
    ];

    public static function all(): array
    {
        return array_merge(...array_values(self::CATALOG));
    }

    public static function label(string $key): string
    {
        return self::all()[$key] ?? $key;
    }
}
