<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class KelasSeeder extends AbstractSeed
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
                'nama_kelas' => 'X RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'tingkat_kelas_id' => 1,
                'wali_kelas_id' => 3,
                'jumlah_siswa' => 32,
                'gedung' => 'A1',
                'keterangan' => 'Kelas dengan fokus pada pengembangan perangkat lunak',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kelas' => 'XI TKJ 2',
                'jurusan' => 'Teknik Komputer dan Jaringan',
                'tingkat_kelas_id' => 2,
                'wali_kelas_id' => 1,
                'jumlah_siswa' => 28,
                'gedung' => 'B2',
                'keterangan' => 'Kelas fokus pada jaringan komputer dan administrasi sistem',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_kelas' => 'XII MM 1',
                'jurusan' => 'Multimedia',
                'tingkat_kelas_id' => 3,
                'wali_kelas_id' => 3,
                'jumlah_siswa' => 30,
                'gedung' => 'C3',
                'keterangan' => 'Kelas dengan fokus pada desain grafis dan produksi media',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('kelas')->insert($data)->saveData();
    }
}