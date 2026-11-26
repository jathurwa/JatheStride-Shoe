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
        $currentCategory = null;
        $isOffersOnly = false;
        $categories = $this->categoryModel->findAll();

        $perPage = 20; // 5 rows x 4 columns on large screens
        $requestedPage = max(1, (int) ($this->request->getGet('page') ?? 1));

        if ($selectedCategory === 'offers' || $this->request->getGet('offers')) {
            $isOffersOnly = true;
            $selectedCategory = 'offers';
            $totalFilteredCount = $this->shoeModel->countOffers();
            $totalPages = max(1, (int) ceil($totalFilteredCount / $perPage));
            $currentPage = min($requestedPage, $totalPages);
            $offset = ($currentPage - 1) * $perPage;
            $shoes = $this->shoeModel->getOffers($perPage, $offset);
            $paginationBaseUrl = base_url('offers');
        } elseif ($selectedCategory) {
            $cat = $this->categoryModel->where('slug', $selectedCategory)->first();
            if ($cat) {
                $categoryId = (int) $cat['id'];
                $currentCategory = $cat;
            }
            $totalFilteredCount = $this->shoeModel->countShoesByCategory($categoryId);
            $totalPages = max(1, (int) ceil($totalFilteredCount / $perPage));
            $currentPage = min($requestedPage, $totalPages);
            $offset = ($currentPage - 1) * $perPage;
            $shoes = $this->shoeModel->getShoesWithCategory($categoryId, $perPage, $offset);
            $paginationBaseUrl = $cat ? base_url('category/' . $cat['slug']) : base_url();
        } else {
            $totalFilteredCount = $this->shoeModel->countShoesByCategory(null);
            $totalPages = max(1, (int) ceil($totalFilteredCount / $perPage));
            $currentPage = min($requestedPage, $totalPages);
            $offset = ($currentPage - 1) * $perPage;
            $shoes = $this->shoeModel->getShoesWithCategory(null, $perPage, $offset);
            $paginationBaseUrl = base_url();
        }

        $offersCount = $isOffersOnly ? $totalFilteredCount : $this->shoeModel->countOffers();
        $totalShoesCount = ($selectedCategory === null && !$isOffersOnly)
            ? $totalFilteredCount
            : $this->shoeModel->countShoesByCategory(null);

        $data = [
            'title'              => $isOffersOnly ? 'Special Offers | ApexStride' : 'ApexStride | Premium Footwear Collection',
            'shoes'              => $shoes,
            'categories'         => $categories,
            'currentCategory'    => $currentCategory,
            'selectedCategory'   => $selectedCategory,
            'isOffersOnly'       => $isOffersOnly,
            'offersCount'        => $offersCount,
            'totalShoesCount'    => $totalShoesCount,
            'totalFilteredCount' => $totalFilteredCount,
            'currentPage'        => $currentPage,
            'totalPages'         => $totalPages,
            'perPage'            => $perPage,
            'paginationBaseUrl'  => $paginationBaseUrl,
            'featuredShoes'      => array_filter($shoes, fn($s) => !empty($s['is_featured'])),
        ];

        return view('storefront/home', $data);
    }
}
