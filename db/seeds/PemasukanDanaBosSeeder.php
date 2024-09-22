<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class PemasukanDanaBosSeeder extends AbstractSeed
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
                'tanggal' => '2024-09-01',
                'deskripsi' => 'Dana BOS Tahap 1',
                'nominal' => 150000000.00,
                'sumber_dana' => 'APBN',
                'tahun_ajaran_id' => 1,
                'keterangan' => 'Dana diterima untuk semester pertama',
            ],
            [
                'tanggal' => '2024-12-01',
                'deskripsi' => 'Dana BOS Tahap 2',
                'nominal' => 200000000.00,
                'sumber_dana' => 'APBN',
                'tahun_ajaran_id' => 1,
                'keterangan' => 'Dana diterima untuk semester kedua',
            ],
            [
                'tanggal' => '2025-03-01',
                'deskripsi' => 'Dana BOS Tambahan',
                'nominal' => 50000000.00,
                'sumber_dana' => 'APBN',
                'tahun_ajaran_id' => 1,
                'keterangan' => 'Dana tambahan untuk kegiatan ekstra kurikuler',
            ],
        ];

        $pemasukanDanaBos = $this->table('pemasukan_dana_bos');
        $pemasukanDanaBos->insert($data)->save();
    }
}