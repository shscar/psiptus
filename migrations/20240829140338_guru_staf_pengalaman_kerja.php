<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GuruStafPengalamanKerja extends AbstractMigration
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
        $table = $this->table('guru_staff_pengalaman_kerja');
        $table->addColumn('guru_staff_id', 'integer')
              ->addColumn('posisi', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('institusi', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('tahun_mulai', 'year', ['null' => true])
              ->addColumn('tahun_selesai', 'year', ['null' => true])
              ->addColumn('keterangan', 'text', ['null' => true])
              ->addTimestamps()
              ->addIndex('guru_staff_id')
              ->create();

    }
}
