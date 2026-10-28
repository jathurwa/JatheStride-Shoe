<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColorsAvailableToShoes extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('colors_available', 'shoes')) {
            $this->forge->addColumn('shoes', [
                'colors_available' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'sizes_available',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('colors_available', 'shoes')) {
            $this->forge->dropColumn('shoes', 'colors_available');
        }
    }
}