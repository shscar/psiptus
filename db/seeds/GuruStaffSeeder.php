<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class GuruStaffSeeder extends AbstractSeed
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
                'nip' => '198710102001121001',
                'nama_lengkap' => 'Ahmad Hidayat',
                'profile' => 'guru_matematika.png',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1987-10-10',
                'tempat_lahir' => 'Jakarta',
                'alamat' => 'Jl. Merdeka No. 12, Jakarta',
                'status_kerja' => 'Aktif',
                'tanggal_mulai_kerja' => '2005-07-01',
                'jabatan' => 'Guru Matematika',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nip' => '197512022000121002',
                'nama_lengkap' => 'Siti Rahmawati',
                'profile' => 'guru_biologi.png',
                'jenis_kelamin' => 'Perempuan',
                'tanggal_lahir' => '1975-12-02',
                'tempat_lahir' => 'Bandung',
                'alamat' => 'Jl. Kebon Sirih No. 5, Bandung',
                'status_kerja' => 'Aktif',
                'tanggal_mulai_kerja' => '2000-09-15',
                'jabatan' => 'Guru Biologi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nip' => '196806201998121003',
                'nama_lengkap' => 'Budi Prasetyo',
                'profile' => 'guru_kimia.png',
                'jenis_kelamin' => 'Laki-laki',
                'tanggal_lahir' => '1968-06-20',
                'tempat_lahir' => 'Surabaya',
                'alamat' => 'Jl. Soekarno-Hatta No. 10, Surabaya',
                'status_kerja' => 'Cuti',
                'tanggal_mulai_kerja' => '1998-01-12',
                'jabatan' => 'Guru Kimia',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('guru_staff')->insert($data)->saveData();
    }
}