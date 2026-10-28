<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShoeGalleryImages extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('gallery_images', 'shoes')) {
            $this->forge->addColumn('shoes', [
                'gallery_images' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'image_url',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('gallery_images', 'shoes')) {
            $this->forge->dropColumn('shoes', 'gallery_images');
        }
    }
}