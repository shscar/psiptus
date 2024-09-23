<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ItemPengeluaranDana extends AbstractMigration
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
        $table = $this->table('item_pengeluaran_dana');
        $table->addColumn('pengeluaran_id', 'integer', ['null' => false])
            ->addColumn('nama_pengeluaran', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('keterangan', 'text', ['null' => true, 'default' => null])
            ->addColumn('jumlah_barang', 'integer', ['null' => false])
            ->addColumn('nilai_bayar', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0])
            ->addTimestamps()
            ->create();
    }
}