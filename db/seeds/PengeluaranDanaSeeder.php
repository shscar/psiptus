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
                'tanggal_pengeluaran' => '2024-12-01',
                'sumber_dana' => 'BOS',
                'pihak_terlibat' => 'CV Sumber Rezeki',
                'ket_pengeluaran' => 'Pembelian alat tulis',
                'jenis_bayar' => 1,
                'total' => 5000000.00,
                'status' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tanggal_pengeluaran' => '2024-12-02',
                'sumber_dana' => 'Komite',
                'pihak_terlibat' => 'PT Berkah Abadi',
                'ket_pengeluaran' => 'Perbaikan gedung',
                'jenis_bayar' => 2,
                'total' => 15000000.00,
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $pengeluaranDana = $this->table('pengeluaran_dana');
        $pengeluaranDana->insert($pengeluaranDanaData)->saveData();

        // Data untuk tabel pengeluaran_dana_item
        $pengeluaranDanaItemData = [
            [
                'pengeluaran_dana_id' => 1,
                'use_kategori' => false,
                'nama_pengeluaran' => 'Buku Tulis',
                'item' => 50,
                'satuan' => 'pcs',
                'harga' => 10000.00,
                'nominal' => 500000.00,
                'komite' => 0,
                'bosda' => 500000.00,
                'jumlah' => 500000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'pengeluaran_dana_id' => 2,
                'use_kategori' => false,
                'nama_pengeluaran' => 'Kaca Jendela',
                'item' => 10,
                'satuan' => 'unit',
                'harga' => 1000000.00,
                'nominal' => 10000000.00,
                'komite' => 10000000.00,
                'bosda' => 0,
                'jumlah' => 10000000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'pengeluaran_dana_id' => 2,
                'use_kategori' => true,
                'nama_pengeluaran' => 3,
                'item' => 10,
                'satuan' => 'unit',
                'harga' => 500000.00,
                'nominal' => 5000000.00,
                'komite' => 0,
                'bosda' => 5000000.00,
                'jumlah' => 5000000.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $pengeluaranDanaItem = $this->table('pengeluaran_dana_item');
        $pengeluaranDanaItem->insert($pengeluaranDanaItemData)->saveData();

        // Data untuk tabel pengeluaran_dana_bukti
        $pengeluaranDanaBuktiData = [
            [
                'pengeluaran_id' => 1,
                'file_path' => 'bukti1.png',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'pengeluaran_id' => 2,
                'file_path' => 'bukti2.png',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $pengeluaranDanaBukti = $this->table('pengeluaran_dana_bukti');
        $pengeluaranDanaBukti->insert($pengeluaranDanaBuktiData)->saveData();
    }
}