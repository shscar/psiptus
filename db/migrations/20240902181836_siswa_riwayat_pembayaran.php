<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SiswaRiwayatPembayaran extends AbstractMigration
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
    $table = $this->table('siswa_riwayat_pembayaran');
    $table->addColumn('pembayaran_id', 'integer', ['null' => false])
      ->addColumn('siswa_id', 'integer', ['null' => false])
      ->addColumn('aksi', 'string', ['limit' => 50, 'null' => false])
      ->addColumn('tanggal_aksi', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('dilakukan_oleh', 'integer', ['null' => true])
      ->addColumn('jumlah_bayar', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
      ->addColumn('metode_pembayaran', 'string', ['limit' => 50, 'null' => true])
      ->addColumn('nomor_kwitansi', 'string', ['limit' => 50, 'null' => true])
      ->addColumn('catatan', 'text', ['null' => true])
      ->addTimestamps()
      ->create();
    //   ->addForeignKey('id_pembayaran', 'pembayaran_spp', 'id_pembayaran', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
    //   ->addForeignKey('dilakukan_oleh', 'pengguna', 'id_user', ['delete'=> 'SET_NULL', 'update'=> 'NO_ACTION'])

  }
}