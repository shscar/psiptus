<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class RiwayatTransaksiSiswaSeeder extends AbstractSeed
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
        // Data dasar untuk riwayat transaksi siswa
        $riwayatTransaksi = [];
        for ($i = 1; $i <= 3; $i++) {
            $riwayatTransaksi[] = [
                'siswa_id' => $i,
                'no_invoice' => sprintf('INV-20241206%03d', $i),
                'tanggal_bayar' => date('Y-m-d', strtotime("+$i day", strtotime('2024-12-01'))),
                'jenis_bayar' => $i % 2 == 0 ? 2 : 1, // 1: Tunai, 2: Transfer
                'total_bayar' => 500000.00 + ($i * 100000),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('riwayat_transaksi_siswa')->insert($riwayatTransaksi)->saveData();

        // Data dasar untuk detail tarif spp
        $riwayatDetailTarif = [];
        for ($i = 1; $i <= 5; $i++) {
            $riwayatDetailTarif[] = [
                'riwayat_transaksi_id' => ($i % 3) + 1,
                'tarif_spp_id' => $i,
                'jumlah_bayar' => 250000.00 + ($i * 50000),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('riwayat_transaksi_siswa_detail_tarifspp')->insert($riwayatDetailTarif)->saveData();

        // Data dasar untuk detail pembayaran lainnya
        $riwayatDetailPembayaranLain = [];
        for ($i = 1; $i <= 5; $i++) {
            $riwayatDetailPembayaranLain[] = [
                'riwayat_transaksi_id' => ($i % 3) + 1,
                'pembayaran_lainnya_id' => $i,
                'jumlah_bayar' => 150000.00 + ($i * 50000),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('riwayat_transaksi_siswa_detail_pembayaranlain')->insert($riwayatDetailPembayaranLain)->saveData();

    }
}
