<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShoeSizeStock extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('shoe_size_stock')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shoe_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'size' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'quantity' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['shoe_id', 'size'], 'uq_shoe_size_stock');
        $this->forge->addForeignKey('shoe_id', 'shoes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('shoe_size_stock');
    }

    public function down()
    {
        $this->forge->dropTable('shoe_size_stock', true);
    }
}