<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <nav class="text-xs text-stone-500 mb-6"><a href="<?= base_url('cart') ?>" class="hover:text-stone-900">Your Bag</a> / Checkout</nav>
    <h1 class="text-3xl font-black text-stone-950 mb-2">Checkout</h1>
    <p class="text-sm text-stone-500 mb-8">Enter your contact details to send your order request to us on WhatsApp.</p>
    <div id="checkoutEmpty" class="hidden bg-white border border-stone-200 rounded-2xl p-8">
        <p class="text-stone-700 font-semibold">Your bag is empty.</p>
        <a href="<?= base_url() ?>" class="inline-block mt-4 text-sm font-bold text-amber-700 hover:underline">Continue shopping</a>
    </div>
    <div id="checkoutContent" class="gap-8" style="display: none">
        <form id="checkoutForm" class="bg-white border border-stone-200 rounded-2xl p-6 space-y-5">
            <h2 class="text-lg font-bold text-stone-900">Contact and delivery details</h2>
            <label class="block text-sm font-semibold text-stone-700">Full name
                <input required name="name" autocomplete="name" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2.5 font-normal focus:border-amber-500 focus:outline-none">
            </label>
            <label class="block text-sm font-semibold text-stone-700">Email address
                <input required type="email" name="email" autocomplete="email" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2.5 font-normal focus:border-amber-500 focus:outline-none">
            </label>
            <label class="block text-sm font-semibold text-stone-700">Phone number
                <input required type="tel" name="phone" autocomplete="tel" inputmode="tel" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2.5 font-normal focus:border-amber-500 focus:outline-none">
            </label>
            <button type="submit" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 py-3.5 text-xs font-black uppercase text-white"><i class="fa-brands fa-whatsapp mr-2" aria-hidden="true"></i>Continue to WhatsApp</button>
        </form>
        <aside class="h-fit bg-white border border-stone-200 rounded-2xl p-5">
            <h2 class="font-bold text-stone-900">Order summary</h2>
            <div id="checkoutItems" class="divide-y divide-stone-100"></div>
            <div class="flex justify-between border-t border-stone-200 pt-4 mt-4 text-sm font-black text-stone-950"><span>Total</span><span id="checkoutTotal">KSH 0.00</span></div>
        </aside>
    </div>
</section>
<?= $this->endSection() ?>

<?php $this->section('scripts') ?>
<?php
    $whatsappPhone = preg_replace('/\D+/', '', getenv('WHATSAPP_PHONE_NUMBER') ?: '0780803908');
    $whatsappCountryCode = preg_replace('/\D+/', '', getenv('WHATSAPP_COUNTRY_CODE') ?: '254');
    if (str_starts_with($whatsappPhone, '0')) {
        $whatsappPhone = $whatsappCountryCode . substr($whatsappPhone, 1);
    }
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const readCart = () => {
        try {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            return Array.isArray(cart) ? cart : [];
        } catch (error) {
            localStorage.removeItem('cart');
            return [];
        }
    };
    const cart = readCart();
    const whatsappPhone = <?= json_encode($whatsappPhone) ?>;
    const emptyState = document.getElementById('checkoutEmpty');
    const content = document.getElementById('checkoutContent');
    emptyState.classList.toggle('hidden', cart.length > 0);
    content.style.display = cart.length > 0 ? 'grid' : 'none';
    content.style.gridTemplateColumns = window.matchMedia('(min-width: 1024px)').matches
        ? 'minmax(0, 1fr) 320px'
        : 'minmax(0, 1fr)';
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[character]);
    document.getElementById('checkoutItems').innerHTML = cart.map((item) => `
        <div class="flex justify-between gap-3 py-3 text-xs">
            <span class="text-stone-600">${Number(item.qty) || 1} × ${escapeHtml(item.name || 'Shoe')} <span class="text-stone-400">(US ${escapeHtml(item.size || '')})</span></span>
            <span class="font-bold text-stone-900">KSH ${((Number(item.price) || 0) * (Number(item.qty) || 1)).toFixed(2)}</span>
        </div>
    `).join('');
    const total = cart.reduce((sum, item) => sum + (Number(item.price) || 0) * (Number(item.qty) || 1), 0);
    document.getElementById('checkoutTotal').textContent = `KSH ${total.toFixed(2)}`;
    document.getElementById('checkoutForm').addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        const orderLines = cart.map((item) => {
            const quantity = Number(item.qty) || 1;
            const price = Number(item.price) || 0;
            return `- ${item.name} (US ${item.size}) x${quantity} - KSH ${(price * quantity).toFixed(2)}`;
        });
        const message = [
            'Hello, I would like to complete this order:',
            '',
            `Name: ${formData.get('name')}`,
            `Email: ${formData.get('email')}`,
            `Phone: ${formData.get('phone')}`,
            '',
            'Order:',
            ...orderLines,
            '',
            `Total: KSH ${total.toFixed(2)}`
        ].join('\n');
        const whatsappUrl = `https://api.whatsapp.com/send?phone=${whatsappPhone}&text=${encodeURIComponent(message)}`;
        window.location.assign(whatsappUrl);
    });
});
</script>
<?php $this->endSection() ?>
