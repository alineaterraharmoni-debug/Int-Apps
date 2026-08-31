{{-- "Rp" dipisah jadi kolom sendiri (lebar tetap) dari angkanya — biar kalau
     dipake berkali-kali di baris-baris berbeda (Total, PPN, Grand Total, dst),
     semua "Rp" sejajar vertikal DAN semua angka sejajar vertikal (rata kanan),
     bukan satu string "Rp 1.234.567" yang posisinya geser-geser tergantung
     panjang angkanya. Lebar kolom angka digenerosin buat nampung sampe
     1 triliun (Rp 1.000.000.000.000) tanpa kepotong/wrap.
     Nominal minus (pajak arah "Kurang" kayak PPh) dibungkus tanda kurung
     akuntansi "(Rp X)" — bukan "-Rp X" — sesuai konvensi laporan keuangan. --}}
<table style="width: 100%;">
    <tr>
        <td style="width: 24px; text-align: left; padding: 0; border: none; white-space: nowrap;">{{ ($negative ?? false) ? '(Rp' : 'Rp' }}</td>
        <td style="text-align: right; padding: 0; border: none; white-space: nowrap;">{{ number_format($amount, 0, ',', '.') }}{{ ($negative ?? false) ? ')' : '' }}</td>
    </tr>
</table>
