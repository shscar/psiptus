<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TingkatKelas extends AbstractMigration
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
        $table = $this->table('tingkat_kelas');
        $table->addColumn('tahun_ajaran_id', 'integer', ['limit' => 9, 'null' => false])
              ->addColumn('tingkat', 'string', ['null' => false])                          // Contoh: 10, 11, 12
              ->addColumn('keterangan', 'text', ['null' => true])                           // Deskripsi tambahan tentang tingkat kelas
              ->addTimestamps()
              ->create();

    }
}
