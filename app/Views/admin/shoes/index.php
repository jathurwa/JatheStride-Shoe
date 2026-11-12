<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Stats Overview -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
        <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Total Catalog</span>
        <div class="text-2xl font-black text-stone-900 mt-1"><?= esc($stats['total_shoes'] ?? 0) ?></div>
        <span class="text-[11px] text-stone-500">Active shoes</span>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
        <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Categories</span>
        <div class="text-2xl font-black text-stone-900 mt-1"><?= esc($stats['total_categories'] ?? 0) ?></div>
        <span class="text-[11px] text-stone-500">Live classifications</span>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
        <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Featured Releases</span>
        <div class="text-2xl font-black text-amber-600 mt-1"><?= esc($stats['featured_count'] ?? 0) ?></div>
        <span class="text-[11px] text-stone-500">Hero storefront shoes</span>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
        <span class="text-xs font-semibold uppercase tracking-wider text-stone-400">Low Stock Alert</span>
        <div class="text-2xl font-black text-rose-600 mt-1"><?= esc($stats['low_stock_count'] ?? 0) ?></div>
        <span class="text-[11px] text-stone-500">&le; 5 units remaining</span>
    </div>
</div>

<!-- Header & Action Button -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-stone-900 tracking-tight">Shoe Inventory</h1>
        <p class="text-xs text-stone-500 mt-0.5">Manage product details, cloud images, prices, and adjust size stock.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= base_url('admin/stock') ?>" class="px-4 py-2.5 rounded-xl border border-stone-300 hover:bg-stone-100 text-stone-800 text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-2">
            <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
            <span>Adjust / Manage Stock</span>
        </a>
        <a href="<?= base_url('admin/shoes/new') ?>" class="px-4 py-2.5 rounded-xl bg-stone-900 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Add New Shoe</span>
        </a>
    </div>
</div>

<!-- Table Card -->
<div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-xs">
    <?php if (empty($shoes)): ?>
        <div class="p-12 text-center">
            <i class="fa-solid fa-box-open text-4xl text-stone-300 mb-3 block"></i>
            <h3 class="text-sm font-bold text-stone-900">No Shoes in Inventory</h3>
            <p class="text-xs text-stone-500 mt-1">Get started by creating your first footwear product.</p>
            <a href="<?= base_url('admin/shoes/new') ?>" class="inline-block mt-4 text-xs font-bold px-4 py-2 bg-stone-900 text-white rounded-lg">
                + Create Shoe
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Shoe</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Price</th>
                        <th class="py-3 px-4">Stock</th>
                        <th class="py-3 px-4">Storage Provider</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach ($shoes as $shoe): ?>
                        <tr class="hover:bg-stone-50/80 transition-colors">
                            <td class="py-3 px-4 flex items-center gap-3">
                                <img src="<?= esc($shoe['image_url']) ?>" 
                                     alt="<?= esc($shoe['name']) ?>" 
                                     class="w-12 h-12 object-contain rounded-lg bg-stone-100 p-1 border border-stone-200"
                                     onerror="this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=120&q=80'">
                                <div>
                                    <div class="font-bold text-stone-900 text-sm flex items-center gap-1.5">
                                        <span><?= esc($shoe['name']) ?></span>
                                        <?php if (!empty($shoe['is_featured'])): ?>
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-[11px] text-stone-400">ID: #<?= esc($shoe['id']) ?></span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-stone-100 text-stone-700 border border-stone-200">
                                    <?= esc($shoe['category_name'] ?? 'Footwear') ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 font-black text-stone-900">
                                $<?= number_format((float)$shoe['price'], 2) ?>
                            </td>
                            <td class="py-3 px-4">
                                <a href="<?= base_url('admin/stock?shoe_id=' . $shoe['id']) ?>" class="group inline-flex items-center gap-1.5 hover:underline" title="Manage / Adjust stock by size">
                                    <?php if (($shoe['stock_quantity'] ?? 0) <= 5): ?>
                                        <span class="text-rose-600 font-bold"><?= esc($shoe['stock_quantity']) ?> left</span>
                                    <?php else: ?>
                                        <span class="text-stone-700 font-medium"><?= esc($shoe['stock_quantity']) ?></span>
                                    <?php endif; ?>
                                    <i class="fa-solid fa-boxes-stacked text-[10px] text-stone-300 group-hover:text-amber-600 transition-colors"></i>
                                </a>
                            </td>
                            <td class="py-3 px-4">
                                <?php if (!empty($shoe['cloudinary_public_id'])): ?>
                                    <span class="text-[10px] text-emerald-700 font-medium bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fa-solid fa-cloud text-[8px]"></i> Cloudinary
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] text-sky-700 font-medium bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fa-solid fa-link text-[8px]"></i> Cloud CDN
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="<?= base_url('admin/stock?shoe_id=' . $shoe['id']) ?>" class="p-1.5 rounded-md hover:bg-amber-100 text-stone-500 hover:text-amber-700" title="Manage Size Stock (+ / - / =)">
                                        <i class="fa-solid fa-boxes-stacked text-xs"></i>
                                    </a>
                                    <a href="<?= base_url('product/' . ($shoe['slug'] ?: $shoe['id'])) ?>" target="_blank" class="p-1.5 rounded-md hover:bg-stone-200 text-stone-500 hover:text-stone-900" title="View in Store">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a href="<?= base_url('admin/shoes/edit/' . $shoe['id']) ?>" class="p-1.5 rounded-md hover:bg-stone-200 text-amber-600 hover:text-amber-700" title="Edit Shoe">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    <form action="<?= base_url('admin/shoes/delete/' . $shoe['id']) ?>" method="POST" class="inline" onsubmit="return confirm('Delete this shoe from catalog?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-1.5 rounded-md hover:bg-rose-100 text-rose-600 hover:text-rose-700" title="Delete Shoe">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
