<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class JenisDanaPemasukanLain extends AbstractSeed
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
        // Data untuk tabel jenis_pemasukan
        $data = [
            [
                'nama_pendapatan' => 'Sumbangan Orang Tua/Wali Murid',
                'kategori' => 'external',
                'periode' => 'Bulan',
                'sumber' => 'Pemerintah',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_pendapatan' => 'bazar, konser, atau lomba',
                'kategori' => 'internal',
                'periode' => 'Bulan',
                'sumber' => 'Siswa',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_pendapatan' => 'Sponsorship',
                'kategori' => 'external',
                'periode' => 'Tahun',
                'sumber' => 'Sponsor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_pendapatan' => 'Program ekstrakurikuler',
                'kategori' => 'internal',
                'periode' => 'Tahun Ajaran',
                'sumber' => 'Sponsor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_pendapatan' => 'Penyewaan gedung atau lapangan (seminar)',
                'kategori' => 'external',
                'periode' => 'Tahun',
                'sumber' => 'Sponsor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_pendapatan' => 'Pendanaan dari yayasan, lembaga, atau pemerintah',
                'kategori' => 'external',
                'periode' => 'Tahun Ajaran',
                'sumber' => 'Sponsor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_pendapatan' => 'Donasi dari Alumni',
                'kategori' => 'internal',
                'periode' => 'Tahun Ajaran',
                'sumber' => 'Sponsor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_pendapatan' => 'Dana dari Lembaga Internasional (Non-Pemerintah)',
                'kategori' => 'external',
                'periode' => 'Tahun',
                'sumber' => 'Sponsor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_pendapatan' => 'Penyelenggaraan Pelatihan dan Workshop',
                'kategori' => 'external',
                'periode' => 'Tahun',
                'sumber' => 'Sponsor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Menyisipkan data ke dalam tabel jenis_pemasukan
        $this->table('jenis_dana_pemasukan_lain')->insert($data)->saveData();
    }
}