<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRiwayatTransaksiSiswaTable extends AbstractMigration
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
        $table = $this->table('riwayat_transaksi_siswa');
        $table->addColumn('siswa_id', 'integer', ['null' => false])
            ->addColumn('no_invoice', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('tanggal_bayar', 'date', ['null' => false])
            ->addColumn('jenis_bayar', 'integer', ['limit' => 1, 'null' => false, 'comment' => '1: Tunai, 2: Transfer',])
            ->addColumn('total_bayar', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => false])
            ->addTimestamps()
            ->addIndex(['no_invoice'], ['unique' => true])
            ->create();
    }
}