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
                'judul' => 'Pembayaran Tagihan Listrik Bulan September',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 2,
                'judul' => 'Pembelian Peralatan Kebersihan Gedung',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kategori_id' => 3,
                'judul' => 'Perawatan AC Gedung',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('detail_kategori_pengeluaran')->insert($data)->saveData();
    }
}