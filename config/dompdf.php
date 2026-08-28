<?php

return [
    'show_warnings' => false,

    'public_path' => null,

    'convert_entities' => true,

    'options' => [
        // Sebelumnya config ini gak pernah di-publish sama sekali, jadi DomPDF
        // jalan pakai default package apa adanya. Kemungkinan besar itu akar
        // masalah bug "Export PDF 500" yang udah lama nyangkut: font_dir/font_cache
        // defaultnya nunjuk ke storage_path('fonts'), tapi folder itu gak pernah
        // dibikin pas deploy fresh di Railway (lihat composer.json — udah dibenerin
        // bareng file ini). DomPDF gagal nulis cache font -> nembak 500.
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),

        'allowed_protocols' => [
            'data://' => ['rules' => []],
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        'artifactPathValidation' => null,
        'log_output_file' => null,
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'serif',
        'dpi' => 96,
        'enable_php' => false,

        // WAJIB true — report PDF-nya nampilin grafik yang digambar via
        // QuickChart.io (URL gambar remote https://). Kalau ini kematiin
        // (atau ke-default false), gambar grafik gak bakal muncul di PDF,
        // atau DomPDF bisa nembak error tergantung versi.
        'enable_remote' => true,

        'enable_javascript' => true,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],
];
