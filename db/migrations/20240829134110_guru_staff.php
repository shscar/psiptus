<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GuruStaff extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('guru_staff');
        $table->addColumn('nip', 'string', ['limit' => 20])
              ->addColumn('nama_lengkap', 'string', ['limit' => 100])
              ->addColumn('profile', 'string', ['limit' => 300, 'null' => true])
              ->addColumn('jenis_kelamin', 'enum', ['values' => ['Laki-laki', 'Perempuan']])
              ->addColumn('tanggal_lahir', 'date')
              ->addColumn('tempat_lahir', 'string', ['limit' => 50])
              ->addColumn('alamat', 'text', ['null' => true])
              ->addColumn('status_kerja', 'enum', ['values' => ['Aktif', 'Tidak Aktif', 'Cuti'], 'default' => 'Aktif'])
              ->addColumn('tanggal_mulai_kerja', 'date')
              ->addColumn('jabatan', 'string', ['limit' => 50])
              ->addTimestamps()
              ->addIndex('nip', ['unique' => true])
              ->create();

    }
}
