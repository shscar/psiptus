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
        $riwayatDetailTarif = [];
        $riwayatDetailPembayaranLain = [];

        for ($i = 1; $i <= 2; $i++) {
            // Hitung jumlah bayar untuk tarif spp
            $jumlahBayarTarifSPP = 250000.00 + ($i * 50000);

            // Hitung jumlah bayar untuk pembayaran lainnya
            $jumlahBayarPembayaranLain = 150000.00 + ($i * 50000);

            // Total bayar dihitung dari jumlahBayarTarifSPP + jumlahBayarPembayaranLain
            $totalBayar = $jumlahBayarTarifSPP + $jumlahBayarPembayaranLain;

            $riwayatTransaksi[] = [
                'siswa_id' => $i,
                'no_invoice' => sprintf('INV-20241206%03d', $i),
                'tanggal_bayar' => date('Y-m-d', strtotime("+$i day", strtotime('2024-12-01'))),
                'jenis_bayar' => $i % 2 == 0 ? 2 : 1, // 1: Tunai, 2: Transfer
                'total_bayar' => $totalBayar,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('riwayat_transaksi_siswa')->insert($riwayatTransaksi)->saveData();

        // Ambil ID yang baru saja dimasukkan
        $riwayatTransaksiIds = $this->fetchAll('SELECT id FROM riwayat_transaksi_siswa');

        for ($i = 0; $i < count($riwayatTransaksiIds); $i++) {
            $riwayatDetailTarif[] = [
                'riwayat_transaksi_id' => $riwayatTransaksiIds[$i]['id'],
                'tarif_spp_id' => $i + 1,
                'jumlah_bayar' => 250000.00 + (($i + 1) * 50000),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $riwayatDetailPembayaranLain[] = [
                'riwayat_transaksi_id' => $riwayatTransaksiIds[$i]['id'],
                'pembayaran_lainnya_id' => $i + 1,
                'jumlah_bayar' => 150000.00 + (($i + 1) * 50000),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('riwayat_transaksi_siswa_detail_tarifspp')->insert($riwayatDetailTarif)->saveData();
        $this->table('riwayat_transaksi_siswa_detail_pembayaranlain')->insert($riwayatDetailPembayaranLain)->saveData();

    }

}