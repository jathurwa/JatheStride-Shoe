<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-stone-500 mb-4">
        <a href="<?= base_url('admin/shoes') ?>" class="hover:text-stone-900">Inventory</a>
        <span>/</span>
        <span class="text-stone-900 font-semibold">New Shoe Product</span>
    </div>

    <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-xs">
        <div class="border-b border-stone-200 pb-4 mb-6">
            <h1 class="text-xl font-black text-stone-900 tracking-tight">Add New Shoe</h1>
            <p class="text-xs text-stone-500 mt-1">Set starting quantities by shoe size. Additional stock is received separately.</p>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1">Please correct the following errors:</div>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/shoes/create') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Shoe Name -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Shoe Name *
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= old('name') ?>" 
                       placeholder="e.g. Apex AeroStryke Runner V2" 
                       required 
                       class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900">
            </div>

            <!-- Category & Price Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Category *
                    </label>
                    <select id="category_id" 
                            name="category_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm bg-white focus:outline-hidden focus:border-stone-900">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat['id']) ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
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
                           min="1" 
                           value="<?= old('price', '149.99') ?>" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900">
                </div>
            </div>

            <!-- Sale Price -->
            <div class="max-w-sm">
                <div>
                    <label for="sale_price" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                        Sale Price (Optional)
                    </label>
                    <input type="number" 
                           id="sale_price" 
                           name="sale_price" 
                           step="0.01" 
                           value="<?= old('sale_price') ?>" 
                           placeholder="Leave empty if not on sale" 
                           class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900">
                </div>
            </div>

            <!-- Serverless Image Upload (Cloudinary / S3 intercept) -->
            <div class="border-2 border-dashed border-stone-300 rounded-2xl p-6 bg-stone-50 text-center">
                <i class="fa-solid fa-cloud-arrow-up text-3xl text-amber-600 mb-2"></i>
                <h4 class="text-xs font-bold text-stone-800 uppercase tracking-wider">Shoe Image Upload</h4>
                <p class="text-[11px] text-stone-500 mb-3">Uses Cloudinary when configured; otherwise saves locally in development.</p>
                
                <input type="file" 
                       id="shoe_image" 
                       name="shoe_image" 
                       accept="image/*"
                       class="text-xs text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 file:text-white hover:file:bg-amber-600 cursor-pointer">

                <div class="mt-3 text-xs text-stone-400">or provide direct image URL</div>
                <input type="url" 
                       name="image_url_direct" 
                       placeholder="https://images.unsplash.com/..." 
                       value="<?= old('image_url_direct') ?>"
                       class="w-full mt-2 px-3 py-2 text-xs rounded-lg border border-stone-300 bg-white">
            </div>

            <div>
                <label for="gallery_image_urls" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Additional Shoe Images (optional, up to 5)
                </label>
                <textarea id="gallery_image_urls" name="gallery_image_urls" rows="3" placeholder="Paste one HTTP/HTTPS image URL per line" class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm"><?= esc(old('gallery_image_urls', '')) ?></textarea>
                <label for="gallery_image_files" class="block text-[11px] font-semibold text-stone-600 mt-3 mb-1.5">Or upload additional images</label>
                <input type="file" id="gallery_image_files" name="gallery_image_files[]" accept="image/jpeg,image/png,image/webp,image/avif" multiple class="text-xs text-stone-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-stone-900 file:text-white">
                <p class="text-[11px] text-stone-400 mt-2">Use URLs, uploads, or both. The combined total cannot exceed 5.</p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Description & Specs *
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          required 
                          class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900"><?= old('description', 'Constructed with dual-density foam midsoles and a high-traction rubber lug outsole for maximum energy return.') ?></textarea>
            </div>

            <!-- Starting Stock by Size -->
            <div class="rounded-2xl border border-stone-200 overflow-hidden">
                <div class="flex items-center justify-between gap-3 bg-stone-50 border-b border-stone-200 px-4 py-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-stone-800">Starting Stock by Size</h2>
                        <p class="text-[11px] text-stone-500 mt-1">Enter the number of pairs for each size.</p>
                    </div>
                    <button type="button" id="addSizeStockRow" class="px-3 py-2 bg-stone-900 text-white text-xs font-bold rounded-lg hover:bg-stone-700">
                        <i class="fa-solid fa-plus mr-1" aria-hidden="true"></i>Add Size
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-[10px] uppercase text-stone-500 bg-white">
                            <tr><th class="px-4 py-2">EU Size</th><th class="px-4 py-2">Pairs</th><th class="px-4 py-2 w-12"></th></tr>
                        </thead>
                        <tbody id="sizeStockRows" class="divide-y divide-stone-100">
                            <?php $initialSizeRows = old('size_stock'); ?>
                            <?php if (!is_array($initialSizeRows) || $initialSizeRows === []): ?>
                                <?php $initialSizeRows = array_map(static fn($size) => ['size' => $size, 'quantity' => 0], ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48']); ?>
                            <?php endif; ?>
                            <?php foreach ($initialSizeRows as $index => $sizeRow): ?>
                                <tr class="size-stock-row">
                                    <td class="px-4 py-2"><input type="number" min="1" max="50" step="0.5" name="size_stock[<?= (int) $index ?>][size]" value="<?= esc($sizeRow['size'] ?? '') ?>" required class="w-full max-w-xs px-3 py-2 border border-stone-300 rounded-lg" aria-label="EU shoe size"></td>
                                    <td class="px-4 py-2"><input type="number" min="0" step="1" name="size_stock[<?= (int) $index ?>][quantity]" value="<?= esc($sizeRow['quantity'] ?? '0') ?>" required class="w-full max-w-xs px-3 py-2 border border-stone-300 rounded-lg" aria-label="Pairs for this size"></td>
                                    <td class="px-4 py-2"><button type="button" class="remove-size-stock-row text-stone-400 hover:text-rose-600" aria-label="Remove size row"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-stone-50 border-t border-stone-200">
                            <tr><td class="px-4 py-3 font-bold text-stone-700">Total starting pairs</td><td id="startingStockTotal" class="px-4 py-3 font-black text-stone-900">0</td><td></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Available Colors -->
            <div>
                <label for="colors_available" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Available Colors (optional, comma-separated)
                </label>
                <input type="text"
                       id="colors_available"
                       name="colors_available"
                       value="<?= old('colors_available') ?>"
                       placeholder="e.g. Black, White, Red"
                       class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-hidden focus:border-stone-900">
            </div>

            <!-- Featured Checkbox -->
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= old('is_featured') ? 'checked' : '' ?> class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-stone-300">
                <label for="is_featured" class="text-xs font-semibold text-stone-800">
                    Feature on Storefront Homepage Hero & Showcase
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-200">
                <a href="<?= base_url('admin/shoes') ?>" class="px-5 py-2.5 rounded-xl border border-stone-300 text-stone-700 text-xs font-bold hover:bg-stone-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-stone-900 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-md">
                    Upload & Save Shoe
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.getElementById('sizeStockRows');
    const total = document.getElementById('startingStockTotal');
    let nextIndex = rows.querySelectorAll('.size-stock-row').length;

    const updateTotal = () => {
        total.textContent = String([...rows.querySelectorAll('input[name$="[quantity]"]')]
            .reduce((sum, input) => sum + (Number(input.value) || 0), 0));
    };

    document.getElementById('addSizeStockRow').addEventListener('click', () => {
        const row = document.createElement('tr');
        row.className = 'size-stock-row';
        row.innerHTML = `<td class="px-4 py-2"><input type="number" min="1" max="50" step="0.5" name="size_stock[${nextIndex}][size]" required class="w-full max-w-xs px-3 py-2 border border-stone-300 rounded-lg" aria-label="EU shoe size"></td><td class="px-4 py-2"><input type="number" min="0" step="1" name="size_stock[${nextIndex}][quantity]" value="0" required class="w-full max-w-xs px-3 py-2 border border-stone-300 rounded-lg" aria-label="Pairs for this size"></td><td class="px-4 py-2"><button type="button" class="remove-size-stock-row text-stone-400 hover:text-rose-600" aria-label="Remove size row"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></td>`;
        rows.appendChild(row);
        nextIndex += 1;
    });

    rows.addEventListener('input', updateTotal);
    rows.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-size-stock-row');
        if (removeButton && rows.querySelectorAll('.size-stock-row').length > 1) {
            removeButton.closest('.size-stock-row').remove();
            updateTotal();
        }
    });
    updateTotal();
});
</script>
<?php $this->endSection() ?>
