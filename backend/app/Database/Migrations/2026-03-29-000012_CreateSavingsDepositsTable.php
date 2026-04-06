<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSavingsDepositsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'goal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'deposit_date' => [
                'type' => 'DATE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('goal_id');
        $this->forge->addForeignKey('goal_id', 'savings_goals', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('savings_deposits');
    }

    public function down()
    {
        $this->forge->dropTable('savings_deposits');
    }
}
