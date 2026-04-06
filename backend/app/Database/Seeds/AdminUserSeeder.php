<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $this->db->table('users')->insert([
            'email'           => 'admin@fintrack.app',
            'password_hash'   => password_hash('Admin@1234', PASSWORD_ARGON2ID),
            'name'            => 'Administrator',
            'role'            => 'admin',
            'is_active'       => 1,
            'must_change_pwd' => 1,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        $adminId = $this->db->insertID();

        // Create default profile for admin
        $this->db->table('profiles')->insert([
            'user_id'    => $adminId,
            'name'       => 'Personal',
            'is_default' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create default settings for admin
        $this->db->table('settings')->insert([
            'user_id'     => $adminId,
            'currency'    => 'EUR',
            'theme'       => 'dark',
            'language'    => 'en',
            'date_format' => 'DD/MM/YYYY',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        echo "Admin user created: admin@fintrack.app / Admin@1234\n";
    }
}
