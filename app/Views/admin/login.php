<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin Login | ApexStride') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-stone-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-stone-950 border border-stone-800 rounded-3xl p-8 sm:p-10 shadow-2xl relative">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-stone-950 font-black text-xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                AS
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Admin Portal</h1>
            <p class="text-xs text-stone-400 mt-1">Sign in to manage shoes, categories & cloud uploads.</p>
        </div>

        <!-- Flash alerts -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?= base_url('login') ?>" method="POST" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-stone-300 mb-1.5">
                    Email Address
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= old('email', 'admin@solestore.com') ?>" 
                       required 
                       class="w-full px-4 py-3 rounded-xl bg-stone-900 border border-stone-700 text-white text-sm focus:outline-hidden focus:border-amber-500 transition-colors">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-stone-300">
                        Password
                    </label>
                    <span class="text-[11px] text-stone-500">Env: ADMIN_PASSWORD</span>
                </div>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       class="w-full px-4 py-3 rounded-xl bg-stone-900 border border-stone-700 text-white text-sm focus:outline-hidden focus:border-amber-500 transition-colors">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-sm uppercase tracking-wider transition-all shadow-lg hover:shadow-amber-500/20 mt-4">
                Secure Sign In
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-stone-800 text-center">
            <a href="<?= base_url() ?>" class="text-xs text-stone-400 hover:text-white transition-colors">
                &larr; Return to Storefront
            </a>
        </div>
    </div>
</body>
</html>
