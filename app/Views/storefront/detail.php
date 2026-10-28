<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$galleryImages = json_decode($shoe['gallery_images'] ?? '[]', true);
$gallerySlides = array_values(array_unique(array_merge(
    [$shoe['image_url']],
    is_array($galleryImages) ? $galleryImages : []
)));
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-stone-500 mb-8">
        <a href="<?= base_url() ?>" class="hover:text-stone-900">Home</a>
        <span>/</span>
        <a href="<?= base_url('category/' . esc($shoe['category_slug'] ?? 'all')) ?>" class="hover:text-stone-900">
            <?= esc($shoe['category_name'] ?? 'Footwear') ?>
        </a>
        <span>/</span>
        <span class="text-stone-900 font-semibold truncate"><?= esc($shoe['name']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
        <!-- Image Gallery Display -->
        <div class="space-y-4">
            <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-12 flex items-center justify-center min-h-[420px] relative overflow-hidden shadow-xs">
                <?php if (!empty($shoe['is_featured'])): ?>
                    <span class="absolute top-6 left-6 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-500 text-stone-950">
                        Featured Release
                    </span>
                <?php endif; ?>

                <img id="mainShoeImage" 
                     src="<?= esc($shoe['image_url']) ?>" 
                     alt="<?= esc($shoe['name']) ?>" 
                     class="max-w-full max-h-[380px] object-contain drop-shadow-xl hover:scale-105 transition-transform duration-300">
            </div>

            <?php if (count($gallerySlides) > 1): ?>
                <div class="flex items-center justify-between gap-3">
                    <button type="button" id="galleryPrevious" aria-label="Previous product image" class="w-10 h-10 shrink-0 rounded-full border border-stone-300 bg-white text-stone-800 hover:bg-stone-100">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <div class="flex min-w-0 items-center justify-center gap-2 overflow-x-auto py-1" aria-label="Product images">
                        <?php foreach ($gallerySlides as $index => $slide): ?>
                            <button type="button" data-gallery-index="<?= $index ?>" aria-label="Show product image <?= $index + 1 ?>" class="gallery-thumb h-14 w-14 shrink-0 rounded-lg border-2 <?= $index === 0 ? 'border-amber-500' : 'border-stone-200' ?> bg-white p-1">
                                <img src="<?= esc($slide) ?>" alt="" class="h-full w-full object-contain">
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="galleryNext" aria-label="Next product image" class="w-10 h-10 shrink-0 rounded-full border border-stone-300 bg-white text-stone-800 hover:bg-stone-100">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Serverless Cloud Storage Metadata Badge -->
            <div class="bg-stone-100 rounded-xl p-3 border border-stone-200 flex items-center justify-between text-xs text-stone-600">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-cloud text-amber-600"></i>
                    <span>Image Provider: <strong><?= !empty($shoe['cloudinary_public_id']) ? 'Cloudinary CDN (Signed)' : 'Cloud Storage CDN' ?></strong></span>
                </span>
                <span class="font-mono text-[10px] text-stone-500">ID: <?= esc($shoe['id']) ?></span>
            </div>
        </div>

        <!-- Product Information & Purchasing -->
        <div class="space-y-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-amber-600">
                    <?= esc($shoe['category_name'] ?? 'Signature Series') ?>
                </span>
                <h1 class="text-3xl sm:text-4xl font-black text-stone-950 tracking-tight mt-1">
                    <?= esc($shoe['name']) ?>
                </h1>
            </div>

            <!-- Price & Stock -->
            <div class="flex items-baseline gap-4">
                <span class="text-3xl font-black text-stone-950">
                    KSH <?= number_format((float)$shoe['price'], 2) ?>
                </span>
                <?php if (!empty($shoe['sale_price'])): ?>
                    <span class="text-lg text-stone-400 line-through">
                        KSH <?= number_format((float)$shoe['sale_price'], 2) ?>
                    </span>
                <?php endif; ?>

                <div class="ml-auto">
                    <?php if (($shoe['stock_quantity'] ?? 0) > 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>In Stock (<?= esc($shoe['stock_quantity']) ?> pairs)</span>
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Out of Stock</span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="border-t border-stone-200 pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-2">Description & Build</h3>
                <p class="text-sm text-stone-600 leading-relaxed">
                    <?= nl2br(esc($shoe['description'])) ?>
                </p>
            </div>

            <!-- Size Selector -->
            <?php
                $sizes = !empty($shoe['sizes_available']) ? explode(',', $shoe['sizes_available']) : ['8', '8.5', '9', '9.5', '10', '10.5', '11', '12'];
                $sizeStock = $sizeStock ?? [];
                $firstAvailableSize = null;
                foreach ($sizes as $size) {
                    $normalizedSize = trim($size);
                    if (!array_key_exists($normalizedSize, $sizeStock) || $sizeStock[$normalizedSize] > 0) {
                        $firstAvailableSize = $normalizedSize;
                        break;
                    }
                }
            ?>
            <div class="border-t border-stone-200 pt-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-700">Select EU Size</span>
                    <span class="text-xs text-amber-600 font-semibold cursor-pointer hover:underline">Size Guide<a href="https://www.grivetoutdoors.com/pages/shoe-size-chart?srsltid=AU7gw4U9UU6lVDLBa1onkWqQFVwEk9FVsNgtu6PkGaHvesHltZgBuxuS" target="_blank" class="text-amber-600 hover:text-amber-400">View Size Chart</a></span>
                </div>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                        <?php foreach ($sizes as $size): ?>
                            <?php
                                $normalizedSize = trim($size);
                                $isTrackedSize = array_key_exists($normalizedSize, $sizeStock);
                                $sizeQuantity = $isTrackedSize ? (int) $sizeStock[$normalizedSize] : null;
                                $isOutOfStock = $isTrackedSize && $sizeQuantity < 1;
                                $isSelectedSize = $normalizedSize === $firstAvailableSize;
                            ?>
                            <button type="button" 
                                    data-size="<?= esc($normalizedSize) ?>"
                                    <?= $isOutOfStock ? 'disabled aria-disabled="true"' : '' ?>
                                    class="size-btn py-2.5 text-xs font-bold rounded-xl border border-stone-300 hover:border-stone-900 text-stone-800 transition-all <?= $isSelectedSize ? 'bg-stone-950 text-white' : 'bg-white' ?> <?= $isOutOfStock ? 'opacity-40 cursor-not-allowed line-through' : '' ?>">
                                <?= esc($normalizedSize) ?>
                                <?php if ($isTrackedSize): ?><span class="block text-[10px] font-medium"><?= $sizeQuantity ?> left</span><?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
            </div>

            <!-- Purchase CTAs -->
            <div class="pt-4 space-y-3">
                <input type="hidden" id="selectedSize" name="selectedSize" value="<?= esc($firstAvailableSize ?? '') ?>">
                <button id="addToBagBtn" type="button" class="w-full py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-sm uppercase tracking-wider transition-all shadow-lg hover:shadow-amber-500/20 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Add to Bag</span>
                </button>
                <div class="grid grid-cols-2 gap-3">
                    <?php if (session()->get('is_logged_in') && session()->get('user_role') !== 'admin'): ?>
                        <button id="saveWishlistBtn" type="button" class="py-3 rounded-xl border border-stone-300 hover:border-stone-900 bg-white text-stone-800 text-xs font-bold transition-all flex items-center justify-center gap-2">
                            <i class="fa-regular fa-heart"></i>
                            <span>Save to Wishlist</span>
                        </button>
                    <?php else: ?>
                        <a href="<?= base_url('login') ?>" class="py-3 rounded-xl border border-stone-300 hover:bg-stone-100 text-stone-700 text-xs font-semibold text-center transition-all flex items-center justify-center gap-2">
                            <i class="fa-regular fa-heart"></i>
                            <span>Sign in to Save</span>
                        </a>
                    <?php endif; ?>

                    <?php if (session()->get('is_logged_in') && session()->get('user_role') === 'admin'): ?>
                        <a href="<?= base_url('admin/shoes/edit/' . $shoe['id']) ?>" class="py-3 rounded-xl border border-stone-300 hover:bg-stone-100 text-stone-700 text-xs font-semibold text-center transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Edit (Admin)</span>
                        </a>
                    <?php else: ?>
                        <div class="py-3"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const gallerySlides = <?= json_encode($gallerySlides, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const mainImage = document.getElementById('mainShoeImage');
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    let activeGalleryIndex = 0;

    const showGallerySlide = (index) => {
        activeGalleryIndex = (index + gallerySlides.length) % gallerySlides.length;
        if (mainImage) mainImage.src = gallerySlides[activeGalleryIndex];
        galleryThumbs.forEach((thumb, thumbIndex) => {
            thumb.classList.toggle('border-amber-500', thumbIndex === activeGalleryIndex);
            thumb.classList.toggle('border-stone-200', thumbIndex !== activeGalleryIndex);
        });
    };

    document.getElementById('galleryPrevious')?.addEventListener('click', () => showGallerySlide(activeGalleryIndex - 1));
    document.getElementById('galleryNext')?.addEventListener('click', () => showGallerySlide(activeGalleryIndex + 1));
    galleryThumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => showGallerySlide(Number(thumb.dataset.galleryIndex)));
    });

    const sizeButtons = document.querySelectorAll('.size-btn');
    const selectedInput = document.getElementById('selectedSize');
    const addBtn = document.getElementById('addToBagBtn');

    sizeButtons.forEach(btn => {
        if (btn.disabled) return;
        btn.addEventListener('click', () => {
            // clear others
            sizeButtons.forEach(b => b.classList.remove('bg-stone-950','text-white'));
            btn.classList.add('bg-stone-950','text-white');
            const sz = btn.getAttribute('data-size');
            if (selectedInput) selectedInput.value = sz;
        });
    });

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            const size = selectedInput ? selectedInput.value : '';
            if (!size) {
                alert('Please select a size before adding to bag.');
                return;
            }

            // Basic localStorage cart implementation
            const shoe = {
                id: <?= (int)$shoe['id'] ?>,
                name: <?= json_encode($shoe['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
                price: <?= (float)$shoe['price'] ?>,
                image_url: gallerySlides[activeGalleryIndex] || <?= json_encode($shoe['image_url'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
                size: size,
                qty: 1,
            };

            let cart = [];
            try {
                const storedCart = JSON.parse(localStorage.getItem('cart') || '[]');
                if (Array.isArray(storedCart)) cart = storedCart;
            } catch (error) {
                localStorage.removeItem('cart');
            }
            // merge same item+size
            const idx = cart.findIndex(i => i.id === shoe.id && i.size === shoe.size);
            if (idx > -1) {
                cart[idx].qty += 1;
                cart[idx].image_url = shoe.image_url;
            } else {
                cart.push(shoe);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            window.location.href = <?= json_encode(base_url('cart')) ?>;
        });
    }
});
</script>
<?php $this->endSection() ?>
