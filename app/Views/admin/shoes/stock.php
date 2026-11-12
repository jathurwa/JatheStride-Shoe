<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-stone-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                Manage Shoe Stock & Inventory
            </h1>
            <p class="text-xs text-stone-500 mt-1">Receive new deliveries (+), manually reduce/deduct stock (-), or set exact pair counts (=) by size.</p>
        </div>
        <a href="<?= base_url('admin/shoes') ?>" class="px-4 py-2 rounded-xl border border-stone-300 text-xs font-bold text-stone-700 hover:bg-stone-100 transition-colors inline-flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            Back to Catalog
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 font-medium">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2 font-medium">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Stock Adjustment Form -->
    <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-xs">
        <form id="stockForm" action="<?= base_url('admin/stock') ?>" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Select Shoe -->
            <div>
                <label for="shoe_id" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">
                    Select Shoe Product *
                </label>
                <select id="shoe_id" 
                        name="shoe_id" 
                        required 
                        onchange="this.form.method='GET'; this.form.action='<?= base_url('admin/stock') ?>'; this.form.submit();"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-300 text-sm bg-white focus:outline-hidden focus:border-stone-900">
                    <option value="">-- Choose a shoe from catalog --</option>
                    <?php foreach ($shoes as $s): ?>
                        <option value="<?= esc($s['id']) ?>" <?= ($selectedShoeId == $s['id']) ? 'selected' : '' ?>>
                            #<?= esc($s['id']) ?> - <?= esc($s['name']) ?> (Current Total Stock: <?= esc($s['stock_quantity'] ?? 0) ?> pairs)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php 
                $activeShoe = null;
                if (!empty($selectedShoeId)) {
                    foreach ($shoes as $s) {
                        if ($s['id'] == $selectedShoeId) {
                            $activeShoe = $s;
                            break;
                        }
                    }
                }
            ?>

            <?php if ($activeShoe): ?>
                <!-- Active Shoe Summary -->
                <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200 flex items-center gap-4">
                    <img src="<?= esc($activeShoe['image_url']) ?>" alt="<?= esc($activeShoe['name']) ?>" class="w-16 h-16 object-contain bg-white rounded-xl border border-stone-200 p-1">
                    <div class="flex-grow">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-bold text-stone-900"><?= esc($activeShoe['name']) ?></h3>
                            <span class="text-xs font-black px-2.5 py-1 rounded-lg bg-amber-100 text-amber-900 border border-amber-200">
                                Total: <span id="currentTotalBadge"><?= esc($activeShoe['stock_quantity'] ?? 0) ?></span> pairs
                            </span>
                        </div>
                        <p class="text-xs text-stone-500 mt-0.5">Category: <?= esc($activeShoe['category_name'] ?? 'Footwear') ?> &bull; Price: $<?= number_format((float)$activeShoe['price'], 2) ?></p>
                    </div>
                </div>

                <!-- Action Mode Selector -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-2">
                        Select Inventory Action *
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="actionToggleGroup">
                        <!-- Add / Restock -->
                        <label class="cursor-pointer border-2 rounded-2xl p-3.5 flex items-start gap-3 transition-all border-emerald-500 bg-emerald-50/50" id="label_add">
                            <input type="radio" name="stock_action" value="add" checked class="mt-1 text-emerald-600 focus:ring-emerald-500" onchange="switchAction('add')">
                            <div>
                                <div class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-plus text-emerald-600"></i>
                                    <span>Add Stock (+)</span>
                                </div>
                                <p class="text-[11px] text-emerald-800/80 mt-0.5">Receive supplier shipments or restock sizes.</p>
                            </div>
                        </label>

                        <!-- Reduce / Deduct -->
                        <label class="cursor-pointer border-2 rounded-2xl p-3.5 flex items-start gap-3 transition-all border-stone-200 hover:border-rose-300 bg-white" id="label_reduce">
                            <input type="radio" name="stock_action" value="reduce" class="mt-1 text-rose-600 focus:ring-rose-500" onchange="switchAction('reduce')">
                            <div>
                                <div class="text-xs font-bold text-stone-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-minus text-rose-600"></i>
                                    <span>Reduce Stock (-)</span>
                                </div>
                                <p class="text-[11px] text-stone-500 mt-0.5">Manually deduct pairs sold in-store, damaged, or lost.</p>
                            </div>
                        </label>

                        <!-- Set Exact Stock -->
                        <label class="cursor-pointer border-2 rounded-2xl p-3.5 flex items-start gap-3 transition-all border-stone-200 hover:border-amber-300 bg-white" id="label_set">
                            <input type="radio" name="stock_action" value="set" class="mt-1 text-amber-600 focus:ring-amber-500" onchange="switchAction('set')">
                            <div>
                                <div class="text-xs font-bold text-stone-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-sliders text-amber-600"></i>
                                    <span>Set Exact Stock (=)</span>
                                </div>
                                <p class="text-[11px] text-stone-500 mt-0.5">Overwrite size stock with physical audit counts.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sizes & Quantities Table -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label id="tableHeading" class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                            Enter Incoming Pairs by Size (+)
                        </label>
                        <span id="instructionNote" class="text-[11px] text-stone-500">
                            Enter quantities to add to existing stock
                        </span>
                    </div>

                    <?php 
                        $sizes = array_filter(array_map('trim', explode(',', $activeShoe['sizes_available'] ?? '39,40,41,42,43,44,45')));
                        if (empty($sizes)) {
                            $sizes = ['39', '40', '41', '42', '43', '44', '45'];
                        }
                        $currentQuantities = $sizeQuantities[$activeShoe['id']] ?? [];
                    ?>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <?php foreach ($sizes as $idx => $size): ?>
                            <?php $currentQty = (int) ($currentQuantities[$size] ?? 0); ?>
                            <div class="p-3.5 bg-stone-50 rounded-2xl border border-stone-200 size-card" data-current="<?= $currentQty ?>">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-black text-stone-900">EU <?= esc($size) ?></span>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-stone-200/80 text-stone-700">
                                        Cur: <strong class="text-stone-900"><?= $currentQty ?></strong>
                                    </span>
                                </div>
                                <input type="hidden" name="size_stock[<?= $idx ?>][size]" value="<?= esc($size) ?>">
                                <div class="relative">
                                    <input type="number" 
                                           id="qty_input_<?= $idx ?>"
                                           name="size_stock[<?= $idx ?>][quantity]" 
                                           min="0" 
                                           placeholder="+0" 
                                           value="0"
                                           data-current="<?= $currentQty ?>"
                                           oninput="recalcTotals()"
                                           class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs font-bold text-stone-900 bg-white focus:outline-hidden focus:border-stone-900 transition-colors quantity-input">
                                </div>
                                <div class="mt-1.5 flex items-center justify-between text-[10px] text-stone-500">
                                    <span>New:</span>
                                    <span class="font-bold projected-val" id="proj_<?= $idx ?>"><?= $currentQty ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Live Summary Bar -->
                <div id="summaryBar" class="p-4 rounded-2xl bg-stone-100 border border-stone-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-4">
                        <div>
                            <span class="text-stone-500 text-[11px] block">Current Total</span>
                            <span class="font-black text-stone-900 text-sm"><?= esc($activeShoe['stock_quantity'] ?? 0) ?> pairs</span>
                        </div>
                        <div class="text-stone-300 text-lg">&rarr;</div>
                        <div>
                            <span class="text-stone-500 text-[11px] block" id="summaryChangeLabel">Adjustment</span>
                            <span class="font-black text-emerald-600 text-sm" id="summaryChangeVal">+0 pairs</span>
                        </div>
                        <div class="text-stone-300 text-lg">&rarr;</div>
                        <div>
                            <span class="text-stone-500 text-[11px] block">Projected Total</span>
                            <span class="font-black text-stone-900 text-sm" id="summaryProjectedVal"><?= esc($activeShoe['stock_quantity'] ?? 0) ?> pairs</span>
                        </div>
                    </div>

                    <button type="submit" 
                            id="submitBtn"
                            name="submit_action" 
                            value="save"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus" id="submitIcon"></i>
                        <span id="submitLabel">Receive & Add Stock</span>
                    </button>
                </div>
            <?php else: ?>
                <div class="p-10 text-center text-stone-400 text-xs bg-stone-50 rounded-2xl border border-dashed border-stone-200">
                    <i class="fa-solid fa-shoe-prints text-3xl mb-2 text-stone-300 block"></i>
                    Please select a shoe from the catalog dropdown above to manage its size inventory.
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
let currentAction = 'add';
const currentTotalStock = <?= (int) ($activeShoe['stock_quantity'] ?? 0) ?>;

