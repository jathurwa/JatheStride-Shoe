<?php

namespace App\Controllers;

use App\Models\ShoeModel;
use App\Models\CategoryModel;
use App\Models\ShoeSizeStockModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Products extends BaseController
{
    protected ShoeModel $shoeModel;
    protected CategoryModel $categoryModel;
    protected ShoeSizeStockModel $sizeStockModel;

    public function __construct()
    {
        $this->shoeModel     = new ShoeModel();
        $this->categoryModel = new CategoryModel();
        $this->sizeStockModel = new ShoeSizeStockModel();
    }

    /**
     * Category filtering view
     */
    public function category(string $slug)
    {
        $category = $this->categoryModel->where('slug', $slug)->first();
        if (!$category) {
            throw PageNotFoundException::forPageNotFound("Category '{$slug}' not found.");
        }

        $shoes = $this->shoeModel->getShoesWithCategory($category['id']);
        $allCategories = $this->categoryModel->findAll();

        return view('storefront/home', [
            'title'            => $category['name'] . ' Shoes | ApexStride',
            'shoes'            => $shoes,
            'categories'       => $allCategories,
            'currentCategory'  => $category,
            'selectedCategory' => $slug,
        ]);
    }

    /**
     * Dynamic product detail page
     */
    public function detail($identifier)
    {
        $shoe = $this->shoeModel->getShoeBySlugOrId($identifier);
        if (!$shoe) {
            throw PageNotFoundException::forPageNotFound("Shoe product not found.");
        }

        $relatedShoes = $this->shoeModel
            ->where('category_id', $shoe['category_id'])
            ->where('id !=', $shoe['id'])
            ->findAll(4);

        return view('storefront/detail', [
            'title'        => $shoe['name'] . ' | ApexStride',
            'shoe'         => $shoe,
            'sizeStock'    => $this->sizeStockModel->getQuantitiesForShoe((int) $shoe['id']),
            'relatedShoes' => $relatedShoes,
        ]);
    }

    public function cart()
    {
        return view('storefront/cart', ['title' => 'Your Bag | ApexStride']);
    }

    public function checkout()
    {
        return view('storefront/checkout', ['title' => 'Checkout | ApexStride']);
    }
}
