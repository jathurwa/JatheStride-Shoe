<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 bg-white rounded-3xl border border-stone-200 p-6 sm:p-10 shadow-sm">
        <!-- Image Box -->
        <div class="bg-stone-100 rounded-2xl p-8 flex flex-col items-center justify-center relative">
            <img src="<?= esc($shoe['image_url']) ?>" alt="<?= esc($shoe['name']) ?>" class="max-h-80 object-contain drop-shadow-xl" />
            <div class="mt-6 text-[11px] text-stone-500 flex items-center gap-2">
                <i class="fa-solid fa-cloud text-amber-600"></i>
                <span>Cloudinary CDN Hosted &bull; ID #<?= esc($shoe['id']) ?></span>
            </div>
        </div>

        <!-- Details -->
        <div class="space-y-6 flex flex-col justify-between">
            <div class="space-y-3">
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider"><?= esc($shoe['category_name'] ?? 'Footwear') ?></span>
                <h1 class="text-3xl font-black text-stone-950 tracking-tight"><?= esc($shoe['name']) ?></h1>
                <div class="text-2xl font-black text-stone-900">KSH <?= number_format($shoe['price'], 2) ?></div>
                <p class="text-xs text-stone-600 leading-relaxed border-t border-stone-100 pt-4"><?= esc($shoe['description']) ?></p>

                <!-- Sizes Available -->
                <div class="pt-4">
                    <span class="text-xs font-bold text-stone-800 uppercase block mb-2">Available Sizes:</span>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (explode(',', $shoe['sizes_available']) as $sz): ?>
                            <span class="px-3 py-1.5 rounded-xl border border-stone-200 text-xs font-bold bg-stone-50">
                                US <?= esc(trim($sz)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <button class="w-full py-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs uppercase tracking-wider transition-all shadow-md">
                    Add To Footwear Bag
                </button>

                <!-- WhatsApp API Checkout Link -->
                <?php 
                    $waPhone = getenv('WHATSAPP_PHONE_NUMBER') ?: '0780803908';
                    $waCountry = getenv('WHATSAPP_COUNTRY_CODE') ?: '254';
                    $cleanPhone = (substr($waPhone, 0, 1) === '0') ? $waCountry . substr($waPhone, 1) : $waPhone;
                    $waMsg = "Hello! I would like to order: " . $shoe['name'] . " - Price: KSH " . number_format($shoe['price'], 2) . " from ApexStride.";
                    $waUrl = "https://api.whatsapp.com/send?phone=" . $cleanPhone . "&text=" . urlencode($waMsg);
                ?>
                <a href="<?= $waUrl ?>" target="_blank" rel="noopener noreferrer" class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                    <span>Order Now via WhatsApp (<?= esc($waPhone) ?>)</span>
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>