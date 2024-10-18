<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class TransaksiDanaPemasukanLain extends AbstractSeed
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
        $data = [
            [
                'jenis_dana_pemasukan_id' => 1,
                'tanggal_transaksi'  => '2024-01-15',
                'jumlah'             => 50000000.00,
                'deskripsi'          => 'Dana BOS Tahap 1',
                'periode'            => '2024',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'jenis_dana_pemasukan_id' => 2,
                'tanggal_transaksi'  => '2024-02-01',
                'jumlah'             => 2000000.00,
                'deskripsi'          => 'Pembayaran SPP Bulan Januari',
                'periode'            => '2024',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'jenis_dana_pemasukan_id' => 3,
                'tanggal_transaksi'  => '2024-03-10',
                'jumlah'             => 10000000.00,
                'deskripsi'          => 'Sumbangan dari PT ABC',
                'periode'            => null,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
        ];

        // Menyisipkan data ke dalam tabel pemasukan_dana
        $this->table('transaksi_dana_pemasukan_lain')->insert($data)->saveData();
    }
}
