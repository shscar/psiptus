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
                'nama_kategori' => 'Pengeluaran Operasional',
                'icon' => 'bi-gear',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kategori' => 'Biaya Listrik',
                'icon' => 'bi-lightning',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kategori' => 'Biaya Perawatan Gedung',
                'icon' => 'bi-building',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('kategori_pengeluaran')->insert($data)->saveData();
    }
}