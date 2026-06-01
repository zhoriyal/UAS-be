<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('articles')->insert([
            [
                'emoji' => '🎯',
                'title' => 'Tips Hemat Pajak UMKM: Bahasa Ringan',
                'excerpt' => 'Tips Hemat Pajak UMKM dengan bahasa ringan yang membantu menyederhanakan perpajakan dalam artikel...',
                'content' => 'Artikel lengkap tentang tips hemat pajak UMKM. Pajak adalah kewajiban setiap warga negara dan badan usaha. Namun, dengan perencanaan yang tepat, UMKM dapat mengoptimalkan kewajiban pajaknya secara legal.\n\nBeberapa tips yang bisa diterapkan:\n1. Manfaatkan tarif PPh Final 0,5% untuk UMKM dengan omzet di bawah Rp 4,8 miliar per tahun\n2. Catat semua pengeluaran yang dapat dikurangkan\n3. Manfaatkan fasilitas perpajakan yang tersedia\n4. Konsultasikan dengan konsultan pajak jika diperlukan',
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'emoji' => '📅',
                'title' => 'Jadwal Penting Pelaporan Pajak Bulan Ini',
                'excerpt' => 'Jangan sampai terlewat! Cek jadwal lengkap batas waktu pelaporan dan pembayaran pajak bulan ini...',
                'content' => 'Jadwal pelaporan pajak sangat penting untuk diperhatikan agar terhindar dari sanksi dan denda.\n\nJadwal penting:\n- Tanggal 10: Pelaporan PPh Pasal 21 (Masa sebelumnya)\n- Tanggal 15: Pelaporan PPN (Masa sebelumnya)\n- Tanggal 20: Pelaporan PPh Pasal 23/26\n- Akhir bulan: Pembayaran PPh Final UMKM 0,5%\n\nPastikan untuk selalu mengecek jadwal terbaru di website resmi DJP.',
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'emoji' => '👨‍💼',
                'title' => 'Panduan PPh Pasal 21 untuk Karyawan Baru',
                'excerpt' => 'Bingung cara hitung potongan pajak gaji karyawan? Simak panduan praktis perhitungan PPh Pasal 21...',
                'content' => 'PPh Pasal 21 adalah pajak atas penghasilan berupa gaji, upah, honorarium, dan imbalan lain sehubungan dengan pekerjaan yang dilakukan oleh wajib pajak dalam negeri.\n\nCara menghitung:\n1. Hitung total penghasilan bruto setahun\n2. Kurangkan biaya jabatan (5%, maks Rp 500.000/bulan)\n3. Kurangkan iuran pensiun dan BPJS\n4. Hasilnya adalah penghasilan neto\n5. Kurangkan PTKP sesuai status perkawinan\n6. Hasilnya adalah PKP (Penghasilan Kena Pajak)\n7. Terapkan tarif progresif 5%-35%',
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'emoji' => '📊',
                'title' => 'Perbedaan PPh Final UMKM dan PPh Biasa',
                'excerpt' => 'Pahami perbedaan antara PPh Final 0,5% dan PPh Regular agar tidak salah dalam pelaporan...',
                'content' => 'UMKM memiliki pilihan dalam membayar pajak, yaitu PPh Final berdasarkan PP 23/2018 atau PPh berdasarkan tarif umum.\n\nPPh Final UMKM (PP 23/2018):\n- Tarif: 0,5% dari omzet kotor\n- Berlaku untuk: UMKM dengan omzet ≤ Rp 4,8 miliar/tahun\n- Masa berlaku: 3 tahun (bisa diperpanjang)\n- Pembayaran: Setiap bulan\n\nPPh Regular:\n- Tarif: Progresif 5%-35%\n- Berdasarkan: Penghasilan neto (setelah dikurangi biaya)\n- Pembayaran: Setahun sekali (SPT Tahunan)\n\nPilih yang sesuai dengan kondisi usaha Anda!',
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
