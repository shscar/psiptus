<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TarifSpp extends AbstractMigration
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
        $table = $this->table('tarif_spp');
        $table->addColumn('nama_tarif', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('nominal', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('tahun_ajaran_id', 'integer', ['limit' => 9, 'null' => false])
            ->addColumn('semester', 'string', ['null' => true])
            ->addColumn('deskripsi', 'text', ['null' => true])
            ->addColumn('status_aktif', 'boolean', ['default' => true])
            ->addTimestamps()
            ->create();

    }
}