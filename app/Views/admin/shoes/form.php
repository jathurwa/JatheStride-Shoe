<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm">
        <h2 class="text-xl font-black text-stone-900 mb-4"><?= empty($shoe) ? 'Add New Shoe' : 'Edit Shoe #' . $shoe['id'] ?></h2>

        <form action="<?= empty($shoe) ? base_url('admin/shoes/store') : base_url('admin/shoes/update/' . $shoe['id']) ?>" method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Shoe Name *</label>
                <input type="text" name="name" value="<?= esc($shoe['name'] ?? '') ?>" required class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Category *</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (!empty($shoe) && $shoe['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= esc($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Price (KSH) *</label>
                    <input type="number" step="0.01" name="price" value="<?= esc($shoe['price'] ?? '149.99') ?>" required class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Cloudinary File Upload</label>
                <input type="file" name="shoe_image" accept="image/*" class="w-full text-xs" />
                <p class="text-[11px] text-stone-400 mt-1">Uploaded image streams straight to Cloudinary API without saving to read-only container disk.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Direct Image URL (Alternative)</label>
                <input type="url" name="image_url_direct" value="<?= esc($shoe['image_url'] ?? '') ?>" placeholder="https://..." class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs font-mono" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-700 mb-1">Description *</label>
                <textarea name="description" rows="3" required class="w-full px-3 py-2 border border-stone-300 rounded-xl text-xs"><?= esc($shoe['description'] ?? '') ?></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="<?= base_url('admin/shoes') ?>" class="px-4 py-2 border border-stone-300 rounded-xl text-xs font-bold text-stone-600">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold rounded-xl text-xs">
                    Save Shoe
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>