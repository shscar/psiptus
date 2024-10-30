<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class KategoriPengeluaranSeeder extends AbstractSeed
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
                'nama_kategori' => 'Pemeliharaan Bangunan',
                'icon' => 'bi-gear',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kategori' => 'Utilitas',
                'icon' => 'bi-lightning',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kategori' => 'Buku dan ATK',
                'icon' => 'bi-building',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kategori' => 'Perangkat Dan Teknologi',
                'icon' => 'bi-building',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kategori' => 'Kegiatan Ekstrakurikuler',
                'icon' => 'bi-building',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kategori' => 'Kegiatan Akademik',
                'icon' => 'bi-building',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kategori' => 'Pengeluaran Kesejahteraan Siswa',
                'icon' => 'bi-building',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('kategori_pengeluaran')->insert($data)->saveData();
    }
}