<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 30px 35px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10.5px; color: #222; }
        table { border-collapse: collapse; width: 100%; }
        .header-table td { vertical-align: top; }
        .logo { width: 150px; }
        .company-info { font-size: 9px; color: #444; line-height: 1.5; margin-top: 4px; }
        .company-info b { color: #111; }
        .doc-title { font-size: 20px; font-style: italic; font-weight: bold; text-align: right; color: #222; line-height: 1.2; margin-bottom: 4px; }
        {{-- Meta-table (Date / No / Ref) sekarang 3-kolom (label/titik-dua/nilai)
             dengan lebar label TETAP — jadi titik dua-nya selalu sejajar
             vertikal dari baris ke baris, gak lompat-lompat ngikutin panjang
             teks label kayak sebelumnya. --}}
        .meta-table { font-size: 9.5px; margin-top: 2px; width: 100%; }
        .meta-table td { padding: 1px 0; vertical-align: top; text-align: left; }
        .meta-label { color: #555; width: 82px; white-space: nowrap; }
        .meta-colon { width: 8px; }
        .meta-value { text-align: left; }
        .divider { border-top: 2px solid #19A9DB; margin: 10px 0 14px; }

        .recipient-box { background: #E9F6FC; padding: 4px 8px; font-weight: bold; font-size: 9.5px; color: #0A1628; margin-bottom: 6px; }
        .recipient-table td { padding: 1px 0; font-size: 10px; vertical-align: top; }
        .recipient-label { width: 90px; color: #555; }
        {{-- Alamat dikasih batas lebar (kayak alamat perusahaan di kop surat
             sebelah kiri) biar wrap jadi beberapa baris pendek, bukan satu
             baris panjang ngebentang selebar halaman. --}}
        .recipient-address { padding-top: 2px !important; }
        .recipient-address-inner { max-width: 340px; }

        .two-col td { width: 50%; vertical-align: top; padding-right: 20px; }

        {{-- Border per-sel item DIHAPUS total — sekarang cuma ada garis tebal
             di bawah judul kolom (th) dan di atas baris Grand Total, biar
             tabelnya keliatan lebih ringan/rapi (gak kotak-kotak). --}}
        table.items { margin-top: 16px; }
        table.items th { background: #F6B01A; color: #1a1a1a; font-size: 9.5px; text-align: left; padding: 6px 6px; border: none; border-bottom: 2px solid #000; }
        table.items td { font-size: 9.5px; padding: 5px 6px; border: none; vertical-align: top; }
        table.items .num { text-align: center; }
        table.items .money { text-align: right; white-space: nowrap; }
        .group-row td { background: #FAFAFA; font-weight: bold; font-size: 9.5px; border: none; }
        .desc-detail { font-size: 9px; color: #555; margin-top: 2px; white-space: pre-line; }
        .total-row td { font-weight: bold; font-size: 11px; border-top: 2px solid #000; }

        .terms-box { border: 1px solid #999; padding: 8px 10px; margin-top: 18px; font-size: 9px; }
        .terms-box .title { font-weight: bold; text-decoration: underline; margin-bottom: 4px; }
        .terms-content { line-height: 1.5; }
        .terms-line { width: 100%; margin-bottom: 3px; }
        .terms-line td { border: none !important; padding: 0 !important; font-size: 9px; }
        .terms-num { width: 14px; vertical-align: top; }
        .terms-text { vertical-align: top; white-space: pre-line; }

        .sign-table { margin-top: 26px; width: 100%; }
        .sign-table td { width: 50%; font-size: 10px; vertical-align: top; padding: 0; text-align: left; }
        .sign-space { height: 55px; }
        .sign-name { font-weight: bold; border-top: 1px solid #333; padding-top: 3px; display: inline-block; min-width: 160px; }
        .sign-title { font-size: 9px; color: #444; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <img src="{{ public_path('images/alinea-logo.jpg') }}" class="logo">
                <div class="company-info">
                    <b>PT. Alinea Terra Harmoni</b><br>
                    Jl. H. Ung Kel. Utan Panjang, Kec. Kemayoran<br>
                    Kota Jakarta Pusat, DKI Jakarta 10650<br>
                    Email: sales@alineaterra.com &nbsp;|&nbsp; Phone: 0857-1673-7556
                </div>
            </td>
            <td style="width: 45%;">
                {{-- Ditambahin margin-top biar blok Date/No sejajar sama baris
                     "PT. Alinea Terra Harmoni" (bukan sejajar sama logo-nya)
                     — konsisten kayak contoh kop surat referensi. --}}
                <div style="margin-top: 38px;">
                    <div class="doc-title">
                        @if ($doc->type === 'po') Purchase Order
                        @elseif ($doc->type === 'bast') BERITA ACARA<br>SERAH TERIMA
                        @else {{ strtoupper($doc->type_label) }}
                        @endif
                    </div>
                <table class="meta-table">
                    <tr><td class="meta-label">Date</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->doc_date->translatedFormat('l, d F Y') }}</td></tr>
                    <tr><td class="meta-label">{{ $doc->type_label }} No</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->number ?: 'DRAFT' }}</td></tr>
                    @if ($doc->type === 'invoice' && $doc->ref_po_number)
                        <tr><td class="meta-label">Refer PO No</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->ref_po_number }}</td></tr>
                    @endif
                    @if ($doc->type === 'bast')
                        @if ($doc->ref_quotation_number)<tr><td class="meta-label">Ref. Quotation</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->ref_quotation_number }}</td></tr>@endif
                        @if ($doc->ref_po_number)<tr><td class="meta-label">Ref. PO</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->ref_po_number }}</td></tr>@endif
                        @if ($doc->ref_invoice_number)<tr><td class="meta-label">Ref. Invoice</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->ref_invoice_number }}</td></tr>@endif
                    @endif
                </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    @if ($doc->type === 'bast')
        <table class="two-col">
            <tr>
                <td>
                    <div class="recipient-box">PIHAK PERTAMA (Menyerahkan)</div>
                    <table class="recipient-table">
                        <tr><td class="recipient-label">Nama Perusahaan</td><td>: PT. Alinea Terra Harmoni</td></tr>
                        <tr><td class="recipient-label">Diwakili oleh</td><td>: {{ $doc->signatory_name }}</td></tr>
                    </table>
                </td>
                <td>
                    <div class="recipient-box">PIHAK KEDUA (Menerima)</div>
                    <table class="recipient-table">
                        <tr><td class="recipient-label">Nama Perusahaan</td><td>: {{ $doc->customer?->name }}</td></tr>
                        @if ($doc->contact_name)<tr><td class="recipient-label">Contact Name</td><td>: {{ $doc->contact_name }}</td></tr>@endif
                        @if ($doc->customer?->address)<tr><td colspan="2" class="recipient-address"><div class="recipient-address-inner">{{ $doc->customer->address }}</div></td></tr>@endif
                    </table>
                </td>
            </tr>
        </table>
        <p style="margin-top: 10px; font-size: 9.5px; line-height: 1.5;">
            Pada hari ini, {{ $doc->doc_date->translatedFormat('l, d F Y') }}, kedua belah pihak menyatakan bahwa pekerjaan/barang berikut telah
            diserahterimakan dengan baik dan diterima dalam kondisi sesuai dengan dokumen referensi di atas.
        </p>
    @else
        <div class="recipient-box">{{ $doc->type === 'po' ? 'Kepada Yth.' : 'Quote to' }}</div>
        <table class="recipient-table">
            <tr>
                <td class="recipient-label">Account Name</td>
                <td>: <b>{{ $doc->recipient_name }}</b></td>
            </tr>
            @if ($doc->contact_name)
                <tr><td class="recipient-label">Contact Name</td><td>: {{ $doc->contact_name }}</td></tr>
            @endif
            @php
                $recipientAddress = $doc->type === 'po' ? $doc->vendor?->address : $doc->customer?->address;
            @endphp
            @if ($recipientAddress)
                {{-- colspan=2 (bukan kolom kedua doang) biar alamatnya sejajar
                     sama teks "Account Name"/"Contact Name", bukan nempel di
                     bawah titik dua. --}}
                <tr><td colspan="2" class="recipient-address"><div class="recipient-address-inner">{{ $recipientAddress }}</div></td></tr>
            @endif
        </table>

        @if ($doc->type === 'po' && $doc->customer)
            <p style="margin-top: 10px; font-size: 9.5px;">
                Dengan Hormat,<br>
                Saat ini kami membutuhkan pembelian lisensi/produk untuk Customer <b>{{ $doc->customer->name }}</b> dengan rincian biaya sebagai berikut:
            </p>
        @endif

        @if ($doc->type === 'invoice' && $doc->ref_quotation_number)
            <p style="margin-top: 8px; font-size: 9px; color: #555;">Sesuai dengan Quotation No. {{ $doc->ref_quotation_number }}</p>
        @endif
    @endif

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                @if ($doc->type === 'po')
                    <th style="width: 20%;">Product Type</th>
                @endif
                <th>Description</th>
                <th style="width: 8%;">Qty</th>
                @if ($doc->type !== 'bast' && $doc->has_credits)
                    <th style="width: 12%;">Credits Required</th>
                @endif
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $colCount = 5 + ($doc->type === 'po' ? 1 : 0) + ($doc->type !== 'bast' && $doc->has_credits ? 1 : 0);
                $rowNum = 1; $lastGroup = null;
            @endphp
            @foreach ($doc->items as $item)
                @if ($item->group_label && $item->group_label !== $lastGroup)
                    <tr class="group-row">
                        <td colspan="{{ $colCount }}">{{ $item->group_label }}</td>
                    </tr>
                    @php $lastGroup = $item->group_label; @endphp
                @endif
                <tr>
                    <td class="num">{{ $rowNum++ }}</td>
                    @if ($doc->type === 'po')
                        <td>{{ $item->product_type }}</td>
                    @endif
                    <td>
                        {{ \Illuminate\Support\Str::of($item->description)->explode("\n")->first() }}
                        @if (\Illuminate\Support\Str::of($item->description)->explode("\n")->count() > 1)
                            <div class="desc-detail">{{ \Illuminate\Support\Str::of($item->description)->explode("\n")->slice(1)->implode("\n") }}</div>
                        @endif
                    </td>
                    <td class="num">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}{{ $item->unit ? ' '.$item->unit : '' }}</td>
                    @if ($doc->type !== 'bast' && $doc->has_credits)
                        <td class="num">{{ $item->credits_required ?? '-' }}</td>
                    @endif
                    <td class="money">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="money">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="{{ $colCount - 1 }}" style="text-align: right; border: none;">Total</td>
                <td class="money" style="border: none;">Rp {{ number_format($doc->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if ($doc->terms)
        <div class="terms-box">
            <div class="title">{{ $doc->type === 'po' ? 'Catatan' : 'Terms and Condition' }} :</div>
            <div class="terms-content">
                @foreach ($doc->terms_lines as $line)
                    @if ($line['num'])
                        <table class="terms-line">
                            <tr>
                                <td class="terms-num">{{ $line['num'] }}.</td>
                                <td class="terms-text">{{ $line['text'] }}</td>
                            </tr>
                        </table>
                    @else
                        <div class="terms-line" style="margin-bottom: 3px;">{{ $line['text'] }}</div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if ($doc->type === 'bast')
        <table class="sign-table">
            <tr>
                <td>
                    <div>Pihak Pertama,</div>
                    <div class="sign-space"></div>
                    <div class="sign-name">{{ $doc->signatory_name }}</div><br>
                    <span class="sign-title">PT. Alinea Terra Harmoni</span>
                    @if ($doc->signatory_title)<br><span class="sign-title">{{ $doc->signatory_title }}</span>@endif
                </td>
                <td>
                    <div>Pihak Kedua,</div>
                    <div class="sign-space"></div>
                    <div class="sign-name">{{ $doc->contact_name }}</div><br>
                    <span class="sign-title">{{ $doc->customer?->name }}</span>
                </td>
            </tr>
        </table>
    @elseif ($doc->type === 'po')
        <div style="margin-top: 20px; font-size: 10px;">
            <b>Disetujui oleh PT. Alinea Terra Harmoni</b>
            <div class="sign-space"></div>
            <div class="sign-name">{{ $doc->signatory_name }}</div><br>
            <span class="sign-title">{{ $doc->signatory_title ?: 'Business Development' }}</span>
        </div>
    @else
        <table class="sign-table">
            <tr>
                <td>
                    <div>Accepted by,</div>
                    <div class="sign-space"></div>
                    {{-- Kalau gak ada Contact Name, baris tanda tangan SENGAJA
                         dibiarin kosong (bukan diisi nama customer) — nama
                         customer-nya tetep di baris caption di bawahnya aja,
                         gak dobel. --}}
                    <div class="sign-name">{{ $doc->contact_name }}</div><br>
                    <span class="sign-title">{{ $doc->customer?->name }}</span>
                </td>
                <td>
                    <div>Regards,</div>
                    @if ($doc->type === 'invoice')
                        <div style="font-size: 9px; margin-top: 4px;">Materai 10.000</div>
                    @endif
                    <div class="sign-space"></div>
                    <div class="sign-name">{{ $doc->signatory_name }}</div><br>
                    <span class="sign-title">PT. Alinea Terra Harmoni</span>
                    @if ($doc->type === 'invoice' && $doc->signatory_title)<br><span class="sign-title">{{ $doc->signatory_title }}</span>@endif
                </td>
            </tr>
        </table>
    @endif

</body>
</html>
