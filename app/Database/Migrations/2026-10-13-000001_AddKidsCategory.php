<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKidsCategory extends Migration
{
    public function up()
    {
        $categories = $this->db->table('categories');
        if ($categories->where('slug', 'kids')->countAllResults() === 0) {
            $categories->insert([
                'name'        => 'Kids',
                'slug'        => 'kids',
                'description' => 'Comfortable footwear for kids.',
                'icon'        => 'fa-child',
            ]);
        }
    }

    public function down()
    {
        $category = $this->db->table('categories')->where('slug', 'kids')->get()->getRowArray();
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