function switchAction(mode) {
    currentAction = mode;

    // Highlight active radio card
    const labels = {
        add: document.getElementById('label_add'),
        reduce: document.getElementById('label_reduce'),
        set: document.getElementById('label_set')
    };

    const borders = {
        add: ['border-emerald-500', 'bg-emerald-50/50'],
        reduce: ['border-rose-500', 'bg-rose-50/50'],
        set: ['border-amber-500', 'bg-amber-50/50']
    };

    ['add', 'reduce', 'set'].forEach(m => {
        if (!labels[m]) return;
        labels[m].className = 'cursor-pointer border-2 rounded-2xl p-3.5 flex items-start gap-3 transition-all ' + 
            (m === mode ? borders[m].join(' ') : 'border-stone-200 bg-white');
    });

    const heading = document.getElementById('tableHeading');
    const note = document.getElementById('instructionNote');
    const submitBtn = document.getElementById('submitBtn');
    const submitLabel = document.getElementById('submitLabel');
    const submitIcon = document.getElementById('submitIcon');
    const inputs = document.querySelectorAll('.quantity-input');

    if (mode === 'add') {
        if (heading) heading.textContent = 'Enter Incoming Pairs by Size (+)';
        if (note) note.textContent = 'Enter quantities to add to existing stock';
        if (submitBtn) submitBtn.className = 'px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2';
        if (submitLabel) submitLabel.textContent = 'Receive & Add Stock';
        if (submitIcon) submitIcon.className = 'fa-solid fa-plus';
        inputs.forEach(input => {
            input.placeholder = '+0';
            if (input.value === '' || input.dataset.modeSet) {
                input.value = '0';
                delete input.dataset.modeSet;
            }
        });
    } else if (mode === 'reduce') {
        if (heading) heading.textContent = 'Enter Pairs to Deduct by Size (-)';
        if (note) note.textContent = 'Enter quantities to deduct (cannot exceed current stock)';
        if (submitBtn) submitBtn.className = 'px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2';
        if (submitLabel) submitLabel.textContent = 'Deduct & Reduce Stock';
        if (submitIcon) submitIcon.className = 'fa-solid fa-minus';
        inputs.forEach(input => {
            input.placeholder = '-0';
            if (input.value === '' || input.dataset.modeSet) {
                input.value = '0';
                delete input.dataset.modeSet;
            }
        });
    } else { // set
        if (heading) heading.textContent = 'Enter Exact Physical Count by Size (=)';
        if (note) note.textContent = 'Overwrites each size with this exact quantity';
        if (submitBtn) submitBtn.className = 'px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2';
        if (submitLabel) submitLabel.textContent = 'Save Exact Stock Counts';
        if (submitIcon) submitIcon.className = 'fa-solid fa-sliders';
        inputs.forEach(input => {
            input.placeholder = 'Exact count';
            input.value = input.dataset.current || '0';
            input.dataset.modeSet = 'true';
        });
    }

    recalcTotals();
}

