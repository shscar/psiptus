<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SiswaPembayaranLainnyaKelas extends AbstractMigration
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
        $table = $this->table('siswa_pembayaran_lainnya_kelas');
        $table->addColumn('siswa_pembayaran_lainnya_id', 'integer', ['null' => false])
            ->addColumn('kelas_id', 'integer', ['null' => false])
            ->addTimestamps()
            ->create();
    }
}