<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class User extends AbstractMigration
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
        $table = $this->table('users');
        $table->addColumn('guru_staff_id', 'integer')
              ->addColumn('username', 'string', ['limit' => 50])
              ->addColumn('email', 'string', ['limit' => 100])
              ->addColumn('password', 'string', ['limit' => 255])
              ->addColumn('last_login', 'timestamp', ['null' => true])
              ->addColumn('status', 'enum', ['values' => ['active', 'inactive', 'blocked'], 'default' => 'active'])
              ->addColumn('role', 'enum', ['values' => ['user', 'admin', 'super_admin'], 'default' => 'user'])
              ->addColumn('reset_token', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('token_expiry', 'timestamp', ['null' => true])
              ->addTimestamps()
              ->addIndex(['username'], ['unique' => true])
              ->addIndex(['email'], ['unique' => true])
              ->create();

    }
}
