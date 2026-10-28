<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin Dashboard | JatheStride') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-stone-100 text-stone-900 min-h-screen flex flex-col font-sans">
    <!-- Top Admin Bar -->
    <header class="bg-stone-900 text-white border-b border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="<?= base_url('admin') ?>" class="flex items-center gap-2 font-black text-xl tracking-tight">
                    <span class="w-7 h-7 rounded bg-amber-500 text-stone-950 flex items-center justify-center text-xs font-black">JE</span>
                    <span>JATHE<span class="text-amber-500">ADMIN</span></span>
                </a>
                <nav class="hidden md:flex items-center gap-4 text-xs font-semibold text-stone-400">
                    <a href="<?= base_url('admin/shoes') ?>" class="text-white hover:text-amber-400 px-3 py-1.5 rounded-md bg-stone-800">Shoes Catalog</a>
                    <a href="<?= base_url('admin/shoes/new') ?>" class="hover:text-amber-400 px-3 py-1.5 rounded-md hover:bg-stone-800 transition-colors">+ Add New Shoe</a>
                    <a href="<?= base_url('admin/stock') ?>" class="hover:text-amber-400 px-3 py-1.5 rounded-md hover:bg-stone-800 transition-colors">Receive Stock</a>
                    <a href="<?= base_url() ?>" target="_blank" class="hover:text-stone-200 px-3 py-1.5 rounded-md flex items-center gap-1">
                        <span>View Live Store</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </nav>
            </div>

            <!-- Profile & Logout -->
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex items-center gap-2 text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="text-stone-300"><?= esc(session()->get('user_email') ?? 'admin@solestore.com') ?></span>
                </div>
                <a href="<?= base_url('logout') ?>" class="text-xs font-medium text-red-400 hover:text-red-300 px-3 py-1.5 rounded bg-stone-800/80 hover:bg-stone-800 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Log Out</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full">
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 shadow-xs">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-white border-t border-stone-200 py-4 text-center text-xs text-stone-500">
        CodeIgniter 4 Admin &bull; Vercel Serverless Production Ready
        <p> Powered by <a href="https://codeigniter.com/" target="_blank" class="text-amber-500 hover:text-amber-400">CodeIgniter 4</a> & <a href="https://tailwindcss.com/" target="_blank" class="text-amber-500 hover:text-amber-400">Tailwind CSS</a></p>
        <p class="mt-1">&copy; <?= date('Y') ?> JatheStride Shoe E-Commerce</p>
    </footer>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
