<?php

namespace App\Support;

class DocumentTerms
{
    public static function default(string $type): string
    {
        return match ($type) {
            'quotation' => "1. Harga yang tercantum pada surat penawaran ini berlaku sampai 14 Hari sejak diterbitkannya Quotation\n"
                ."2. Harga yang tercantum belum termasuk PPN\n"
                ."3. Pembayaran lisensi dilakukan 100% di muka sebelum pemasangan dan/atau pengiriman Lisensi barang/software.\n"
                ."4. Pelunasan pembayaran professional service dilakukan maksimal 7 hari kerja sejak terbitnya BAST (Berita Acara Serah Terima Pekerjaan) atau setelah pekerjaan implementasi selesai\n"
                ."5. Pembatalan pemesanan setelah Pembayaran di muka dilakukan akan dikenakan denda sebesar 25% dari Total Pembayaran di muka\n"
                ."6. Harga yang tercantum bersifat tidak mengikat\n"
                ."7. Harga Jasa Lokal Support berdasarkan asumsi office customer di Jakarta dan bersifat opsional",

            'invoice' => "1. Pembayaran dilakukan paling lambat 7 hari kalender sejak diterbitkannya Invoice\n"
                ."2. Pembayaran dilakukan 100% di muka sebelum pemasangan dan/atau pengiriman Lisensi barang/software\n"
                ."3. Pembatalan pemesanan setelah Pembayaran di muka dilakukan akan dikenakan denda sebesar 25% dan biaya pembelian lisensi tidak dapat dikembalikan\n"
                ."4. Harga yang tercantum sesuai dengan Quotation terkait\n"
                ."5. Harga Jasa Lokal Support berdasarkan asumsi office customer di Jakarta\n"
                ."6. Pembayaran dapat dilakukan melalui transfer ke nomor rekening berikut:\n"
                ."   Bank Mandiri a/n PT. ALINEA TERRA HARMONI\n"
                ."   1230013333010",

            'po' => "Dengan Hormat,\nSaat ini kami membutuhkan pembelian lisensi/produk untuk customer di bawah dengan rincian biaya sebagai berikut.",

            'bast' => "Dengan ditandatanganinya Berita Acara Serah Terima ini, Pihak Kedua menyatakan bahwa pekerjaan/barang tersebut di atas telah diterima dengan baik dan sesuai, serta menjadi dasar penagihan pelunasan pembayaran sesuai referensi Invoice terkait.",

            default => '',
        };
    }
}
