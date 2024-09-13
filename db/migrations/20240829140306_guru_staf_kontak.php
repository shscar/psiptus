<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GuruStafKontak extends AbstractMigration
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
        $table = $this->table('guru_staff_kontak');
        $table->addColumn('guru_staff_id', 'integer')
              ->addColumn('email', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('telepon', 'string', ['limit' => 15, 'null' => true])
              ->addColumn('alamat_rumah', 'text', ['null' => true])
              ->addColumn('telepon_rumah', 'string', ['limit' => 15, 'null' => true])
              ->addTimestamps()
              ->addIndex('guru_staff_id')
              ->create();

    }
}
