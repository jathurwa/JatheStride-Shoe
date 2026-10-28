<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <!-- Category Pills -->
    <div class="flex flex-wrap items-center gap-2 border-b border-stone-200 pb-4">
        <a href="<?= base_url() ?>" class="px-4 py-2 rounded-xl text-xs font-bold <?= empty($currentCategory) ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' ?>">
            All Footwear
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('category/' . esc($cat['slug'])) ?>" 
               class="px-4 py-2 rounded-xl text-xs font-bold <?= (!empty($currentCategory) && $currentCategory['id'] == $cat['id']) ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' ?>">
                <?= esc($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (!empty($shoes)): ?>
            <?php foreach ($shoes as $shoe): ?>
                <div class="group bg-white rounded-3xl border border-stone-200 overflow-hidden hover:shadow-xl transition-all flex flex-col">
                    <div class="bg-stone-100 p-6 flex items-center justify-center h-64 relative">
                        <?php if ($shoe['is_featured']): ?>
                            <span class="absolute top-3 left-3 px-2 py-0.5 rounded bg-amber-500 text-stone-950 text-[10px] font-black uppercase tracking-wider">
                                Featured
                            </span>
                        <?php endif; ?>
                        <img src="<?= esc($shoe['image_url']) ?>" alt="<?= esc($shoe['name']) ?>" class="max-h-full object-contain group-hover:scale-105 transition-transform duration-300" />
                    </div>
                    <div class="p-5 flex flex-col flex-grow justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-amber-600 uppercase"><?= esc($shoe['category_name'] ?? 'Footwear') ?></span>
                            <h3 class="font-bold text-stone-900 text-sm mt-1"><?= esc($shoe['name']) ?></h3>
                            <p class="text-xs text-stone-500 line-clamp-2 mt-1"><?= esc($shoe['description']) ?></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                            <span class="text-base font-black text-stone-950">KSH <?= number_format($shoe['price'], 2) ?></span>
                            <a href="<?= base_url('shoes/' . esc($shoe['slug'])) ?>" class="px-3 py-1.5 rounded-xl bg-stone-900 hover:bg-amber-600 text-white text-xs font-bold transition-colors">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-4 p-12 text-center text-stone-500 bg-white rounded-2xl border border-stone-200">
                No shoes found in this collection.
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>