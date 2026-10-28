<?php

namespace App\Controllers;

use App\Models\ShoeModel;
use App\Models\CategoryModel;

class Home extends BaseController
{
    protected ShoeModel $shoeModel;
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->shoeModel     = new ShoeModel();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Homepage: showcases featured shoes and category filter
     */
    public function index()
    {
        $selectedCategory = $this->request->getGet('category');
        $categoryId = null;

        $categories = $this->categoryModel->findAll();

        if ($selectedCategory) {
            $cat = $this->categoryModel->where('slug', $selectedCategory)->first();
            if ($cat) {
                $categoryId = $cat['id'];
            }
        }

        $shoes = $this->shoeModel->getShoesWithCategory($categoryId);

        $data = [
            'title'            => 'ApexStride | Premium Footwear Collection',
            'shoes'            => $shoes,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
            'featuredShoes'    => array_filter($shoes, fn($s) => !empty($s['is_featured'])),
        ];

        return view('storefront/home', $data);
    }
}
