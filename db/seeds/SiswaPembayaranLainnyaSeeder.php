<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SiswaPembayaranLainnyaSeeder extends AbstractSeed
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
                'nama_pembayaran' => 'Biaya Seragam Sekolah',
                'bisa_diangsur' => false,
                'nominal' => 750000.00,
                'tahun_ajaran_id' => 1,
                'keterangan' => 'Pembayaran untuk pembelian seragam sekolah lengkap.',
                'semester' => 'gasal',
                'status_aktif' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'jenis_dana_pemasukan_id' => 1,
                'nama_pembayaran' => 'Biaya Buku Tahunan',
                'bisa_diangsur' => true,
                'nominal' => 500000.00,
                'tahun_ajaran_id' => 2,
                'keterangan' => 'Pembayaran untuk buku pelajaran selama satu tahun.',
                'semester' => 'none',
                'status_aktif' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'jenis_dana_pemasukan_id' => 3,
                'nama_pembayaran' => 'Biaya Kegiatan Sekolah',
                'bisa_diangsur' => false,
                'nominal' => 300000.00,
                'tahun_ajaran_id' => 3,
                'keterangan' => 'Pembayaran untuk kegiatan ekstrakurikuler dan acara sekolah lainnya.',
                'semester' => 'genap',
                'status_aktif' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('siswa_pembayaran_lainnya')->insert($data)->saveData();
    }
}