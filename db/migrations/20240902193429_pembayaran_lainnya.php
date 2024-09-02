<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PembayaranLainnya extends AbstractMigration
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
        $table = $this->table('pembayaran_lainnya');
        $table->addColumn('nama_pembayaran', 'string', ['limit' => 100, 'null' => false])
              ->addColumn('bisa_diangsur', 'boolean', ['default' => true])  // True jika bisa diangsur, False jika sekali lunas
              ->addColumn('keterangan', 'text', ['null' => true])
              ->addColumn('status_aktif', 'boolean', ['default' => true])  // Menentukan apakah jenis pembayaran ini aktif
              ->addTimestamps()
              ->create();

    }
}
