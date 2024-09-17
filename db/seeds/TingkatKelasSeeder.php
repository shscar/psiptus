<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class TingkatKelasSeeder extends AbstractSeed
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
                'tahun_ajaran_id' => 1,
                'tingkat' => 'X',
                'keterangan' => 'Kelas untuk tingkat 10',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tahun_ajaran_id' => 2,
                'tingkat' => 'XI',
                'keterangan' => 'Kelas untuk tingkat 11',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tahun_ajaran_id' => 3,
                'tingkat' => 'XII',
                'keterangan' => 'Kelas untuk tingkat 12',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert data into the table
        $this->table('tingkat_kelas')->insert($data)->saveData();
    }
}