<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SiswaSeeder extends AbstractSeed
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
                'nis' => '1234567890',
                'nisn' => 1001001001,
                'nama_lengkap' => 'Ahmad Setiawan',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '2005-05-12',
                'tempat_lahir' => 'Jakarta',
                'alamat' => 'Jl. Merpati No. 7, Jakarta',
                'kelas_id' => 1,  // Assuming this ID exists in the `kelas` table
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nis' => '2345678901',
                'nisn' => 1001002002,
                'nama_lengkap' => 'Siti Nurhaliza',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '2006-08-21',
                'tempat_lahir' => 'Bandung',
                'alamat' => 'Jl. Kenari No. 3, Bandung',
                'kelas_id' => 2,  // Assuming this ID exists in the `kelas` table
                'status' => 'Aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nis' => '3456789012',
                'nisn' => 1001003003,
                'nama_lengkap' => 'Budi Santoso',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '2004-11-30',
                'tempat_lahir' => 'Surabaya',
                'alamat' => 'Jl. Kamboja No. 12, Surabaya',
                'kelas_id' => 3,  // Assuming this ID exists in the `kelas` table
                'status' => 'Tidak Aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('siswa')->insert($data)->saveData();
    }
}