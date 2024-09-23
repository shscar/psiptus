<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class PengeluaranDanaSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Data untuk tabel pengeluaran_dana
        $pengeluaranDanaData = [
            [
                'tanggal_pengeluaran' => '2024-09-01',
                'bukti_pengeluaran' => 'bukti_pengeluaran1.jpg',
                'pihak_terlibat' => 'Supplier Alat Tulis',
                'detail_kategori_pengeluaran_id' => 1, // Sesuaikan dengan ID yang valid dari tabel detail_kategori_pengeluaran
                'sumber_dana' => 'Dana Sekolah',
                'jenis_bayar' => 1, // 1: Tunai, 2: Transfer, 3: Lainnya
                'total_jumlah' => 1500000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tanggal_pengeluaran' => '2024-09-10',
                'bukti_pengeluaran' => 'bukti_pengeluaran2.pdf',
                'pihak_terlibat' => 'CV Jasa Listrik',
                'detail_kategori_pengeluaran_id' => 2, // Sesuaikan dengan ID yang valid dari tabel detail_kategori_pengeluaran
                'sumber_dana' => 'Donasi Alumni',
                'jenis_bayar' => 2, // 1: Tunai, 2: Transfer, 3: Lainnya
                'total_jumlah' => 5000000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tanggal_pengeluaran' => '2024-09-15',
                'bukti_pengeluaran' => null, // Tidak ada bukti pengeluaran
                'pihak_terlibat' => 'Toko Perlengkapan Gedung',
                'detail_kategori_pengeluaran_id' => 3, // Sesuaikan dengan ID yang valid dari tabel detail_kategori_pengeluaran
                'sumber_dana' => 'Dana Pemerintah',
                'jenis_bayar' => 3, // 1: Tunai, 2: Transfer, 3: Lainnya
                'total_jumlah' => 10000000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert data ke tabel pengeluaran_dana
        $this->table('pengeluaran_dana')->insert($pengeluaranDanaData)->saveData();

        // Data untuk tabel item_pengeluaran_dana
        $itemPengeluaranDanaData = [];
        $pengeluaranIds = [1, 2, 3]; // Asumsi ID yang valid dari tabel pengeluaran_dana

        foreach ($pengeluaranIds as $id) {
            for ($i = 1; $i <= 5; $i++) {
                $itemPengeluaranDanaData[] = [
                    'pengeluaran_id' => $id, // Sesuaikan dengan ID pengeluaran_dana yang valid
                    'nama_pengeluaran' => "Item Pengeluaran $i untuk Pengeluaran $id",
                    'keterangan' => "Keterangan pengeluaran $i untuk pengeluaran $id",
                    'jumlah_barang' => rand(1, 10),
                    'nilai_bayar' => rand(100000, 500000) / 100, // Nilai acak antara 1000 dan 5000
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        // Insert data ke tabel item_pengeluaran_dana
        $this->table('item_pengeluaran_dana')->insert($itemPengeluaranDanaData)->saveData();
    }
}