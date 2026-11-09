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
        $shoes = $this->shoeModel->getOffers(100);
        $totalShoesCount = $this->shoeModel->countAllResults(false);

        return view('storefront/home', [
            'title'            => 'Special Offers & Discounts | ApexStride',
            'shoes'            => $shoes,
            'categories'       => $allCategories,
            'selectedCategory' => 'offers',
            'isOffersOnly'     => true,
            'offersCount'      => count($shoes),
            'totalShoesCount'  => $totalShoesCount,
            'featuredShoes'    => array_filter($shoes, static fn($s) => !empty($s['is_featured'])),
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

        $shoes = $this->shoeModel->getShoesWithCategory($category['id'], 100);
        $allCategories = $this->categoryModel->findAll();
        $offersCount = count($this->shoeModel->getOffers(100));
        $totalShoesCount = $this->shoeModel->countAllResults(false);

        return view('storefront/home', [
            'title'            => $category['name'] . ' Shoes | ApexStride',
            'shoes'            => $shoes,
            'categories'       => $allCategories,
            'currentCategory'  => $category,
            'selectedCategory' => $slug,
            'isOffersOnly'     => false,
            'offersCount'      => $offersCount,
            'totalShoesCount'  => $totalShoesCount,
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

        $shoes = $builder
            ->orderBy('shoes.is_featured', 'DESC')
            ->orderBy('shoes.created_at', 'DESC')
            ->findAll(100);

        $offersCount = count($this->shoeModel->getOffers(100));
        $totalShoesCount = $this->shoeModel->countAllResults(false);

        return view('storefront/home', [
            'title'            => $query === '' ? 'Search Shoes | ApexStride' : 'Search: ' . $query . ' | ApexStride',
            'shoes'            => $shoes,
            'categories'       => $this->categoryModel->findAll(),
            'selectedCategory' => null,
            'searchTerm'       => $query,
            'isOffersOnly'     => false,
            'offersCount'      => $offersCount,
            'totalShoesCount'  => $totalShoesCount,
            'featuredShoes'    => array_filter($shoes, static fn($shoe) => !empty($shoe['is_featured'])),
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
