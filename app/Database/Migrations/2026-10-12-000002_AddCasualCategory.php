<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCasualCategory extends Migration
{
    public function up()
    {
        $categories = $this->db->table('categories');
        if ($categories->where('slug', 'casual')->countAllResults() === 0) {
            $categories->insert([
                'name'        => 'Casual',
                'slug'        => 'casual',
                'description' => 'Comfortable everyday shoes for relaxed, versatile wear.',
                'icon'        => 'fa-shoe-prints',
            ]);
        }
    }

    public function down()
    {
        $category = $this->db->table('categories')->where('slug', 'casual')->get()->getRowArray();
        if ($category === null) {
            return;
        }

        $shoesUsingCategory = $this->db->table('shoes')
            ->where('category_id', $category['id'])
            ->countAllResults();

        if ($shoesUsingCategory === 0) {
            $this->db->table('categories')->where('id', $category['id'])->delete();
        }
    }
}