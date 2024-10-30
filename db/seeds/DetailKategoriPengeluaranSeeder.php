<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DetailKategoriPengeluaranSeeder extends AbstractSeed
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
                'kategori_id' => 1,
                'judul' => 'Biaya perawatan gedung',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 1,
                'judul' => 'Biaya kebersihan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 2,
                'judul' => 'Listrik',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 2,
                'judul' => 'Air',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 2,
                'judul' => 'Internet',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 3,
                'judul' => 'Pembelian buku pelajaran',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 3,
                'judul' => 'Alat tulis untuk siswa',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 3,
                'judul' => 'Alat laboratorium',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 3,
                'judul' => 'Bahan praktikum',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 4,
                'judul' => 'Komputer dan perangkat lunak',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 5,
                'judul' => 'Kegiatan Olahraga',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 5,
                'judul' => 'Kegiatan Seni dan Budaya',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 5,
                'judul' => 'Workshop',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 5,
                'judul' => 'pentas seni',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 6,
                'judul' => 'Biaya ujian nasional',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 6,
                'judul' => 'Biaya pencetakan soal',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 6,
                'judul' => 'Kunjungan Industri',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 7,
                'judul' => 'Beasiswa Kurang Mampu',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 7,
                'judul' => 'Beasiswa Prestasi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 7,
                'judul' => 'Pemeriksaan kesehatan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('detail_kategori_pengeluaran')->insert($data)->saveData();
    }
}