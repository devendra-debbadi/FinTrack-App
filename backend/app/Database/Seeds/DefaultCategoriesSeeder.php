<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DefaultCategoriesSeeder extends Seeder
{
    public function run()
    {
        // Get admin user and their default profile
        $admin = $this->db->table('users')->where('email', 'admin@fintrack.app')->get()->getRowArray();

        if (! $admin) {
            echo "Admin user not found. Run AdminUserSeeder first.\n";
            return;
        }

        $profile = $this->db->table('profiles')
            ->where('user_id', $admin['id'])
            ->where('is_default', 1)
            ->get()->getRowArray();

        if (! $profile) {
            echo "Default profile not found for admin.\n";
            return;
        }

        $userId = $admin['id'];
        $profileId = $profile['id'];
        $now = date('Y-m-d H:i:s');

        // Expense categories
        $expenseCategories = [
            ['name' => 'Housing',        'icon' => 'pi-home',           'color' => '#6366f1'],
            ['name' => 'Transportation', 'icon' => 'pi-car',            'color' => '#8b5cf6'],
            ['name' => 'Food & Dining',  'icon' => 'pi-shopping-bag',   'color' => '#ec4899'],
            ['name' => 'Groceries',      'icon' => 'pi-shopping-cart',  'color' => '#f43f5e'],
            ['name' => 'Utilities',      'icon' => 'pi-bolt',           'color' => '#f59e0b'],
            ['name' => 'Healthcare',     'icon' => 'pi-heart',          'color' => '#ef4444'],
            ['name' => 'Insurance',      'icon' => 'pi-shield',         'color' => '#14b8a6'],
            ['name' => 'Entertainment',  'icon' => 'pi-ticket',         'color' => '#a855f7'],
            ['name' => 'Shopping',       'icon' => 'pi-tag',            'color' => '#f97316'],
            ['name' => 'Education',      'icon' => 'pi-book',           'color' => '#3b82f6'],
            ['name' => 'Personal Care',  'icon' => 'pi-user',           'color' => '#d946ef'],
            ['name' => 'Subscriptions',  'icon' => 'pi-replay',         'color' => '#0ea5e9'],
            ['name' => 'Travel',         'icon' => 'pi-globe',          'color' => '#06b6d4'],
            ['name' => 'Gifts',          'icon' => 'pi-gift',           'color' => '#e11d48'],
            ['name' => 'Other Expense',  'icon' => 'pi-ellipsis-h',     'color' => '#6b7280'],
        ];

        // Income categories
        $incomeCategories = [
            ['name' => 'Salary',         'icon' => 'pi-wallet',         'color' => '#10b981'],
            ['name' => 'Freelance',      'icon' => 'pi-briefcase',      'color' => '#22c55e'],
            ['name' => 'Investments',    'icon' => 'pi-chart-line',     'color' => '#059669'],
            ['name' => 'Rental Income',  'icon' => 'pi-building',       'color' => '#34d399'],
            ['name' => 'Bonus',          'icon' => 'pi-star',           'color' => '#84cc16'],
            ['name' => 'Refunds',        'icon' => 'pi-replay',         'color' => '#a3e635'],
            ['name' => 'Other Income',   'icon' => 'pi-ellipsis-h',     'color' => '#6b7280'],
        ];

        $order = 0;
        foreach ($expenseCategories as $cat) {
            $this->db->table('categories')->insert([
                'user_id'     => $userId,
                'profile_id'  => $profileId,
                'name'        => $cat['name'],
                'type'        => 'expense',
                'icon'        => $cat['icon'],
                'color'       => $cat['color'],
                'is_archived' => 0,
                'sort_order'  => $order++,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $order = 0;
        foreach ($incomeCategories as $cat) {
            $this->db->table('categories')->insert([
                'user_id'     => $userId,
                'profile_id'  => $profileId,
                'name'        => $cat['name'],
                'type'        => 'income',
                'icon'        => $cat['icon'],
                'color'       => $cat['color'],
                'is_archived' => 0,
                'sort_order'  => $order++,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        echo "Created " . count($expenseCategories) . " expense categories and " . count($incomeCategories) . " income categories.\n";
    }
}
