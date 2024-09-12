<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Siswa extends AbstractMigration
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
        $table = $this->table('siswa');
        $table->addColumn('nis', 'string', ['limit' => 20])
              ->addColumn('nisn', 'integer')
              ->addColumn('nama_lengkap', 'string', ['limit' => 100])
              ->addColumn('jenis_kelamin', 'enum', ['values' => ['Laki-laki', 'Perempuan']])
              ->addColumn('tanggal_lahir', 'date')
              ->addColumn('tempat_lahir', 'string', ['limit' => 50])
              ->addColumn('alamat', 'text', ['null' => true])
              ->addColumn('kelas_id', 'integer')
              ->addColumn('status', 'enum', ['values' => ['Aktif', 'Tidak Aktif'], 'default' => 'Aktif'])
              ->addTimestamps()
              ->addIndex('nis', ['unique' => true])
              ->create();

    }
}
