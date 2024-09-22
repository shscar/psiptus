<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePemasukanDanaBosTable extends AbstractMigration
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
        $table = $this->table('pemasukan_dana_bos');
        $table->addColumn('tanggal', 'date', ['null' => false])
            ->addColumn('deskripsi', 'string', ['null' => true])
            ->addColumn('nominal', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => false])
            ->addColumn('sumber_dana', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('tahun_ajaran_id', 'integer', ['null' => true])
            ->addColumn('keterangan', 'text', ['null' => true])
            ->addTimestamps()
            ->create();
    }
}