function recalcTotals() {
    const inputs = document.querySelectorAll('.quantity-input');
    let delta = 0;
    let newTotal = 0;

    inputs.forEach((input, idx) => {
        const cur = parseInt(input.dataset.current || '0', 10);
        const val = parseInt(input.value || '0', 10);
        const projEl = document.getElementById('proj_' + idx);

        if (currentAction === 'add') {
            const projected = cur + Math.max(0, val);
            if (projEl) projEl.textContent = projected;
            delta += Math.max(0, val);
        } else if (currentAction === 'reduce') {
            const safeVal = Math.max(0, val);
            const projected = Math.max(0, cur - safeVal);
            if (projEl) projEl.textContent = projected;
            delta += safeVal;
        } else { // set
            const safeVal = Math.max(0, val);
            if (projEl) projEl.textContent = safeVal;
            newTotal += safeVal;
        }
    });

    const changeLabel = document.getElementById('summaryChangeLabel');
    const changeVal = document.getElementById('summaryChangeVal');
    const projTotalVal = document.getElementById('summaryProjectedVal');

    if (!changeLabel || !changeVal || !projTotalVal) return;

    if (currentAction === 'add') {
        changeLabel.textContent = 'Addition (+)';
        changeVal.textContent = '+' + delta + ' pairs';
        changeVal.className = 'font-black text-emerald-600 text-sm';
        projTotalVal.textContent = (currentTotalStock + delta) + ' pairs';
    } else if (currentAction === 'reduce') {
        changeLabel.textContent = 'Deduction (-)';
        changeVal.textContent = '-' + delta + ' pairs';
        changeVal.className = 'font-black text-rose-600 text-sm';
        projTotalVal.textContent = Math.max(0, currentTotalStock - delta) + ' pairs';
    } else { // set
        const diff = newTotal - currentTotalStock;
        changeLabel.textContent = 'Net Adjustment';
        changeVal.textContent = (diff >= 0 ? '+' : '') + diff + ' pairs';
        changeVal.className = diff >= 0 ? 'font-black text-emerald-600 text-sm' : 'font-black text-rose-600 text-sm';
        projTotalVal.textContent = newTotal + ' pairs';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    recalcTotals();
});
</script>

<?= $this->endSection() ?>
