<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PembayaranSpp extends AbstractMigration
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
        $table = $this->table('pembayaran_spp');
        $table->addColumn('siswa_id', 'integer', ['null' => false])
              ->addColumn('tarif_spp_id', 'integer', ['null' => false])
              ->addColumn('tanggal_pembayaran', 'date', ['null' => false])
              ->addColumn('jumlah_bayar', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
              ->addColumn('metode_pembayaran', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('nomor_kwitansi', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('catatan', 'text', ['null' => true])
            //   ->addForeignKey('id_siswa', 'siswa', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            //   ->addForeignKey('id_tarif', 'tarif_spp', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
              ->addTimestamps()
              ->create();
    }
}
