<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('AdminUserSeeder');
        $this->call('DefaultCategoriesSeeder');

        echo "\n✓ Database seeding complete.\n";
    }
}
