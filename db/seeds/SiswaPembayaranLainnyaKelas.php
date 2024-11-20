<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SiswaPembayaranLainnyaKelas extends AbstractSeed
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
                'siswa_pembayaran_lainnya_id' => 1,
                'kelas_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'siswa_pembayaran_lainnya_id' => 1,
                'kelas_id' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'siswa_pembayaran_lainnya_id' => 2,
                'kelas_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'siswa_pembayaran_lainnya_id' => 2,
                'kelas_id' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'siswa_pembayaran_lainnya_id' => 3,
                'kelas_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        $this->table('siswa_pembayaran_lainnya_kelas')->insert($data)->saveData();
    }
}