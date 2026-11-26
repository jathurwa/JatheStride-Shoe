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
        $this->shoeModel      = new ShoeModel();
        $this->categoryModel  = new CategoryModel();
        $this->sizeStockModel = new ShoeSizeStockModel();
    }

    public function index()
    {
        return redirect()->to('/');
    }

    /**
     * Dedicated offers / on-sale view
     */
    public function offers()
    {
        $allCategories = $this->categoryModel->findAll();
        $perPage = 20;
        $requestedPage = max(1, (int) ($this->request->getGet('page') ?? 1));
        $totalFilteredCount = $this->shoeModel->countOffers();
        $totalPages = max(1, (int) ceil($totalFilteredCount / $perPage));
        $currentPage = min($requestedPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $shoes = $this->shoeModel->getOffers($perPage, $offset);
        $totalShoesCount = $this->shoeModel->countShoesByCategory(null);

        return view('storefront/home', [
            'title'              => 'Special Offers & Discounts | ApexStride',
            'shoes'              => $shoes,
            'categories'         => $allCategories,
            'selectedCategory'   => 'offers',
            'isOffersOnly'       => true,
            'offersCount'        => $totalFilteredCount,
            'totalShoesCount'    => $totalShoesCount,
            'totalFilteredCount' => $totalFilteredCount,
            'currentPage'        => $currentPage,
            'totalPages'         => $totalPages,
            'perPage'            => $perPage,
            'paginationBaseUrl'  => base_url('offers'),
            'featuredShoes'      => array_filter($shoes, static fn($s) => !empty($s['is_featured'])),
        ]);
    }

    /**
     * Category filtering view
     */
    public function category(string $slug)
    {
        if ($slug === 'all') {
            return redirect()->to('/');
        }

        if ($slug === 'offers') {
            return redirect()->to('/offers');
        }

        $category = $this->categoryModel->where('slug', $slug)->first();
        if (!$category) {
            throw PageNotFoundException::forPageNotFound("Category '{$slug}' not found.");
        }

        $perPage = 20;
        $requestedPage = max(1, (int) ($this->request->getGet('page') ?? 1));
        $totalFilteredCount = $this->shoeModel->countShoesByCategory((int) $category['id']);
        $totalPages = max(1, (int) ceil($totalFilteredCount / $perPage));
        $currentPage = min($requestedPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $shoes = $this->shoeModel->getShoesWithCategory((int) $category['id'], $perPage, $offset);
        $allCategories = $this->categoryModel->findAll();
        $offersCount = $this->shoeModel->countOffers();
        $totalShoesCount = $this->shoeModel->countShoesByCategory(null);

        return view('storefront/home', [
            'title'              => $category['name'] . ' Shoes | ApexStride',
            'shoes'              => $shoes,
            'categories'         => $allCategories,
            'currentCategory'    => $category,
            'selectedCategory'   => $slug,
            'isOffersOnly'       => false,
            'offersCount'        => $offersCount,
            'totalShoesCount'    => $totalShoesCount,
            'totalFilteredCount' => $totalFilteredCount,
            'currentPage'        => $currentPage,
            'totalPages'         => $totalPages,
            'perPage'            => $perPage,
            'paginationBaseUrl'  => base_url('category/' . $slug),
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

    public function search()
    {
        $query = trim((string) $this->request->getGet('q'));
        $perPage = 20;
        $requestedPage = max(1, (int) ($this->request->getGet('page') ?? 1));

        $builder = $this->shoeModel
            ->select('shoes.*, categories.name as category_name, categories.slug as category_slug')
            ->join('categories', 'categories.id = shoes.category_id', 'left');

        if ($query !== '') {
            $builder->groupStart()
                ->like('shoes.name', $query)
                ->orLike('shoes.description', $query)
                ->orLike('categories.name', $query)
                ->orLike('shoes.colors_available', $query)
                ->orLike('shoes.sizes_available', $query)
                ->groupEnd();
        }

        $totalFilteredCount = (int) $builder->countAllResults(false);
        $totalPages = max(1, (int) ceil($totalFilteredCount / $perPage));
        $currentPage = min($requestedPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;

        $shoes = $builder
            ->orderBy('shoes.is_featured', 'DESC')
            ->orderBy('shoes.created_at', 'DESC')
            ->orderBy('shoes.id', 'DESC')
            ->findAll($perPage, $offset);

        $offersCount = $this->shoeModel->countOffers();
        $totalShoesCount = $this->shoeModel->countShoesByCategory(null);
        $paginationBaseUrl = $query !== ''
            ? base_url('search?q=' . urlencode($query))
            : base_url('search');

        return view('storefront/home', [
            'title'              => $query === '' ? 'Search Shoes | ApexStride' : 'Search: ' . $query . ' | ApexStride',
            'shoes'              => $shoes,
            'categories'         => $this->categoryModel->findAll(),
            'selectedCategory'   => null,
            'searchTerm'         => $query,
            'isOffersOnly'       => false,
            'offersCount'        => $offersCount,
            'totalShoesCount'    => $totalShoesCount,
            'totalFilteredCount' => $totalFilteredCount,
            'currentPage'        => $currentPage,
            'totalPages'         => $totalPages,
            'perPage'            => $perPage,
            'paginationBaseUrl'  => $paginationBaseUrl,
            'featuredShoes'      => array_filter($shoes, static fn($shoe) => !empty($shoe['is_featured'])),
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
