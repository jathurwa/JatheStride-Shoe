<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$stockProducts = array_map(static function ($shoe) use ($sizeQuantities) {
    return [
        'id'              => (int) $shoe['id'],
        'name'            => $shoe['name'],
        'sizes_available' => $shoe['sizes_available'] ?? '',
        'stock_quantity'  => (int) $shoe['stock_quantity'],
        'size_stock'      => $sizeQuantities[$shoe['id']] ?? [],
    ];
}, $shoes);
?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-end justify-between gap-4">
        <div>
            <a href="<?= base_url('admin/shoes') ?>" class="text-xs text-stone-500 hover:text-stone-900">Inventory</a>
            <h1 class="text-2xl font-black text-stone-900 mt-1">Receive Stock</h1>
            <p class="text-sm text-stone-600 mt-1">Add incoming pairs to existing or new shoe sizes.</p>
        </div>
        <a href="<?= base_url('admin/shoes/new') ?>" class="shrink-0 px-4 py-2 bg-stone-900 hover:bg-stone-700 text-white text-xs font-bold rounded-xl">
            + Add New Shoe
        </a>
    </div>

    <?php if ($shoes === []): ?>
        <div class="bg-white border border-stone-200 rounded-2xl p-8 text-center">
            <p class="font-bold text-stone-900">No shoes in the catalog yet.</p>
            <a href="<?= base_url('admin/shoes/new') ?>" class="inline-flex mt-4 px-4 py-2 bg-amber-500 text-stone-950 text-xs font-bold rounded-lg">Add New Shoe</a>
        </div>
    <?php else: ?>
        <form action="<?= base_url('admin/stock/receive') ?>" method="POST" class="bg-white border border-stone-200 rounded-2xl overflow-hidden">
            <?= csrf_field() ?>
            <div class="p-5 sm:p-7 space-y-6">
                <div>
                    <label for="shoe_id" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-2">Shoe Product</label>
                    <select id="shoe_id" name="shoe_id" required class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-white text-sm">
                        <option value="">Select a shoe</option>
                        <?php foreach ($shoes as $shoe): ?>
                            <option value="<?= esc($shoe['id']) ?>" <?= $selectedShoeId === (int) $shoe['id'] ? 'selected' : '' ?>>
                                <?= esc($shoe['name']) ?> (<?= esc($shoe['category_name'] ?? 'General') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-2 text-xs text-stone-500">Current product total: <strong id="currentProductTotal">Select a shoe</strong></p>
                </div>

                <div class="border border-stone-200 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between gap-3 px-4 py-3 bg-stone-50 border-b border-stone-200">
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-stone-800">Incoming Pairs by Size</h2>
                            <p class="text-[11px] text-stone-500 mt-1">Enter only the quantities being added now.</p>
                        </div>
                        <button type="button" id="addReceiveSizeRow" class="px-3 py-2 bg-stone-900 text-white text-xs font-bold rounded-lg hover:bg-stone-700">
                            <i class="fa-solid fa-plus mr-1" aria-hidden="true"></i>Add Size
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-[10px] uppercase text-stone-500">
                                <tr><th class="px-4 py-2">EU Size</th><th class="px-4 py-2">Currently Recorded</th><th class="px-4 py-2">Add Pairs</th><th class="px-4 py-2 w-12"></th></tr>
                            </thead>
                            <tbody id="receiveSizeRows" class="divide-y divide-stone-100"></tbody>
                            <tfoot class="bg-stone-50 border-t border-stone-200">
                                <tr><td colspan="2" class="px-4 py-3 font-bold text-stone-700">Pairs to receive</td><td id="receiveStockTotal" class="px-4 py-3 font-black text-stone-900">0</td><td></td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <p class="text-xs text-stone-500">Older stock totals may not have been recorded by size. New receipts are tracked per size from now on.</p>
            </div>
            <div class="flex justify-end border-t border-stone-200 bg-stone-50 px-5 sm:px-7 py-4">
                <button type="submit" class="px-5 py-3 bg-amber-500 hover:bg-amber-400 text-stone-950 text-xs font-black uppercase rounded-xl">
                    Save Received Stock
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const products = <?= json_encode($stockProducts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const selectedShoeId = <?= (int) $selectedShoeId ?>;
    const shoeSelect = document.getElementById('shoe_id');
    if (!shoeSelect) return;

    const rows = document.getElementById('receiveSizeRows');
    const currentTotal = document.getElementById('currentProductTotal');
    const receiveTotal = document.getElementById('receiveStockTotal');
    let nextIndex = 0;

    const addRow = (size = '', onHand = null) => {
        const row = document.createElement('tr');
        row.className = 'receive-size-row';

        const sizeCell = document.createElement('td');
        sizeCell.className = 'px-4 py-2';
        const sizeInput = document.createElement('input');
        sizeInput.type = 'number';
        sizeInput.min = '1';
        sizeInput.max = '50';
        sizeInput.step = '0.5';
        sizeInput.required = true;
        sizeInput.name = `size_stock[${nextIndex}][size]`;
        sizeInput.value = size;
        sizeInput.setAttribute('aria-label', 'EU shoe size');
        sizeInput.className = 'w-full max-w-xs px-3 py-2 border border-stone-300 rounded-lg';
        sizeCell.appendChild(sizeInput);

        const onHandCell = document.createElement('td');
        onHandCell.className = 'px-4 py-2 text-stone-600';
        onHandCell.textContent = onHand === null ? 'Not split by size' : `${onHand} pairs`;

        const quantityCell = document.createElement('td');
        quantityCell.className = 'px-4 py-2';
        const quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.min = '0';
        quantityInput.step = '1';
        quantityInput.value = '0';
        quantityInput.required = true;
        quantityInput.name = `size_stock[${nextIndex}][quantity]`;
        quantityInput.setAttribute('aria-label', `Pairs to add for size ${size || 'new size'}`);
        quantityInput.className = 'w-full max-w-xs px-3 py-2 border border-stone-300 rounded-lg';
        quantityCell.appendChild(quantityInput);

        const removeCell = document.createElement('td');
        removeCell.className = 'px-4 py-2';
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-receive-size-row text-stone-400 hover:text-rose-600';
        removeButton.setAttribute('aria-label', 'Remove size row');
        removeButton.innerHTML = '<i class="fa-solid fa-trash" aria-hidden="true"></i>';
        removeCell.appendChild(removeButton);

        row.append(sizeCell, onHandCell, quantityCell, removeCell);
        rows.appendChild(row);
        nextIndex += 1;
    };

    const renderProductSizes = () => {
        const product = products.find((item) => item.id === Number(shoeSelect.value));
        rows.replaceChildren();
        nextIndex = 0;
        if (!product) {
            currentTotal.textContent = 'Select a shoe';
            addRow();
            return;
        }

        currentTotal.textContent = `${product.stock_quantity} pairs`;
        const listedSizes = product.sizes_available.split(',').map((size) => size.trim()).filter(Boolean);
        const sizes = [...new Set([...listedSizes, ...Object.keys(product.size_stock)])];
        if (sizes.length === 0) sizes.push('');
        sizes.forEach((size) => {
            const onHand = Object.prototype.hasOwnProperty.call(product.size_stock, size)
                ? product.size_stock[size]
                : null;
            addRow(size, onHand);
        });
    };

    const updateReceiveTotal = () => {
        receiveTotal.textContent = String([...rows.querySelectorAll('input[name$="[quantity]"]')]
            .reduce((sum, input) => sum + (Number(input.value) || 0), 0));
    };

    document.getElementById('addReceiveSizeRow').addEventListener('click', () => addRow());
    shoeSelect.addEventListener('change', renderProductSizes);
    rows.addEventListener('input', updateReceiveTotal);
    rows.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-receive-size-row');
        if (removeButton && rows.querySelectorAll('.receive-size-row').length > 1) {
            removeButton.closest('.receive-size-row').remove();
            updateReceiveTotal();
        }
    });

    if (selectedShoeId) shoeSelect.value = String(selectedShoeId);
    renderProductSizes();
    updateReceiveTotal();
});
</script>
<?php $this->endSection() ?>