<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'ApexStride Footwear') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen flex flex-col">
    <!-- Header / Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="<?= base_url() ?>" class="flex items-center gap-2 text-stone-950 font-black text-2xl tracking-tight">
                <span class="w-8 h-8 rounded-lg bg-stone-900 text-amber-500 flex items-center justify-center text-xs">JE</span>
                <span>JATHE<span class="text-amber-600">STRIDE</span></span>
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-stone-600">
                <a href="<?= base_url() ?>" class="hover:text-stone-950 transition-colors">Catalog</a>
                <a href="<?= base_url('category/sneakers') ?>" class="hover:text-stone-950 transition-colors">Sneakers</a>
                <a href="<?= base_url('category/running') ?>" class="hover:text-stone-950 transition-colors">Running</a>
                <a href="<?= base_url('category/boots') ?>" class="hover:text-stone-950 transition-colors">Boots</a>
                <a href="<?= base_url('category/formal') ?>" class="hover:text-stone-950 transition-colors">Formal</a>
                <a href="<?= base_url('category/casual') ?>" class="hover:text-stone-950 transition-colors">Casual</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="<?= base_url('cart') ?>" class="px-3 py-1.5 rounded-full border border-stone-300 hover:border-stone-900 text-xs font-semibold text-stone-700 transition-colors">
                    <i class="fa-solid fa-bag-shopping mr-1.5" aria-hidden="true"></i>Bag (<span class="cart-count">0</span>)
                </a>
                <a href="<?= base_url('admin') ?>" class="px-3 py-1.5 rounded-full border border-stone-300 hover:border-stone-900 text-xs font-semibold text-stone-700 transition-colors">
                    Admin Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Content slot -->
    <main class="flex-grow">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-stone-950 text-stone-400 py-10 border-t border-stone-900 text-xs text-center">
        <p>&copy; <?= date('Y') ?> JatheStride Shoe E-Commerce. Vercel Serverless.</p>
        <p>Powered by <a href="https://codeigniter.com/" target="_blank" class="text-amber-500 hover:text-amber-400">CodeIgniter 4</a> & <a href="https://tailwindcss.com/" target="_blank" class="text-amber-500 hover:text-amber-400">Tailwind CSS</a></p>
    </footer>
    <?= $this->renderSection('scripts') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let cart = [];
            try {
                const storedCart = JSON.parse(localStorage.getItem('cart') || '[]');
                if (Array.isArray(storedCart)) cart = storedCart;
            } catch (error) {
                localStorage.removeItem('cart');
            }
            const count = cart.reduce((total, item) => total + (Number(item.qty) || 0), 0);
            document.querySelectorAll('.cart-count').forEach((element) => {
                element.textContent = String(count);
            });
        });
    </script>
</body>
</html>