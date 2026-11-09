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
        $isOffersOnly = false;
        $categories = $this->categoryModel->findAll();

        if ($selectedCategory === 'offers' || $this->request->getGet('offers')) {
            $isOffersOnly = true;
            $shoes = $this->shoeModel->getOffers(100);
            $selectedCategory = 'offers';
        } elseif ($selectedCategory) {
            $cat = $this->categoryModel->where('slug', $selectedCategory)->first();
            if ($cat) {
                $categoryId = $cat['id'];
            }
            $shoes = $this->shoeModel->getShoesWithCategory($categoryId, 100);
        } else {
            $shoes = $this->shoeModel->getShoesWithCategory(null, 100);
        }

        $allOffers = $this->shoeModel->getOffers(100);
        $offersCount = count($allOffers);
        $totalShoesCount = $this->shoeModel->countAllResults(false);

        $data = [
            'title'            => $isOffersOnly ? 'Special Offers | ApexStride' : 'ApexStride | Premium Footwear Collection',
            'shoes'            => $shoes,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
            'isOffersOnly'     => $isOffersOnly,
            'offersCount'      => $offersCount,
            'totalShoesCount'  => $totalShoesCount,
            'featuredShoes'    => array_filter($shoes, fn($s) => !empty($s['is_featured'])),
        ];

        return view('storefront/home', $data);
    }
}
