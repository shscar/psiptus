<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TransaksiDanaPemasukanLain extends AbstractMigration
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
        $table = $this->table('transaksi_dana_pemasukan_lain');
        $table->addColumn('jenis_dana_pemasukan_id', 'integer')
              ->addColumn('tanggal_transaksi', 'date')
              ->addColumn('jumlah', 'decimal', ['precision' => 15, 'scale' => 2])
              ->addColumn('deskripsi', 'text', ['null' => true])
              ->addColumn('periode', 'string', ['limit' => 10, 'null' => true])
              ->addTimestamps()
              ->create();
    }
}
