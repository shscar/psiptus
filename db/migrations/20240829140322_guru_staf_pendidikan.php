<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GuruStafPendidikan extends AbstractMigration
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
        $table = $this->table('guru_staff_pendidikan');
        $table->addColumn('guru_staff_id', 'integer')
              ->addColumn('jenjang_pendidikan', 'enum', ['values' => ['D3', 'S1', 'S2', 'S3']])
              ->addColumn('institusi', 'string', ['limit' => 100])
              ->addColumn('jurusan', 'string', ['limit' => 100])
              ->addColumn('tahun_lulus', 'year', ['null' => true])
              ->addTimestamps()
              ->addIndex('guru_staff_id')
              ->create();

    }
}
