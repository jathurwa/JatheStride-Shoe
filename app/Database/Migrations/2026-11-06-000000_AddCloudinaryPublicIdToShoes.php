<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCloudinaryPublicIdToShoes extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('cloudinary_public_id', 'shoes')) {
            $this->forge->addColumn('shoes', [
                'cloudinary_public_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'image_url',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('cloudinary_public_id', 'shoes')) {
            $this->forge->dropColumn('shoes', 'cloudinary_public_id');
        }
    }
}
