<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-stone-900">Shoe Inventory Management</h1>
            <p class="text-xs text-stone-500">Manage products, pricing, stock and Cloudinary asset links</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= base_url('admin/stock') ?>" class="px-4 py-2 border border-stone-300 bg-white hover:bg-stone-50 text-stone-800 text-xs font-bold rounded-xl">
                <i class="fa-solid fa-boxes-stacked mr-1.5" aria-hidden="true"></i>Receive Stock
            </a>
            <a href="<?= base_url('admin/shoes/create') ?>" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-stone-950 text-xs font-bold rounded-xl shadow-sm">
                + Add New Shoe
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-stone-50 border-b border-stone-200 text-stone-500 uppercase font-bold text-[10px]">
                <tr>
                    <th class="py-3 px-4">Shoe Name</th>
                    <th class="py-3 px-4">Category</th>
                    <th class="py-3 px-4">Price</th>
                    <th class="py-3 px-4">Stock</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                <?php foreach ($shoes as $s): ?>
                    <tr class="hover:bg-stone-50">
                        <td class="py-3 px-4 font-bold flex items-center gap-3">
                            <img src="<?= esc($s['image_url']) ?>" class="w-10 h-10 object-contain bg-stone-100 rounded-lg p-1" />
                            <span><?= esc($s['name']) ?></span>
                        </td>
                        <td class="py-3 px-4"><?= esc($s['category_name'] ?? 'General') ?></td>
                        <td class="py-3 px-4 font-black">KSH <?= number_format($s['price'], 2) ?></td>
                        <td class="py-3 px-4"><?= esc($s['stock_quantity']) ?> units</td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="<?= base_url('admin/shoes/edit/' . $s['id']) ?>" class="text-amber-700 font-bold hover:underline">Edit</a>
                            <a href="<?= base_url('admin/stock?shoe_id=' . $s['id']) ?>" class="text-emerald-700 font-bold hover:underline">Add Stock</a>
                            <a href="<?= base_url('admin/shoes/delete/' . $s['id']) ?>" onclick="return confirm('Delete this shoe?')" class="text-rose-600 font-bold hover:underline">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>