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

    <!-- 30-Minute Session Keep-Alive Heartbeat while Admin Tab is Active -->
    <script>
    (function() {
        setInterval(function() {
            if (!document.hidden) {
                fetch('<?= base_url('admin/shoes') ?>', {
                    method: 'HEAD',
                    credentials: 'same-origin'
                }).catch(function() {});
            }
        }, 5 * 60 * 1000);
    })();
    </script>

    <!-- Client-Side Auto-Compressor to prevent Vercel 413 FUNCTION_PAYLOAD_TOO_LARGE -->
    <script>
    window.compressImageFile = function(file, maxDimension = 1600, quality = 0.82) {
        if (!file || !file.type.startsWith('image/')) return Promise.resolve(file);
        if (file.size <= 600 * 1024) return Promise.resolve(file); // Already lightweight

        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;
                    if (width > maxDimension || height > maxDimension) {
                        if (width > height) {
                            height = Math.round((height * maxDimension) / width);
                            width = maxDimension;
                        } else {
                            width = Math.round((width * maxDimension) / height);
                            height = maxDimension;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (blob && blob.size < file.size) {
                            const newName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                            const compressed = new File([blob], newName, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            resolve(compressed);
                        } else {
                            resolve(file);
                        }
                    }, 'image/jpeg', quality);
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    };

    // Auto-compress large phone/camera images on file selection
    document.addEventListener('change', async function(e) {
        const input = e.target;
        if (input && input.type === 'file' && input.accept && input.accept.includes('image')) {
            if (!input.files || input.files.length === 0) return;

            let hasLargeFile = false;
            for (let i = 0; i < input.files.length; i++) {
                if (input.files[i].size > 600 * 1024) {
                    hasLargeFile = true;
                    break;
                }
            }
            if (!hasLargeFile) return;

            let statusEl = input.parentElement.querySelector('.image-compress-status');
            if (!statusEl) {
                statusEl = document.createElement('div');
                statusEl.className = 'image-compress-status text-[11px] text-amber-600 font-semibold mt-1 flex items-center gap-1.5';
                input.parentElement.appendChild(statusEl);
            }
            statusEl.className = 'image-compress-status text-[11px] text-amber-600 font-semibold mt-1 flex items-center gap-1.5';
            statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Optimizing photo for Vercel upload...';

            try {
                const dt = new DataTransfer();
                let beforeTotal = 0;
                let afterTotal = 0;

                for (let i = 0; i < input.files.length; i++) {
                    const orig = input.files[i];
                    beforeTotal += orig.size;
                    const compressed = await window.compressImageFile(orig);
                    afterTotal += compressed.size;
                    dt.items.add(compressed);
                }

                input.files = dt.files;
                const beforeMb = (beforeTotal / (1024 * 1024)).toFixed(1);
                const afterKb = Math.round(afterTotal / 1024);
                statusEl.className = 'image-compress-status text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1.5';
                statusEl.innerHTML = `<i class="fa-solid fa-circle-check"></i> Optimized photo (${beforeMb}MB &rarr; ${afterKb}KB) to prevent 413 payload limit!`;
            } catch (err) {
                if (statusEl) statusEl.remove();
            }
        }
    });

    // Hard payload limit guard before submitting form
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.enctype === 'multipart/form-data') {
            const fileInputs = form.querySelectorAll('input[type="file"]');
            let totalBytes = 0;
            fileInputs.forEach(input => {
                if (input.files) {
                    for (let i = 0; i < input.files.length; i++) {
                        totalBytes += input.files[i].size;
                    }
                }
            });

            // 4.0 MB limit to guarantee safety under Vercel's 4.5 MB hard cap
            if (totalBytes > 4.0 * 1024 * 1024) {
                e.preventDefault();
                const totalMb = (totalBytes / (1024 * 1024)).toFixed(1);
                alert(`Selected photos total ${totalMb}MB, exceeding Vercel's 4.5MB request limit (413 Payload Too Large).\n\nPlease upload a compressed photo or paste an image URL directly into the link field.`);
                return false;
            }
        }
    });
    </script>
</body>
</html>
