<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class JenisDanaPemasukanLain extends AbstractMigration
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
        $table = $this->table('jenis_dana_pemasukan_lain');
        $table->addColumn('nama_pendapatan', 'string', ['limit' => 500])
              ->addColumn('kategori', 'enum', ['values' => ['Internal', 'External']])
              ->addColumn('periode', 'enum', ['values' => ['Bulan', 'Tahun', 'Tahun Ajaran']])
              ->addColumn('sumber', 'string', ['limit' => 500, 'null' => true])
              ->addTimestamps()
              ->create();
    }
}
