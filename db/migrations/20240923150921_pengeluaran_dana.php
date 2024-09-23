<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PengeluaranDana extends AbstractMigration
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
        $table = $this->table('pengeluaran_dana');
        $table->addColumn('tanggal_pengeluaran', 'date', ['null' => false])
            ->addColumn('bukti_pengeluaran', 'string', ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('pihak_terlibat', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('detail_kategori_pengeluaran_id', 'integer', ['null' => false])
            ->addColumn('sumber_dana', 'string', ['limit' => 255, 'default' => 1])
            ->addColumn('jenis_bayar', 'integer', ['null' => false])
            ->addColumn('total_jumlah', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0])
            ->addTimestamps()
            ->create();
    }
}