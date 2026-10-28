<?php

namespace App\Models;

use CodeIgniter\Model;

class ShoeModel extends Model
{
    protected $table            = 'shoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'slug',
        'category_id',
        'price',
        'sale_price',
        'description',
        'image_url',
        'gallery_images',
        'cloudinary_public_id',
        'sizes_available',
        'colors_available',
        'is_featured',
        'stock_quantity',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Strict Validation Rules
    protected $validationRules = [
        'name'           => 'required|min_length[3]|max_length[255]',
        'category_id'    => 'required|is_natural_no_zero',
        'price'          => 'required|decimal|greater_than[0]',
        'sale_price'     => 'permit_empty|decimal',
        'description'    => 'required|min_length[10]',
        'image_url'      => 'required|valid_url',
        'stock_quantity' => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Shoe name is required.',
            'min_length' => 'Shoe name must be at least 3 characters long.',
        ],
        'category_id' => [
            'required'            => 'Please assign the shoe to a valid category.',
            'is_natural_no_zero'  => 'Category must be a valid selection.',
        ],
        'price' => [
            'required'     => 'Shoe price is required.',
            'decimal'      => 'Price must be a valid decimal amount (e.g. 129.99).',
            'greater_than' => 'Price must be greater than $0.00.',
        ],
        'image_url' => [
            'required'  => 'An uploaded shoe image or valid cloud image URL is required.',
            'valid_url' => 'The image URL must be a valid HTTP/HTTPS link.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Fetch shoes with joined category name
     */
    public function getShoesWithCategory(?int $categoryId = null, int $limit = 20)
    {
        $builder = $this->select('shoes.*, categories.name as category_name, categories.slug as category_slug')
                        ->join('categories', 'categories.id = shoes.category_id', 'left')
                        ->orderBy('shoes.is_featured', 'DESC')
                        ->orderBy('shoes.created_at', 'DESC');

        if ($categoryId !== null) {
            $builder->where('shoes.category_id', $categoryId);
        }

        return $builder->findAll($limit);
    }

    /**
     * Find shoe by slug or ID with category details
     */
    public function getShoeBySlugOrId($identifier)
    {
        $builder = $this->select('shoes.*, categories.name as category_name, categories.slug as category_slug')
                        ->join('categories', 'categories.id = shoes.category_id', 'left');

        if (is_numeric($identifier)) {
            $builder->where('shoes.id', (int)$identifier);
        } else {
            $builder->where('shoes.slug', $identifier);
        }

        return $builder->first();
    }
}
