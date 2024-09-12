<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SiswaKeluarga extends AbstractMigration
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
        $table = $this->table('siswa_keluarga');
        $table->addColumn('siswa_id', 'integer')
              ->addColumn('nama_ayah', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('pekerjaan_ayah', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('nama_ibu', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('pekerjaan_ibu', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('jumlah_saudara', 'integer', ['null' => true])
              ->addTimestamps()
              ->addIndex('siswa_id')
              ->create();

    }
}
