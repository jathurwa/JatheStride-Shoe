<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="relative bg-stone-900 text-white overflow-hidden py-16 lg:py-24 border-b border-stone-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                <i class="fa-solid fa-fire-flame-curved"></i>
                <span>2025 Serverless Collection</span>
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                ENGINEERED FOR <br><span class="text-amber-500">EVERY STRIDE.</span>
            </h1>
            <p class="text-stone-400 text-base sm:text-lg max-w-lg leading-relaxed">
                Explore lightweight racing shoes, handcrafted leather formal wear, rugged outdoor boots, and iconic everyday sneakers. Powered by CodeIgniter 4 and Vercel edge delivery.
            </p>
            <div class="flex flex-wrap items-center gap-4 pt-2">
                <a href="#catalog" class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-sm transition-all shadow-lg hover:shadow-amber-500/25">
                    Browse All Shoes &darr;
                </a>
                <a href="<?= base_url('admin') ?>" class="px-6 py-3 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-semibold text-sm transition-all border border-stone-700">
                    Admin Inventory CRUD
                </a>
            </div>
        </div>

        <div class="relative flex justify-center">
            <div class="w-80 h-80 sm:w-96 sm:h-96 rounded-full bg-gradient-to-tr from-amber-500/20 to-orange-500/10 absolute -top-4 -left-4 blur-2xl"></div>
            <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80" 
                 alt="Featured Running Shoe" 
                 class="relative z-10 w-full max-w-md object-contain -rotate-12 hover:rotate-0 transition-transform duration-500 drop-shadow-2xl">
        </div>
    </div>
</section>

<!-- Category Filtering Bar -->
<section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-stone-200 pb-6">
        <div>
            <h2 class="text-2xl font-black tracking-tight text-stone-900">Footwear Catalog</h2>
            <p class="text-xs text-stone-500 mt-1">Filter by silhouette, performance type, and occasions.</p>
        </div>

        <!-- Filter Pills -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= base_url() ?>#catalog" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= empty($selectedCategory) ? 'bg-stone-950 text-white shadow-sm' : 'bg-stone-200/70 hover:bg-stone-300 text-stone-700' ?>">
                All Footwear (<?= count($shoes) ?>)
            </a>

            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= base_url('category/' . esc($cat['slug'])) ?>#catalog" 
                       class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($selectedCategory === $cat['slug']) ? 'bg-amber-600 text-white shadow-sm' : 'bg-stone-200/70 hover:bg-stone-300 text-stone-700' ?>">
                        <?= esc($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Shoes Product Grid -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <?php if (empty($shoes)): ?>
        <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center my-8">
            <i class="fa-solid fa-shoe-prints text-4xl text-stone-300 mb-4 block"></i>
            <h3 class="text-base font-bold text-stone-900">No Shoes Found</h3>
            <p class="text-xs text-stone-500 mt-1">No shoes currently exist in this category.</p>
            <a href="<?= base_url('admin/shoes/new') ?>" class="inline-block mt-4 text-xs font-semibold px-4 py-2 bg-stone-900 text-white rounded-lg">
                + Add One in Admin
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($shoes as $shoe): ?>
                <div class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:shadow-xl hover:border-stone-300 transition-all flex flex-col">
                    <!-- Image Card -->
                    <div class="relative bg-stone-100 p-6 flex items-center justify-center h-64 overflow-hidden">
                        <?php if (!empty($shoe['is_featured'])): ?>
                            <span class="absolute top-3 left-3 z-10 px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-amber-500 text-stone-950">
                                Featured
                            </span>
                        <?php endif; ?>

                        <span class="absolute top-3 right-3 z-10 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-white/80 backdrop-blur-xs text-stone-700 border border-stone-200">
                            <?= esc($shoe['category_name'] ?? 'Footwear') ?>
                        </span>

                        <a href="<?= base_url('product/' . ($shoe['slug'] ?: $shoe['id'])) ?>" class="w-full h-full flex items-center justify-center">
                            <img src="<?= esc($shoe['image_url']) ?>" 
                                 alt="<?= esc($shoe['name']) ?>" 
                                 class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300 drop-shadow-md"
                                 loading="lazy"
                                 onerror="this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80'">
                        </a>
                    </div>

                    <!-- Details -->
                    <div class="p-5 flex flex-col flex-grow justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">
                                <?= esc($shoe['category_name'] ?? 'Lifestyle') ?>
                            </span>
                            <h3 class="font-bold text-stone-900 text-base line-clamp-1 mt-0.5">
                                <a href="<?= base_url('product/' . ($shoe['slug'] ?: $shoe['id'])) ?>" class="hover:text-amber-600 transition-colors">
                                    <?= esc($shoe['name']) ?>
                                </a>
                            </h3>
                            <p class="text-xs text-stone-500 line-clamp-2 mt-1 leading-relaxed">
                                <?= esc($shoe['description']) ?>
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-stone-400 block font-medium">Price</span>
                                <span class="text-lg font-black text-stone-950">
                                    KSH <?= number_format((float)$shoe['price'], 2) ?>
                                </span>
                            </div>

                            <a href="<?= base_url('product/' . ($shoe['slug'] ?: $shoe['id'])) ?>" 
                               class="px-3.5 py-2 rounded-xl bg-stone-900 hover:bg-amber-600 text-white text-xs font-semibold transition-colors flex items-center gap-1.5">
                                <span>View</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>
