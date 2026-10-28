<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 text-xs text-stone-500 mb-4">
        <a href="<?= base_url('admin/shoes') ?>" class="hover:text-stone-900">Inventory</a>
        <span>/</span>
        <span class="text-stone-900 font-semibold">Edit Shoe #<?= esc($shoe['id']) ?></span>
    </div>

    <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-xs">
        <div class="border-b border-stone-200 pb-4 mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-black text-stone-900 tracking-tight">Edit Shoe: <?= esc($shoe['name']) ?></h1>
                <p class="text-xs text-stone-500 mt-1">Update product details and media. Manage sizes and stock separately.</p>
            </div>
            <img src="<?= esc($shoe['image_url']) ?>" alt="Thumbnail" class="w-12 h-12 rounded-xl object-contain bg-stone-100 border border-stone-200 p-1">
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/shoes/update/' . $shoe['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Shoe Name *
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= old('name', $shoe['name']) ?>" 
                       required 
                       class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Category *
                    </label>
                    <select id="category_id" 
                            name="category_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm bg-white focus:outline-hidden focus:border-stone-900">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat['id']) ?>" <?= (old('category_id', $shoe['category_id']) == $cat['id']) ? 'selected' : '' ?>>
                                <?= esc($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="price" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Price (KSH) *
                    </label>
                    <input type="number" 
                           id="price" 
                           name="price" 
                           step="0.01" 
                           value="<?= old('price', $shoe['price']) ?>" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">Current Total Stock</span>
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50">
                        <span class="text-sm font-bold text-stone-900"><?= esc($shoe['stock_quantity']) ?> pairs</span>
                        <a href="<?= base_url('admin/stock?shoe_id=' . $shoe['id']) ?>" class="text-xs font-bold text-emerald-700 hover:underline">Receive stock</a>
                    </div>
                </div>

                <div>
                    <label for="sale_price" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Sale Price
                    </label>
                    <input type="number" 
                           id="sale_price" 
                           name="sale_price" 
                           step="0.01" 
                           value="<?= old('sale_price', $shoe['sale_price']) ?>" 
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900">
                </div>
            </div>

            <!-- Image Replacement Field -->
            <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200">
                <div class="flex items-center gap-4 mb-3">
                    <img src="<?= esc($shoe['image_url']) ?>" alt="Current" class="w-16 h-16 object-contain bg-white rounded-xl border p-1">
                    <div>
                        <span class="text-xs font-bold text-stone-800">Current Product Image</span>
                        <div class="text-[10px] text-stone-500 font-mono truncate max-w-sm"><?= esc($shoe['image_url']) ?></div>
                    </div>
                </div>
                <label class="block text-[11px] font-bold uppercase text-stone-600 mb-1">Replace Image</label>
                <input type="file" name="shoe_image" accept="image/*" class="text-xs text-stone-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-stone-900 file:text-white">
                <input type="url" name="image_url_direct" placeholder="Or update direct CDN URL" value="<?= old('image_url_direct', $shoe['image_url']) ?>" class="w-full mt-2 px-3 py-2 text-xs rounded-lg border border-stone-300 bg-white">
            </div>

            <?php $galleryImages = json_decode($shoe['gallery_images'] ?? '[]', true) ?: []; ?>
            <div>
                <label for="gallery_image_urls" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Additional Shoe Images (optional, up to 5)
                </label>
                <textarea id="gallery_image_urls" name="gallery_image_urls" rows="3" placeholder="Paste one HTTP/HTTPS image URL per line" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm"><?= esc(old('gallery_image_urls', implode("\n", $galleryImages))) ?></textarea>
                <label for="gallery_image_files" class="block text-[11px] font-semibold text-stone-600 mt-3 mb-1.5">Upload more images</label>
                <input type="file" id="gallery_image_files" name="gallery_image_files[]" accept="image/jpeg,image/png,image/webp,image/avif" multiple class="text-xs text-stone-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-stone-900 file:text-white">
                <p class="text-[11px] text-stone-400 mt-2">Edit the URL list to replace or remove saved images. The combined total cannot exceed 5.</p>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Description *
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          required 
                          class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900"><?= old('description', $shoe['description']) ?></textarea>
            </div>

            <div>
                <div class="flex items-center justify-between gap-3 mb-1.5">
                    <label for="sizes_available" class="block text-xs font-bold uppercase tracking-wider text-stone-700">Available EU Sizes</label>
                    <a href="<?= base_url('admin/stock?shoe_id=' . $shoe['id']) ?>" class="shrink-0 text-xs font-bold text-emerald-700 hover:underline">Receive stock</a>
                </div>
                <input type="text" id="sizes_available" name="sizes_available" inputmode="decimal" value="<?= esc(old('sizes_available', $shoe['sizes_available'])) ?>" placeholder="e.g. 36,37,38,39,40" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm">
                <p class="mt-2 text-[11px] text-stone-500">Use EU whole or half sizes separated by commas. A size with stock cannot be removed.</p>
            </div>

            <div>
                <label for="colors_available" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Available Colors (optional, comma-separated)
                </label>
                <input type="text"
                       id="colors_available"
                       name="colors_available"
                       value="<?= old('colors_available', $shoe['colors_available'] ?? '') ?>"
                       placeholder="e.g. Black, White, Red"
                       class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= (old('is_featured', $shoe['is_featured'])) ? 'checked' : '' ?> class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-stone-300">
                <label for="is_featured" class="text-xs font-semibold text-stone-800">
                    Feature on Storefront Homepage
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-200">
                <a href="<?= base_url('admin/shoes') ?>" class="px-5 py-2.5 rounded-xl border border-stone-300 text-stone-700 text-xs font-bold hover:bg-stone-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-stone-900 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-md">
                    Update Shoe Details
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
