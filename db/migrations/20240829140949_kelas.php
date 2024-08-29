<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Kelas extends AbstractMigration
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
        $table = $this->table('kelas');
        $table->addColumn('nama_kelas', 'string', ['limit' => 50])
              ->addColumn('jurusan', 'string', ['limit' => 50])
              ->addColumn('tingkat_kelas', 'integer')
              ->addColumn('wali_kelas_id', 'integer', ['null' => true])
              ->addColumn('jumlah_siswa', 'integer', ['null' => true])
              ->addColumn('gedung', 'string', ['limit' => 20, 'null' => true])
              ->addColumn('keterangan', 'text', ['null' => true])
              ->addColumn('tahun_ajaran', 'string', ['limit' => 9])
              ->addTimestamps()
              ->addIndex('wali_kelas_id')
              ->create();
        
        // Add foreign key constraint
        // $this->table('kelas')
        //     ->addForeignKey('wali_kelas_id', 'GuruStaff', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
        //     ->update();

    }
}
