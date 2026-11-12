<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'ApexStride | Premium Footwear') ?></title>
    <!-- Tailwind CSS via Official CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            500: '#0ea5e9',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen flex flex-col font-sans antialiased">
    <!-- Serverless Notice Banner -->
    <div class="bg-stone-900 text-stone-300 text-xs py-1.5 px-4 text-center flex items-center justify-center gap-2 border-b border-stone-800">
        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <!--<span>Hosted on <strong>Vercel Serverless</strong> &bull; Media streamed via <strong>Cloudinary</strong> &bull; CI4 PHP Runtime</span>-->
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <a href="<?= base_url() ?>" class="flex items-center gap-2 text-stone-950 font-black tracking-tighter text-2xl group flex-shrink-0">
                <span class="w-8 h-8 rounded-lg bg-stone-900 text-white flex items-center justify-center text-sm font-extrabold group-hover:bg-amber-600 transition-colors">
                    AS
                </span>
                <span>APEX<span class="text-amber-600">STRIDE</span></span>
            </a>

            <!-- Category Links (Desktop) -->
            <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-stone-600">
                <a href="<?= base_url() ?>#catalog" class="hover:text-stone-950 transition-colors">All Shoes</a>
                <a href="<?= base_url('offers') ?>#catalog" class="inline-flex items-center gap-1.5 text-rose-600 font-bold hover:text-rose-700 transition-colors">
                    <i class="fa-solid fa-fire text-xs text-rose-500"></i>
                    <span>Offers</span>
                </a>
                <a href="<?= base_url('category/sneakers') ?>#catalog" class="hover:text-stone-950 transition-colors">Sneakers</a>
                <a href="<?= base_url('category/running') ?>#catalog" class="hover:text-stone-950 transition-colors">Running</a>
                <a href="<?= base_url('category/boots') ?>#catalog" class="hover:text-stone-950 transition-colors">Boots</a>
                <a href="<?= base_url('category/formal') ?>#catalog" class="hover:text-stone-950 transition-colors">Formal</a>
            </nav>

            <!-- Search Bar (Desktop) -->
            <form action="<?= base_url('search') ?>#catalog" method="GET" class="hidden md:flex items-center relative flex-1 max-w-xs mx-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-stone-400 text-xs pointer-events-none"></i>
                <input type="search" 
                       name="q" 
                       value="<?= esc($searchTerm ?? '') ?>" 
                       placeholder="Search shoes, colors, sizes..." 
                       class="w-full pl-9 pr-3 py-1.5 bg-stone-100 hover:bg-stone-50 focus:bg-white text-stone-900 placeholder-stone-400 text-xs rounded-full border border-stone-200 focus:outline-none focus:border-stone-900 transition-colors"
                       autocomplete="off">
            </form>

            <!-- User / Admin Actions -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="<?= base_url('offers') ?>" class="md:hidden text-rose-600 font-bold text-xs flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-50 border border-rose-200">
                    <i class="fa-solid fa-tag text-[10px]"></i>
                    <span>Offers</span>
                </a>

                <a href="<?= base_url('admin') ?>" class="text-xs font-semibold px-3 py-1.5 rounded-full border border-stone-300 hover:border-stone-900 hover:text-stone-950 transition-all flex items-center gap-1.5 text-stone-700">
                    <i class="fa-solid fa-lock text-[10px]"></i>
                    <span class="hidden sm:inline">Admin Portal</span>
                    <span class="sm:hidden">Admin</span>
                </a>

                <button class="relative p-2 text-stone-700 hover:text-stone-950" aria-label="Shopping Bag">
                    <i class="fa-solid fa-bag-shopping text-lg"></i>
                    <span class="absolute top-1 right-0 w-4 h-4 rounded-full bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center">0</span>
                </button>
            </div>
        </div>

        <!-- Dedicated Mobile Search Area (available prominently in mobile view) -->
        <div class="md:hidden bg-stone-50 border-t border-stone-200 px-4 py-2.5 shadow-2xs">
            <form action="<?= base_url('search') ?>#catalog" method="GET" class="relative flex items-center">
                <i class="fa-solid fa-magnifying-glass absolute left-3 text-stone-400 text-xs pointer-events-none"></i>
                <input type="search" 
                       name="q" 
                       value="<?= esc($searchTerm ?? '') ?>" 
                       placeholder="Search shoes, colors, sizes..." 
                       class="w-full pl-9 pr-16 py-2 bg-white text-stone-900 placeholder-stone-400 text-xs rounded-xl border border-stone-300 focus:outline-none focus:border-stone-900 focus:ring-1 focus:ring-stone-900 shadow-2xs"
                       autocomplete="off">
                <button type="submit" class="absolute right-1.5 px-2.5 py-1 bg-stone-900 text-white rounded-lg text-xs font-semibold hover:bg-amber-600 transition-colors">
                    Search
                </button>
            </form>
        </div>
    </header>

    <!-- Main Content Injection -->
    <main class="flex-grow">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-stone-950 text-stone-400 text-sm border-t border-stone-900 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="space-y-3">
                <span class="text-white font-black tracking-wider text-lg">APEX<span class="text-amber-600">STRIDE</span></span>
                <p class="text-xs text-stone-500 leading-relaxed">
                    Premium footwear engineered for comfort, performance, and everyday style. Find the perfect pair for every foot, style and occassion
                </p>
            </div>
            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-3">Categories</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="<?= base_url('category/sneakers') ?>" class="hover:text-white transition-colors">Lifestyle &amp; Sneakers</a></li>
                    <li><a href="<?= base_url('category/running') ?>" class="hover:text-white transition-colors">Performance Running</a></li>
                    <li><a href="<?= base_url('category/boots') ?>" class="hover:text-white transition-colors">Rugged Leather Boots</a></li>
                    <li><a href="<?= base_url('category/formal') ?>" class="hover:text-white transition-colors">Handcrafted Formal</a></li>
                    <li><a href="<?= base_url('category/kids') ?>" class="hover:text-white transition-colors">Kids Collection</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-3">Quick Links</h4>
                <ul class="space-y-2 text-xs">
                    <li>
                        <a href="<?= base_url('offers') ?>" class="text-rose-400 hover:text-rose-300 font-semibold transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-tag text-[10px]"></i>
                            <span>Special Offers &amp; Discounts</span>
                        </a>
                    </li>
                    <li><a href="<?= base_url() ?>#catalog" class="hover:text-white transition-colors">All Footwear</a></li>
                    <li><a href="<?= base_url('cart') ?>" class="hover:text-white transition-colors">Shopping Bag</a></li>
                    <li><a href="<?= base_url('admin') ?>" class="hover:text-white transition-colors">Admin Portal</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-stone-900 py-6 text-center text-xs text-stone-400 space-y-1.5">
            <p class="font-medium text-stone-300">&copy; 2026 ApexStride Shoe E-Commerce. Vercel Serverless.</p>
            <p class="text-stone-400">Designed by <strong class="text-stone-200"><a href="https://elvis-portfolio-ejcb.vercel.app" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors underline decoration-stone-600 underline-offset-2">Elvis Ochieng</a></strong></p>
        </div>
    </footer>
</body>
</html>
