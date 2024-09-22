<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class KategoriPengeluaran extends AbstractMigration
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
        $table = $this->table('kategori_pengeluaran');
        $table->addColumn('nama_kategori', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('parent_id', 'integer', ['null' => true, 'default' => null])
            ->addColumn('icon', 'string', ['limit' => 50, 'default' => 'bi-folder'])
            ->addTimestamps()
            ->create();
    }
}