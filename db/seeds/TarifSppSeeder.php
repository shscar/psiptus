<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class TarifSppSeeder extends AbstractSeed
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
                'nama_tarif' => 'SPP Bulanan',
                'nominal' => 500000.00,
                'tahun_ajaran_id' => 1,
                'semester' => 'gasal',
                'priode_awal' => '2024-01-01',
                'priode_akhir' => '2024-06-30',
                'deskripsi' => 'Tarif SPP untuk bulan Januari sampai Desember',
                'status_aktif' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_tarif' => 'SPP Semester',
                'nominal' => 2500000.00,
                'tahun_ajaran_id' => 2,
                'semester' => 'genap',
                'priode_awal' => '2024-07-01',
                'priode_akhir' => '2024-12-30',
                'deskripsi' => 'Tarif SPP untuk satu semester',
                'status_aktif' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_tarif' => 'SPP Tahunan',
                'nominal' => 5000000.00,
                'tahun_ajaran_id' => 3,
                'semester' => 'none',
                'priode_awal' => '2024-01-01',
                'priode_akhir' => '2024-12-30',
                'deskripsi' => 'Tarif SPP untuk satu tahun ajaran',
                'status_aktif' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('tarif_spp')->insert($data)->saveData();
    }
}