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
        $this->shoeModel      = new ShoeModel();
        $this->categoryModel  = new CategoryModel();
        $this->sizeStockModel = new ShoeSizeStockModel();
        $this->uploadService  = new ImageUploadService();
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
            'name'             => 'required|min_length[3]|max_length[255]',
            'category_id'      => 'required|is_natural_no_zero',
            'price'            => 'required|numeric|greater_than[0]',
            'sale_price'       => 'permit_empty|numeric|greater_than[0]',
            'description'      => 'required|min_length[10]',
            'colors_available' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $salePrice = trim((string) $this->request->getPost('sale_price'));
        if ($salePrice !== '' && (float) $salePrice >= (float) $this->request->getPost('price')) {
            return redirect()->back()->withInput()->with('error', 'Sale price must be lower than the regular price.');
        }

        $sizeStockResult = $this->parseSizeStockRows();
        if (!$sizeStockResult['success']) {
            return redirect()->back()->withInput()->with('error', $sizeStockResult['error']);
        }

        // Handle Image: Supports BOTH Machine File Upload AND Direct Image Link URL
        $imageFile = $this->request->getFile('shoe_image');
        $imageUrlDirect = trim((string)$this->request->getPost('image_url_direct'));
        $imageUrl = null;
        $cloudinaryPublicId = null;

        if (!empty($imageUrlDirect)) {
            if (!$this->isHttpUrl($imageUrlDirect)) {
                return redirect()->back()->withInput()->with('error', 'Enter a valid HTTP or HTTPS image URL.');
            }
            $imageUrl = $imageUrlDirect;
        }

        if ($imageFile && $imageFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$imageFile->isValid() || $imageFile->hasMoved()) {
                return redirect()->back()->withInput()->with('error', $imageFile->getErrorString());
            }
            $uploadResult = $this->uploadService->upload($imageFile, 'shoes');
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['url'];
                $cloudinaryPublicId = $uploadResult['public_id'] ?? null;
            } else {
                return redirect()->back()->withInput()->with('error', $uploadResult['error']);
            }
        }

        if (empty($imageUrl)) {
            return redirect()->back()->withInput()->with('error', 'Please upload a shoe image from your PC OR provide a direct image link URL.');
        }

        $galleryResult = $this->collectGalleryImages();
        if (!$galleryResult['success']) {
            return redirect()->back()->withInput()->with('error', $galleryResult['error']);
        }

        $name = $this->request->getPost('name');
        $slug = url_title($name, '-', true) . '-' . substr(md5(uniqid()), 0, 5);

        $formStock = (int) $this->request->getPost('stock_quantity');
        $sizeStock = $sizeStockResult['entries'] ?? [];
        $totalStock = !empty($sizeStock) ? array_sum(array_column($sizeStock, 'quantity')) : 0;

        if ($totalStock === 0 && $formStock > 0) {
            $totalStock = $formStock;
            $count = count($sizeStock);
            if ($count > 0) {
                $perSize = (int) floor($formStock / $count);
                $remainder = $formStock % $count;
                foreach ($sizeStock as $idx => &$entry) {
                    $entry['quantity'] = $perSize + ($idx === 0 ? $remainder : 0);
                }
                unset($entry);
            }
        }

        $sizesString = !empty($sizeStock) 
            ? implode(',', array_column($sizeStock, 'size')) 
            : (string)($this->request->getPost('sizes') ?: $this->request->getPost('sizes_available') ?: '7,8,8.5,9,9.5,10,10.5,11,12');

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
        if ($shoeId && !empty($sizeStock)) {
            $this->sizeStockModel->addQuantities((int) $shoeId, $sizeStock);
        }
        $database->transComplete();

        if (!$shoeId || !$database->transStatus()) {
            $modelErrors = $this->shoeModel->errors();
            $dbError = $database->error();
            $msg = !empty($modelErrors) ? implode(', ', $modelErrors) : ($dbError['message'] ?? 'Database transaction error');
            return redirect()->back()->withInput()->with('error', 'Shoe could not be saved: ' . $msg);
        }

        return redirect()->to('/admin')->with('success', "Shoe '{$name}' created with {$totalStock} pairs in stock.");
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
            'name'             => 'required|min_length[3]|max_length[255]',
            'category_id'      => 'required|is_natural_no_zero',
            'price'            => 'required|numeric|greater_than[0]',
            'sale_price'       => 'permit_empty|numeric|greater_than[0]',
            'description'      => 'required|min_length[10]',
            'colors_available' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $salePrice = trim((string) $this->request->getPost('sale_price'));
        if ($salePrice !== '' && (float) $salePrice >= (float) $this->request->getPost('price')) {
            return redirect()->back()->withInput()->with('error', 'Sale price must be lower than the regular price.');
        }

        $sizeResult = $this->parseAvailableSizes(
            (string) ($this->request->getPost('sizes_available') ?? $this->request->getPost('sizes') ?? $shoe['sizes_available'])
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

        $newSizeEntries = [];
        foreach (array_diff($updatedSizes, $currentSizes) as $newSize) {
            $newSizeEntries[] = ['size' => $newSize, 'quantity' => 0];
        }

        $imageUrl = $shoe['image_url'];
        $cloudinaryPublicId = $shoe['cloudinary_public_id'];
        $imageUrlDirect = trim((string)$this->request->getPost('image_url_direct'));

        if (!empty($imageUrlDirect)) {
            if (!$this->isHttpUrl($imageUrlDirect)) {
                return redirect()->back()->withInput()->with('error', 'Enter a valid HTTP or HTTPS image URL.');
            }
            $imageUrl = $imageUrlDirect;
        }

        $imageFile = $this->request->getFile('shoe_image');
        if ($imageFile && $imageFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$imageFile->isValid() || $imageFile->hasMoved()) {
                return redirect()->back()->withInput()->with('error', $imageFile->getErrorString());
            }
            $uploadResult = $this->uploadService->upload($imageFile, 'shoes');
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['url'];
                $cloudinaryPublicId = $uploadResult['public_id'] ?? null;
            } else {
                return redirect()->back()->withInput()->with('error', $uploadResult['error']);
            }
        }

        $galleryResult = $this->collectGalleryImages($shoe['gallery_images'] ?? null);
        if (!$galleryResult['success']) {
            return redirect()->back()->withInput()->with('error', $galleryResult['error']);
        }

        $stockQty = $this->request->getPost('stock_quantity');
        $finalStock = ($stockQty !== null && $stockQty !== '') ? (int)$stockQty : (int)$shoe['stock_quantity'];

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
            'stock_quantity'       => $finalStock,
            'is_featured'          => $this->request->getPost('is_featured') ? 1 : 0,
        ];

        $database = db_connect();
        $database->transStart();
        if (!empty($removedSizes)) {
            $this->sizeStockModel->removeSizes($id, $removedSizes);
        }
        if (!empty($newSizeEntries)) {
            $this->sizeStockModel->addQuantities($id, $newSizeEntries);
        }
        $updated = $this->shoeModel->update($id, $updateData);
        $database->transComplete();

        if (!$updated || !$database->transStatus()) {
            $modelErrors = $this->shoeModel->errors();
            $dbError = $database->error();
            $msg = !empty($modelErrors) ? implode(', ', $modelErrors) : ($dbError['message'] ?? 'Database error on update');
            return redirect()->back()->withInput()->with('error', 'Shoe details could not be saved: ' . $msg);
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

        $newTotalStock = (int) $shoe['stock_quantity'] + $received;
        $updated = $this->shoeModel->builder()->where('id', $shoeId)->update([
            'sizes_available' => implode(',', $allSizes),
            'stock_quantity'  => $newTotalStock,
        ]);
        $database->transComplete();

        if (!$updated || !$database->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Stock could not be saved. No inventory changes were applied.');
        }

        return redirect()->to('/admin/stock')->with('success', "Added {$received} pairs of {$shoe['name']}. New total stock: {$newTotalStock}.");
    }

    private function parseSizeStockRows(): array
    {
        $rows = $this->request->getPost('size_stock');
        
        if (!is_array($rows)) {
            $sizesInput = (string)($this->request->getPost('sizes') ?: $this->request->getPost('sizes_available') ?: '');
            $stockQty = (int)($this->request->getPost('stock_quantity') ?? 0);

            $sizesList = array_values(array_filter(array_map('trim', explode(',', $sizesInput))));
            if (empty($sizesList)) {
                $sizesList = ['39', '40', '41', '42', '43', '44', '45'];
            }

            $entries = [];
            $count = count($sizesList);
            $perSize = $count > 0 ? (int)floor($stockQty / $count) : 0;
            $remainder = $count > 0 ? ($stockQty % $count) : 0;
            foreach ($sizesList as $idx => $s) {
                $qty = $perSize + ($idx === 0 ? $remainder : 0);
                $entries[] = ['size' => $s, 'quantity' => $qty];
            }
            return ['success' => true, 'entries' => $entries];
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
            $formStock = (int) $this->request->getPost('stock_quantity');
            $sizesInput = (string)($this->request->getPost('sizes') ?: $this->request->getPost('sizes_available') ?: '');
            $sizesList = array_values(array_filter(array_map('trim', explode(',', $sizesInput))));
            if (empty($sizesList)) {
                $sizesList = ['40', '41', '42', '43', '44'];
            }

            $count = count($sizesList);
            $perSize = $count > 0 ? (int)floor($formStock / $count) : 0;
            $remainder = $count > 0 ? ($formStock % $count) : 0;
            foreach ($sizesList as $idx => $s) {
                $entries[] = ['size' => $s, 'quantity' => $perSize + ($idx === 0 ? $remainder : 0)];
            }
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

    private function isHttpUrl(string $url): bool
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array($scheme, ['http', 'https'], true);
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
            if ($uploadResult['success']) {
                $images[] = $uploadResult['url'];
            } else {
                if (empty($images)) {
                    return ['success' => false, 'error' => 'Gallery image upload failed: ' . $uploadResult['error']];
                }
            }
        }

        return ['success' => true, 'images' => array_values(array_unique($images))];
    }
}
