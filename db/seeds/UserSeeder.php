<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [
            [
                'guru_staff_id' => 1,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'last_logout' => null,
                'last_login' => null,
                'status' => 'active',
                'role' => 'super_admin',
                'reset_token' => null,
                'token_expiry' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'guru_staff_id' => 2,
                'username' => 'user1',
                'email' => 'user1@example.com',
                'password' => password_hash('userpassword', PASSWORD_BCRYPT),
                'last_login' => null,
                'last_logout' => null,
                'status' => 'active',
                'role' => 'user',
                'reset_token' => null,
                'token_expiry' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('users')->insert($data)->saveData();
    }
}