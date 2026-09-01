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
        {{-- Meta-table (Date / No / Ref) — lebarnya sekarang OTOMATIS (bukan
             100%) + margin-left:auto, biar keseluruhan blok nempel ke pojok
             kanan (sejajar margin kanan halaman), sementara titik dua-nya
             tetep sejajar antar baris (label punya lebar tetap). --}}
        .meta-table { font-size: 9.5px; margin-top: 6px; width: auto; margin-left: auto; }
        .meta-table td { padding: 1px 0; vertical-align: top; text-align: left; }
        .meta-label { color: #555; width: 82px; white-space: nowrap; }
        .meta-colon { width: 8px; }
        .meta-value { text-align: left; white-space: nowrap; }
        .divider { border-top: 2px solid #19A9DB; margin: 10px 0 14px; }

        {{-- display:inline-block — sebelumnya div block biasa otomatis
             ngambil lebar penuh kolomnya, jadi background birunya kepanjangan
             ngebentang. Sekarang cuma sepanjang teks "Quote to" doang, kayak
             badge di contoh referensi. Warna disesuaiin sama contoh gambar
             (biru solid + teks putih, bukan biru muda + teks navy). --}}
        .recipient-box { display: inline-block; background: #4E63BC; padding: 4px 10px; font-weight: bold; font-size: 9.5px; color: #FFFFFF; margin-bottom: 6px; border-radius: 2px; }
        .recipient-table td { padding: 1px 0; font-size: 10px; vertical-align: top; }
        .recipient-label { width: 90px; color: #555; }
        .recipient-label-bold { font-weight: bold; color: #222; }
        {{-- Kolom titik-dua & nilai dipisah — biar kalau nilainya (nama
             perusahaan panjang) wrap ke baris ke-2, baris itu sejajar sama
             huruf pertama nilainya, bukan nempel ke kiri banget (di bawah
             titik dua). --}}
        .recipient-colon { width: 8px; }
        .recipient-value { vertical-align: top; }
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
        table.items th { background: #F6B01A; color: #1a1a1a; font-size: 9.5px; text-align: center; padding: 6px 6px; border: none; border-bottom: 2px solid #000; }
        table.items td { font-size: 9.5px; padding: 5px 6px; border: none; vertical-align: top; }
        table.items .num { text-align: center; }
        table.items .money { text-align: right; white-space: nowrap; width: 18%; }
        .group-row td { background: #FAFAFA; font-weight: bold; font-size: 9.5px; border: none; }
        .desc-detail { font-size: 9px; color: #555; margin-top: 2px; white-space: pre-line; }
        {{-- Garis border-top DIHAPUS dari Total & Grand Total (gak ada garis
             sama sekali di Total). Grand Total nanti pake garis SEMPIT
             (cuma di 2 sel terakhir), bukan lewat class row ini lagi. --}}
        table.items .total-row td { font-weight: bold; font-size: 11px; border: none; }
        {{-- Baris DP/Termin — bold kayak Grand Total. --}}
        table.items .bold-row td { font-weight: bold; font-size: 11px; border: none; }
        {{-- Highlight abu-abu & garis Grand Total sekarang DIKASIH LANGSUNG
             ke sel yang relevan doang (bukan class di <tr>), soalnya harus
             SEMPIT — cuma dari kolom label ("Grand Total"/"Down Payment")
             sampai kolom nominal, kolom di sebelah kirinya (No, Description,
             Qty, dst) tetep putih polos. --}}

        .terms-box { border: 1px solid #999; padding: 8px 10px; margin-top: 18px; font-size: 9px; }
        .terms-box .title { font-weight: bold; text-decoration: underline; margin-bottom: 4px; }
        .terms-content { line-height: 1.5; }
        .terms-line { width: 100%; margin-bottom: 3px; }
        .terms-line td { border: none !important; padding: 0 !important; font-size: 9px; }
        .terms-num { width: 14px; vertical-align: top; }
        .terms-text { vertical-align: top; white-space: pre-line; }

        .sign-table { margin-top: 30px; width: 100%; }
        .sign-table td { width: 50%; font-size: 10px; vertical-align: top; padding: 0; text-align: left; }
        .sign-space { height: 60px; }
        .sign-name { font-weight: bold; border-top: 1px solid #333; padding-top: 3px; display: inline-block; min-width: 160px; }
        .sign-title { font-size: 9px; color: #444; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <img src="{{ public_path('images/alinea-logo.jpg') }}" class="logo">
            </td>
            <td style="width: 45%;">
                <div class="doc-title">
                    @if ($doc->type === 'po') Purchase Order
                    @elseif ($doc->type === 'bast') BERITA ACARA<br>SERAH TERIMA
                    @else {{ strtoupper($doc->type_label) }}
                    @endif
                </div>
            </td>
        </tr>
        {{-- Baris ke-2 terpisah (bukan ditumpuk margin/height nebak-nebak) —
             biar "Date"/"No" DIJAMIN sejajar persis sama baris
             "PT. Alinea Terra Harmoni", soalnya dua-duanya di <tr> yang sama. --}}
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="company-info">
                    <b>PT. Alinea Terra Harmoni</b><br>
                    Jl. H. Ung Kel. Utan Panjang, Kec. Kemayoran<br>
                    Kota Jakarta Pusat, DKI Jakarta 10650<br>
                    Email: sales@alineaterra.com &nbsp;|&nbsp; Phone: 0857-1673-7556
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="meta-table">
                    <tr><td class="meta-label">Date</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->doc_date->translatedFormat('l, d F Y') }}</td></tr>
                    <tr><td class="meta-label">{{ $doc->type === 'bast' ? 'BAST' : $doc->type_label }} No</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->number ?: 'DRAFT' }}</td></tr>
                    @if ($doc->type === 'invoice' && $doc->ref_po_number)
                        <tr><td class="meta-label">Refer PO No</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->ref_po_number }}</td></tr>
                    @endif
                    @if ($doc->type === 'bast')
                        @if ($doc->ref_quotation_number)<tr><td class="meta-label">Ref. Quotation</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->ref_quotation_number }}</td></tr>@endif
                        @if ($doc->ref_po_number)<tr><td class="meta-label">Ref. PO</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->ref_po_number }}</td></tr>@endif
                        @if ($doc->ref_invoice_number)<tr><td class="meta-label">Ref. Invoice</td><td class="meta-colon">:</td><td class="meta-value">{{ $doc->ref_invoice_number }}</td></tr>@endif
                    @endif
                </table>
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
                        <tr><td class="recipient-label">Nama Perusahaan</td><td class="recipient-colon">:</td><td class="recipient-value">PT. Alinea Terra Harmoni</td></tr>
                        <tr><td class="recipient-label">Diwakili oleh</td><td class="recipient-colon">:</td><td class="recipient-value">{{ $doc->signatory_name }}</td></tr>
                        @if ($doc->signatory_title)
                            <tr><td class="recipient-label">Jabatan</td><td class="recipient-colon">:</td><td class="recipient-value">{{ $doc->signatory_title }}</td></tr>
                        @endif
                    </table>
                </td>
                <td>
                    <div class="recipient-box">PIHAK KEDUA (Menerima)</div>
                    <table class="recipient-table">
                        <tr><td class="recipient-label">Nama Perusahaan</td><td class="recipient-colon">:</td><td class="recipient-value">{{ $doc->customer?->name }}</td></tr>
                        @if ($doc->contact_name)<tr><td class="recipient-label">Contact Name</td><td class="recipient-colon">:</td><td class="recipient-value">{{ $doc->contact_name }}</td></tr>@endif
                        @if ($doc->contact_title)<tr><td class="recipient-label">Jabatan</td><td class="recipient-colon">:</td><td class="recipient-value">{{ $doc->contact_title }}</td></tr>@endif
                        @if ($doc->customer?->address)<tr><td colspan="3" class="recipient-address"><div class="recipient-address-inner">{{ $doc->customer->address }}</div></td></tr>@endif
                    </table>
                </td>
            </tr>
        </table>
        <p style="margin-top: 10px; font-size: 9.5px; line-height: 1.5;">
            Pada hari ini, {{ $doc->doc_date->translatedFormat('l, d F Y') }}, yang bertanda tangan di bawah ini telah melaksanakan serah terima
            pekerjaan/barang sebagaimana tercantum dalam dokumen referensi di atas. PIHAK PERTAMA menyerahkan dan PIHAK KEDUA menerima
            pekerjaan/barang tersebut dalam kondisi baik dan lengkap. Berita Acara ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.
        </p>
    @else
        <div class="recipient-box">
            @if ($doc->type === 'po') Kepada Yth.
            @elseif ($doc->type === 'invoice') Bill to
            @else Quote to
            @endif
        </div>
        <table class="recipient-table">
            <tr>
                <td class="recipient-label recipient-label-bold">Account Name</td>
                <td class="recipient-colon">:</td>
                <td class="recipient-value"><b>{{ $doc->recipient_name }}</b></td>
            </tr>
            @if ($doc->contact_name)
                <tr><td class="recipient-label">Contact Name</td><td class="recipient-colon">:</td><td class="recipient-value">{{ $doc->contact_name }}</td></tr>
            @endif
            @if ($doc->contact_title)
                <tr><td class="recipient-label">Jabatan</td><td class="recipient-colon">:</td><td class="recipient-value">{{ $doc->contact_title }}</td></tr>
            @endif
            @php
                $recipientAddress = $doc->type === 'po' ? $doc->vendor?->address : $doc->customer?->address;
            @endphp
            @if ($recipientAddress)
                {{-- colspan=3 (bukan kolom terakhir doang) biar alamatnya sejajar
                     sama teks "Account Name"/"Contact Name", bukan nempel di
                     bawah titik dua. --}}
                <tr><td colspan="3" class="recipient-address"><div class="recipient-address-inner">{{ $recipientAddress }}</div></td></tr>
            @endif
        </table>

        @if ($doc->type === 'po' && $doc->customer)
            <p style="margin-top: 10px; font-size: 9.5px;">
                Dengan Hormat,<br>
                Saat ini kami membutuhkan pembelian lisensi/produk untuk Customer <b>{{ $doc->customer->name }}</b> dengan rincian biaya sebagai berikut:
            </p>
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
                @if ($doc->type !== 'bast' && $doc->has_discount)
                    <th style="width: 8%;">Disc.</th>
                @endif
                @if ($doc->type !== 'bast' && $doc->has_credits)
                    <th style="width: 12%;">Credits Required</th>
                @endif
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $colCount = 5 + ($doc->type === 'po' ? 1 : 0) + ($doc->type !== 'bast' && $doc->has_credits ? 1 : 0) + ($doc->type !== 'bast' && $doc->has_discount ? 1 : 0);
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
                        {{-- Nama Item (BOLD) sekarang field terpisah dari Deskripsi.
                             Fallback ke baris pertama $description buat data lama
                             yang belum ada item_name-nya. Deskripsi cuma
                             ditampilin kalau beneran diisi (gak wajib lagi). --}}
                        <b>{{ $item->item_name ?: \Illuminate\Support\Str::of($item->description)->explode("\n")->first() }}</b>
                        @if ($item->item_name && $item->description)
                            <div class="desc-detail">{{ $item->description }}</div>
                        @elseif (! $item->item_name && \Illuminate\Support\Str::of($item->description)->explode("\n")->count() > 1)
                            <div class="desc-detail">{{ \Illuminate\Support\Str::of($item->description)->explode("\n")->slice(1)->implode("\n") }}</div>
                        @endif
                    </td>
                    <td class="num">{{ rtrim(rtrim(number_format($item->qty, 2), '0'), '.') }}{{ $item->unit ? ' '.$item->unit : '' }}</td>
                    @if ($doc->type !== 'bast' && $doc->has_discount)
                        <td class="num">{{ $item->discount ? rtrim(rtrim(number_format($item->discount, 2), '0'), '.').'%' : '-' }}</td>
                    @endif
                    @if ($doc->type !== 'bast' && $doc->has_credits)
                        <td class="num">{{ $item->credits_required ?? '-' }}</td>
                    @endif
                    <td class="money">@include('pdf.partials.money', ['amount' => $item->unit_price])</td>
                    <td class="money">@include('pdf.partials.money', ['amount' => $item->amount])</td>
                </tr>
            @endforeach
            @php
                // Total (subtotal) — highlight cuma di kasus khusus: Invoice
                // tanpa pajak sama sekali & Lunas (di situ Total = angka
                // final yang perlu dibayar, gak ada Grand Total lagi).
                $totalHighlight = $doc->type === 'invoice' && $doc->taxes->isEmpty() && $doc->payment_scheme !== 'staged';
            @endphp
            <tr class="total-row">
                {{-- Spacer kosong ikut dikasih border-top juga, biar garisnya
                     NYAMBUNG selebar tabel (kayak garis bawah header) —
                     bedanya sama Grand Total: background abu-abu tetep
                     SEMPIT (cuma di sel label+nominal), garisnya doang yang
                     full-width. --}}
                <td colspan="{{ $colCount - 2 }}" style="border-top: 2px solid #000;"></td>
                <td style="text-align: right; border-top: 2px solid #000; {{ $totalHighlight ? 'background: #F3F4F6;' : '' }}">Total</td>
                <td class="money" style="border-top: 2px solid #000; {{ $totalHighlight ? 'background: #F3F4F6;' : '' }}">@include('pdf.partials.money', ['amount' => $doc->total])</td>
            </tr>

            {{-- Pajak (PPN/PPh/lain-lain) — baris BIASA (gak bold), masih di
                 dalem tabel yang SAMA persis kayak Total, jadi kolom Rp/angka-nya
                 dijamin sejajar sempurna (bukan tabel terpisah kayak sebelumnya
                 yang proporsi lebar kolomnya beda-beda). --}}
            @if ($doc->type === 'invoice')
                @foreach ($doc->taxes as $tax)
                    <tr>
                        <td colspan="{{ $colCount - 1 }}" style="text-align: right; color: #444;">{{ $tax->label }}</td>
                        <td class="money" style="color: #444;">@include('pdf.partials.money', ['amount' => $tax->amount, 'negative' => $tax->direction === 'subtract'])</td>
                    </tr>
                @endforeach

                {{-- Grand Total: garis SEMPIT (cuma di sel label+nominal, bukan
                     sepanjang baris) + highlight GANTIAN — kalau ADA DP/Termin,
                     Grand Total GAK di-highlight (fokusnya pindah ke baris
                     DP/Termin di bawahnya). Kalau LUNAS (gak ada DP/Termin),
                     Grand Total YANG di-highlight. --}}
                @if ($doc->taxes->count())
                    @php $gtHighlight = $doc->payment_scheme !== 'staged'; @endphp
                    <tr class="total-row">
                        <td colspan="{{ $colCount - 2 }}"></td>
                        <td style="text-align: right; border-top: 2px solid #000; {{ $gtHighlight ? 'background: #F3F4F6;' : '' }}">Grand Total</td>
                        <td class="money" style="border-top: 2px solid #000; {{ $gtHighlight ? 'background: #F3F4F6;' : '' }}">@include('pdf.partials.money', ['amount' => $doc->grand_total])</td>
                    </tr>
                @endif

                {{-- Skema Pembayaran — bukan tabel/kotak terpisah, cuma baris
                     tambahan di bawah Grand Total (bold + highlight sempit,
                     TANPA garis). Lunas = gak ada baris tambahan sama sekali. --}}
                @if ($doc->payment_scheme === 'staged')
                    @foreach ($doc->paymentTerms as $term)
                        <tr class="bold-row">
                            <td colspan="{{ $colCount - 2 }}"></td>
                            <td style="text-align: right; background: #F3F4F6;">
                                {{ $term->label }}{{ $term->percentage ? ' '.rtrim(rtrim(number_format($term->percentage, 2), '0'), '.').'%' : '' }}
                            </td>
                            <td class="money" style="background: #F3F4F6;">@include('pdf.partials.money', ['amount' => $term->amount])</td>
                        </tr>
                    @endforeach
                @endif
            @endif
        </tbody>
    </table>

    {{-- BAST gak butuh Terms & Condition — itu dokumen serah terima
         (receipt), bukan dokumen komersial kayak Quotation/PO/Invoice. --}}
    @if ($doc->terms && $doc->type !== 'bast')
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
                    @if ($doc->signatory_title)<span class="sign-title">{{ $doc->signatory_title }}</span><br>@endif
                    <span class="sign-title">PT. Alinea Terra Harmoni</span>
                </td>
                <td>
                    <div style="width: 190px; margin-left: auto;">
                        <div>Pihak Kedua,</div>
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $doc->contact_name }}</div><br>
                        @if ($doc->contact_title)<span class="sign-title">{{ $doc->contact_title }}</span><br>@endif
                        <span class="sign-title">{{ $doc->customer?->name }}</span>
                    </div>
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
                    @if ($doc->contact_title)<span class="sign-title">{{ $doc->contact_title }}</span><br>@endif
                    <span class="sign-title">{{ $doc->customer?->name }}</span>
                </td>
                {{-- Blok "Regards" posisinya tetep di kanan (div pembungkus
                     lebar tetap + margin-left:auto ngedorong ke kanan), TAPI
                     teks di dalemnya (nama, PT Alinea) tetep rata kiri —
                     bukan text-align:right di td (yang bikin teksnya ikut
                     rata kanan juga, gak cuma posisi blok-nya doang). --}}
                <td>
                    <div style="width: 190px; margin-left: auto;">
                        <div>Regards,</div>
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $doc->signatory_name }}</div><br>
                        {{-- Jabatan di ATAS "PT. Alinea Terra Harmoni" (posisi
                             sebelumnya salah, kebalik) — cuma muncul kalau diisi. --}}
                        @if ($doc->type === 'invoice' && $doc->signatory_title)<span class="sign-title">{{ $doc->signatory_title }}</span><br>@endif
                        <span class="sign-title">PT. Alinea Terra Harmoni</span>
                    </div>
                </td>
            </tr>
        </table>
    @endif

</body>
</html>