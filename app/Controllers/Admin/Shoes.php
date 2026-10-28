<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShoeModel;
use App\Models\CategoryModel;
use App\Models\ShoeSizeStockModel;
use App\Libraries\ImageUploadService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Shoes extends BaseController
{
    protected ShoeModel $shoeModel;
    protected CategoryModel $categoryModel;
    protected ShoeSizeStockModel $sizeStockModel;
    protected ImageUploadService $uploadService;

    public function __construct()
    {
        $this->shoeModel     = new ShoeModel();
        $this->categoryModel = new CategoryModel();
        $this->sizeStockModel = new ShoeSizeStockModel();
        $this->uploadService = new ImageUploadService();
    }

    /**
     * Admin Dashboard: Inventory Table
     */
    public function index()
    {
        $shoes = $this->shoeModel->getShoesWithCategory(null, 100);
        $categories = $this->categoryModel->findAll();

        return view('admin/shoes/index', [
            'title'      => 'Inventory Management | ApexStride Admin',
            'shoes'      => $shoes,
            'categories' => $categories,
            'stats'      => [
                'total_shoes'      => count($shoes),
                'total_categories' => count($categories),
                'featured_count'   => count(array_filter($shoes, fn($s) => !empty($s['is_featured']))),
                'low_stock_count'  => count(array_filter($shoes, fn($s) => ($s['stock_quantity'] ?? 0) <= 5)),
            ],
        ]);
    }

    /**
     * Show New Shoe Form
     */
    public function new()
    {
        return view('admin/shoes/create', [
            'title'      => 'Add New Shoe Product | ApexStride Admin',
            'categories' => $this->categoryModel->findAll(),
        ]);
    }

    public function stock()
    {
        $shoes = $this->shoeModel->getShoesWithCategory(null, 500);

        return view('admin/shoes/stock', [
            'title'           => 'Receive Shoe Stock | Admin',
            'shoes'           => $shoes,
            'sizeQuantities'  => $this->sizeStockModel->getAllQuantities(),
            'selectedShoeId'  => (int) $this->request->getGet('shoe_id'),
        ]);
    }

    /**
     * Store New Shoe
     */
    public function create()
    {
        $rules = [
            'name'           => 'required|min_length[3]|max_length[255]',
            'category_id'    => 'required|is_natural_no_zero',
            'price'          => 'required|decimal|greater_than[0]',
            'description'    => 'required|min_length[10]',
            'colors_available' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $sizeStockResult = $this->parseSizeStockRows();
        if (!$sizeStockResult['success']) {
            return redirect()->back()->withInput()->with('error', $sizeStockResult['error']);
        }

        // Handle Image Upload via Serverless Cloudinary/S3 Service
        $imageFile = $this->request->getFile('shoe_image');
        $imageUrl = $this->request->getPost('image_url_direct');
        $cloudinaryPublicId = null;

        if ($imageFile && $imageFile->isValid()) {
            $uploadResult = $this->uploadService->upload($imageFile, 'shoes');
            if (!$uploadResult['success']) {
                return redirect()->back()->withInput()->with('error', 'Cloud Image Upload Failed: ' . $uploadResult['error']);
            }
            $imageUrl = $uploadResult['url'];
            $cloudinaryPublicId = $uploadResult['public_id'] ?? null;
        }

        if (empty($imageUrl)) {
            return redirect()->back()->withInput()->with('error', 'Please upload a shoe image or supply a valid cloud CDN URL.');
        }

        $galleryResult = $this->collectGalleryImages();
        if (!$galleryResult['success']) {
            return redirect()->back()->withInput()->with('error', $galleryResult['error']);
        }

        // Generate URL-friendly slug
        $name = $this->request->getPost('name');
        $slug = url_title($name, '-', true) . '-' . substr(md5(uniqid()), 0, 5);

        $sizeStock = $sizeStockResult['entries'];
        $sizesString = implode(',', array_column($sizeStock, 'size'));
        $totalStock = array_sum(array_column($sizeStock, 'quantity'));

        $saveData = [
            'name'                 => $name,
            'slug'                 => $slug,
            'category_id'          => (int)$this->request->getPost('category_id'),
            'price'                => (float)$this->request->getPost('price'),
            'sale_price'           => $this->request->getPost('sale_price') ? (float)$this->request->getPost('sale_price') : null,
            'description'          => $this->request->getPost('description'),
            'image_url'            => $imageUrl,
            'gallery_images'       => json_encode($galleryResult['images']),
            'cloudinary_public_id' => $cloudinaryPublicId,
            'sizes_available'      => $sizesString,
            'colors_available'     => trim((string)$this->request->getPost('colors_available')) ?: null,
            'stock_quantity'       => $totalStock,
            'is_featured'          => $this->request->getPost('is_featured') ? 1 : 0,
        ];

        $database = db_connect();
        $database->transStart();
        $shoeId = $this->shoeModel->insert($saveData);
        if ($shoeId) {
            $this->sizeStockModel->addQuantities((int) $shoeId, $sizeStock);
        }
        $database->transComplete();

        if (!$shoeId || !$database->transStatus()) {
            return redirect()->back()->withInput()->with('errors', $this->shoeModel->errors());
        }

        return redirect()->to('/admin')->with('success', "Shoe '{$name}' has been created and image synced to cloud storage.");
    }

    /**
     * Show Edit Form
     */
    public function edit(int $id)
    {
        $shoe = $this->shoeModel->find($id);
        if (!$shoe) {
            throw PageNotFoundException::forPageNotFound("Shoe not found.");
        }

        return view('admin/shoes/edit', [
            'title'      => 'Edit Shoe: ' . $shoe['name'],
            'shoe'       => $shoe,
            'categories' => $this->categoryModel->findAll(),
        ]);
    }

    /**
     * Update Existing Shoe
     */
    public function update(int $id)
    {
        $shoe = $this->shoeModel->find($id);
        if (!$shoe) {
            throw PageNotFoundException::forPageNotFound("Shoe not found.");
        }

        $rules = [
            'name'           => 'required|min_length[3]|max_length[255]',
            'category_id'    => 'required|is_natural_no_zero',
            'price'          => 'required|decimal|greater_than[0]',
            'description'    => 'required|min_length[10]',
            'colors_available' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $sizeResult = $this->parseAvailableSizes(
            (string) ($this->request->getPost('sizes_available') ?? $shoe['sizes_available'])
        );
        if (!$sizeResult['success']) {
            return redirect()->back()->withInput()->with('error', $sizeResult['error']);
        }

        $currentSizes = array_values(array_filter(array_map('trim', explode(',', $shoe['sizes_available'] ?? ''))));
        $updatedSizes = $sizeResult['sizes'];
        $removedSizes = array_values(array_diff($currentSizes, $updatedSizes));
        $currentQuantities = $this->sizeStockModel->getQuantitiesForShoe($id);

        foreach ($removedSizes as $removedSize) {
            if (($currentQuantities[$removedSize] ?? 0) > 0) {
                return redirect()->back()->withInput()->with('error', "EU size {$removedSize} still has pairs in stock and cannot be removed.");
            }
        }

        $unallocatedStock = max(0, (int) $shoe['stock_quantity'] - array_sum($currentQuantities));
        if ($removedSizes !== [] && $unallocatedStock > 0) {
            return redirect()->back()->withInput()->with('error', 'Sizes cannot be removed while older stock is still unallocated by size.');
        }

        $newSizeEntries = [];
        foreach (array_diff($updatedSizes, $currentSizes) as $newSize) {
            $newSizeEntries[] = ['size' => $newSize, 'quantity' => 0];
        }

        $imageUrl = $shoe['image_url'];
        $cloudinaryPublicId = $shoe['cloudinary_public_id'];

        // Check if a replacement file was uploaded
        $imageFile = $this->request->getFile('shoe_image');
        if ($imageFile && $imageFile->isValid()) {
            $uploadResult = $this->uploadService->upload($imageFile, 'shoes');
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['url'];
                $cloudinaryPublicId = $uploadResult['public_id'] ?? null;
            }
        } elseif ($this->request->getPost('image_url_direct')) {
            $imageUrl = $this->request->getPost('image_url_direct');
        }

        $galleryResult = $this->collectGalleryImages($shoe['gallery_images'] ?? null);
        if (!$galleryResult['success']) {
            return redirect()->back()->withInput()->with('error', $galleryResult['error']);
        }

        $updateData = [
            'name'                 => $this->request->getPost('name'),
            'category_id'          => (int)$this->request->getPost('category_id'),
            'price'                => (float)$this->request->getPost('price'),
            'sale_price'           => $this->request->getPost('sale_price') ? (float)$this->request->getPost('sale_price') : null,
            'description'          => $this->request->getPost('description'),
            'image_url'            => $imageUrl,
            'gallery_images'       => json_encode($galleryResult['images']),
            'cloudinary_public_id' => $cloudinaryPublicId,
            'sizes_available'      => implode(',', $updatedSizes),
            'colors_available'     => trim((string)$this->request->getPost('colors_available')) ?: null,
            'is_featured'          => $this->request->getPost('is_featured') ? 1 : 0,
        ];

        $database = db_connect();
        $database->transStart();
        $this->sizeStockModel->removeSizes($id, $removedSizes);
        $this->sizeStockModel->addQuantities($id, $newSizeEntries);
        $updated = $this->shoeModel->update($id, $updateData);
        $database->transComplete();

        if (!$updated || !$database->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Shoe details could not be saved.');
        }

        return redirect()->to('/admin')->with('success', "Shoe '{$updateData['name']}' updated successfully.");
    }

    /**
     * Delete Shoe
     */
    public function delete(int $id)
    {
        $shoe = $this->shoeModel->find($id);
        if ($shoe) {
            $this->shoeModel->delete($id);
            return redirect()->to('/admin')->with('success', "Shoe '{$shoe['name']}' removed from catalog.");
        }

        return redirect()->to('/admin')->with('error', 'Shoe record not found.');
    }

    public function receiveStock()
    {
        $shoeId = (int) $this->request->getPost('shoe_id');
        $shoe = $this->shoeModel->find($shoeId);
        if (!$shoe) {
            return redirect()->back()->withInput()->with('error', 'Select a valid shoe before receiving stock.');
        }

        $stockResult = $this->parseSizeStockRows();
        if (!$stockResult['success']) {
            return redirect()->back()->withInput()->with('error', $stockResult['error']);
        }

        $entries = array_values(array_filter(
            $stockResult['entries'],
            static fn($entry) => $entry['quantity'] > 0
        ));
        if ($entries === []) {
            return redirect()->back()->withInput()->with('error', 'Enter at least one quantity greater than zero.');
        }

        $existingSizes = array_filter(array_map('trim', explode(',', $shoe['sizes_available'] ?? '')));
        $allSizes = array_values(array_unique(array_merge($existingSizes, array_column($entries, 'size'))));
        $received = array_sum(array_column($entries, 'quantity'));

        $database = db_connect();
        $database->transStart();
        $this->sizeStockModel->addQuantities($shoeId, $entries);
        $updated = $this->shoeModel->update($shoeId, [
            'name'            => $shoe['name'],
            'slug'            => $shoe['slug'],
            'category_id'     => $shoe['category_id'],
            'price'           => $shoe['price'],
            'sale_price'      => $shoe['sale_price'],
            'description'     => $shoe['description'],
            'image_url'       => $shoe['image_url'],
            'gallery_images'  => $shoe['gallery_images'],
            'cloudinary_public_id' => $shoe['cloudinary_public_id'],
            'sizes_available' => implode(',', $allSizes),
            'colors_available' => $shoe['colors_available'],
            'is_featured'     => $shoe['is_featured'],
            'stock_quantity'  => (int) $shoe['stock_quantity'] + $received,
        ]);
        $database->transComplete();

        if (!$updated || !$database->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Stock could not be saved. No inventory changes were applied.');
        }

        return redirect()->to('/admin/stock')->with('success', "Added {$received} pairs of {$shoe['name']} to size-specific stock.");
    }

    private function parseSizeStockRows(): array
    {
        $rows = $this->request->getPost('size_stock');
        if (!is_array($rows)) {
            return ['success' => false, 'error' => 'Add at least one shoe size and quantity.'];
        }

        $entries = [];
        $seenSizes = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $size = trim((string) ($row['size'] ?? ''));
            $quantityValue = trim((string) ($row['quantity'] ?? ''));
            if ($size === '' && ($quantityValue === '' || $quantityValue === '0')) {
                continue;
            }

            if (!preg_match('/^\d{1,2}(?:\.5)?$/', $size) || (float) $size < 1 || (float) $size > 50) {
                return ['success' => false, 'error' => 'Enter a valid EU shoe size from 1 to 50 in whole or half-size steps.'];
            }
            if ($quantityValue === '' || filter_var($quantityValue, FILTER_VALIDATE_INT) === false || (int) $quantityValue < 0) {
                return ['success' => false, 'error' => "Enter a whole number of 0 or more for size {$size}."];
            }

            $normalizedSize = $this->normalizeSize($size);
            if (isset($seenSizes[$normalizedSize])) {
                return ['success' => false, 'error' => "Shoe size {$size} appears more than once."];
            }

            $seenSizes[$normalizedSize] = true;
            $entries[] = ['size' => $normalizedSize, 'quantity' => (int) $quantityValue];
        }

        if ($entries === []) {
            return ['success' => false, 'error' => 'Add at least one shoe size and quantity.'];
        }

        return ['success' => true, 'entries' => $entries];
    }

    private function parseAvailableSizes(string $sizesInput): array
    {
        $sizes = array_values(array_filter(array_map('trim', explode(',', $sizesInput)), static fn($size) => $size !== ''));
        if ($sizes === []) {
            return ['success' => false, 'error' => 'Enter at least one available EU shoe size.'];
        }

        $normalizedSizes = [];
        foreach ($sizes as $size) {
            if (!preg_match('/^\d{1,2}(?:\.5)?$/', $size) || (float) $size < 1 || (float) $size > 50) {
                return ['success' => false, 'error' => 'Enter EU shoe sizes from 1 to 50 as whole or half sizes, separated by commas.'];
            }

            $normalizedSizes[] = $this->normalizeSize($size);
        }

        if (count($normalizedSizes) !== count(array_unique($normalizedSizes))) {
            return ['success' => false, 'error' => 'Each UK shoe size can only be listed once.'];
        }

        return ['success' => true, 'sizes' => $normalizedSizes];
    }

    private function normalizeSize(string $size): string
    {
        $numericSize = (float) $size;
        return $numericSize === floor($numericSize) ? (string) (int) $numericSize : number_format($numericSize, 1, '.', '');
    }

    private function collectGalleryImages(?string $existingImages = null): array
    {
        $urls = $this->request->getPost('gallery_image_urls');
        if ($urls === null && $existingImages !== null) {
            $images = json_decode($existingImages, true);
            $urls = is_array($images) ? implode("\n", $images) : '';
        }

        $images = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $urls))));
        foreach ($images as $url) {
            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
                return ['success' => false, 'error' => 'Every additional image must be a valid HTTP or HTTPS URL.'];
            }
        }

        $files = array_values(array_filter(
            $this->request->getFileMultiple('gallery_image_files') ?? [],
            static fn($file) => $file->getError() !== UPLOAD_ERR_NO_FILE
        ));

        if (count($images) + count($files) > 5) {
            return ['success' => false, 'error' => 'A maximum of 5 additional images is allowed.'];
        }

        foreach ($files as $file) {
            $uploadResult = $this->uploadService->upload($file, 'shoes/gallery');
            if (!$uploadResult['success']) {
                return ['success' => false, 'error' => 'Gallery image upload failed: ' . $uploadResult['error']];
            }
            $images[] = $uploadResult['url'];
        }

        return ['success' => true, 'images' => array_values(array_unique($images))];
    }
}
