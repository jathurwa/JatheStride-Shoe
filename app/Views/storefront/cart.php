<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <nav class="text-xs text-stone-500 mb-6"><a href="<?= base_url() ?>" class="hover:text-stone-900">Home</a> / Your Bag</nav>
    <h1 class="text-3xl font-black text-stone-950 mb-8">Your Bag</h1>
    <div id="emptyCart" class="hidden bg-white border border-stone-200 rounded-2xl p-10 text-center">
        <i class="fa-solid fa-bag-shopping text-3xl text-stone-300 mb-4" aria-hidden="true"></i>
        <h2 class="text-lg font-bold text-stone-900">Your bag is empty</h2>
        <p class="text-sm text-stone-500 mt-2">Find your next pair in the footwear collection.</p>
        <a href="<?= base_url() ?>" class="inline-flex mt-5 px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 text-sm font-bold">Continue shopping</a>
    </div>
    <div id="cartContent" class="gap-8" style="display: none">
        <div id="cartItems" class="space-y-3"></div>
        <aside class="h-fit bg-white border border-stone-200 rounded-2xl p-5">
            <h2 class="font-bold text-stone-900">Order summary</h2>
            <div class="flex justify-between text-sm text-stone-600 mt-5">
                <span>Subtotal</span><span id="cartSubtotal" class="font-bold text-stone-900">KSH 0.00</span>
            </div>
            <p class="text-xs text-stone-500 mt-2">Shipping calculated at checkout.</p>
            <a href="<?= base_url('checkout') ?>" class="block text-center mt-5 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 text-xs font-black uppercase">Proceed to Checkout</a>
        </aside>
    </div>
</section>
<?= $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emptyState = document.getElementById('emptyCart');
    const cartContent = document.getElementById('cartContent');
    const itemsContainer = document.getElementById('cartItems');
    const subtotalElement = document.getElementById('cartSubtotal');

    const readCart = () => {
        try {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            return Array.isArray(cart) ? cart : [];
        } catch (error) {
            localStorage.removeItem('cart');
            return [];
        }
    };
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[character]);

    const render = () => {
        const cart = readCart();
        emptyState.classList.toggle('hidden', cart.length > 0);
        cartContent.style.display = cart.length > 0 ? 'grid' : 'none';
        document.querySelectorAll('.cart-count').forEach((element) => {
            element.textContent = String(cart.reduce((count, item) => count + (Number(item.qty) || 0), 0));
        });
        itemsContainer.innerHTML = cart.map((item, index) => `
            <article class="flex items-center gap-4 bg-white border border-stone-200 rounded-2xl p-4">
                <img src="${escapeHtml(item.image_url || '')}" alt="${escapeHtml(item.name)}" class="w-20 h-20 object-contain bg-stone-50 rounded-xl" onerror="this.style.display='none'">
                <div class="min-w-0 flex-grow">
                    <h2 class="font-bold text-sm text-stone-900">${escapeHtml(item.name)}</h2>
                    <p class="text-xs text-stone-500 mt-1">EU size ${escapeHtml(item.size)}</p>
                    <p class="font-bold text-sm mt-2">KSH ${(Number(item.price) || 0).toFixed(2)}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-action="decrease" data-index="${index}" aria-label="Decrease quantity" class="w-8 h-8 border border-stone-300 rounded-lg font-bold hover:bg-stone-100">-</button>
                    <span class="min-w-5 text-center text-sm font-bold">${Number(item.qty) || 1}</span>
                    <button type="button" data-action="increase" data-index="${index}" aria-label="Increase quantity" class="w-8 h-8 border border-stone-300 rounded-lg font-bold hover:bg-stone-100">+</button>
                </div>
                <button type="button" data-action="remove" data-index="${index}" aria-label="Remove ${escapeHtml(item.name)}" class="p-2 text-stone-400 hover:text-rose-600"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
            </article>
        `).join('');
        const subtotal = cart.reduce((total, item) => total + (Number(item.price) || 0) * (Number(item.qty) || 1), 0);
        subtotalElement.textContent = `KSH ${subtotal.toFixed(2)}`;
    };

    itemsContainer.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const cart = readCart();
        const index = Number(button.dataset.index);
        if (!cart[index]) return;
        if (button.dataset.action === 'remove') cart.splice(index, 1);
        if (button.dataset.action === 'increase') cart[index].qty = (Number(cart[index].qty) || 1) + 1;
        if (button.dataset.action === 'decrease') {
            cart[index].qty = (Number(cart[index].qty) || 1) - 1;
            if (cart[index].qty <= 0) cart.splice(index, 1);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        render();
    });

    render();
});
</script>
<?php $this->endSection() ?>
