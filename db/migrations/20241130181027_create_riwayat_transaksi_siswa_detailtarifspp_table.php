<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRiwayatTransaksiSiswaDetailtarifsppTable extends AbstractMigration
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
        $table = $this->table('detail_riwayat_transaksi_siswa_tarifspp');
        $table->addColumn('riwayat_transaksi_id', 'integer', ['null' => false])
            ->addColumn('tarif_spp_id', 'integer', ['null' => false])
            ->addColumn('jumlah_bayar', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => false])
            ->addTimestamps()
            ->create();
    }
}