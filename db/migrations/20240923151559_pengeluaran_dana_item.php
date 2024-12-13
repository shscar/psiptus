<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PengeluaranDanaItem extends AbstractMigration
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
        $table = $this->table('pengeluaran_dana_item');
        $table->addColumn('pengeluaran_dana_id', 'integer', ['null' => false])
            ->addColumn('use_kategori', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('nama_pengeluaran', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('item', 'integer', ['null' => false])
            ->addColumn('satuan', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('harga', 'decimal', options: ['precision' => 15, 'scale' => 2, 'null' => false])
            ->addColumn('nominal', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => true, 'default' => 0])
            ->addColumn('komite', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => false])
            ->addColumn('bosda', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => false])
            ->addColumn('jumlah', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => true, 'default' => 0])
            ->addTimestamps()
            ->create();
    }
}