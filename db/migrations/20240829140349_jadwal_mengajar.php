<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class JadwalMengajar extends AbstractMigration
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
        $table = $this->table('jadwal_mengajar');
        $table->addColumn('guru_id', 'integer')
              ->addColumn('mata_pelajaran', 'string', ['limit' => 100])
              ->addColumn('kelas', 'string', ['limit' => 20])
              ->addColumn('hari', 'enum', ['values' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']])
              ->addColumn('jam_mulai', 'time')
              ->addColumn('jam_selesai', 'time')
              ->addTimestamps()
              ->addIndex('guru_id')
              ->create();

    }
}
