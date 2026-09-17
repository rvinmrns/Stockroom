<?php if ($products): ?>
<div class="product-grid">
    <?php foreach ($products as $product): ?>
    <article class="catalog-card">
        <div class="catalog-card-top"><span class="product-icon" aria-hidden="true">▦</span><span class="badge <?= $product['quantity'] == 0 ? 'out' : ($product['quantity'] <= $product['minimum_stock'] ? 'low' : 'good') ?>"><?= $product['quantity'] == 0 ? 'Out of stock' : ($product['quantity'] <= $product['minimum_stock'] ? 'Low stock' : 'In stock') ?></span></div>
        <h3><?= e($product['name']) ?></h3>
        <dl class="catalog-metrics"><div><dt>Available units</dt><dd><?= number_format($product['quantity']) ?></dd></div></dl>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state"><span class="empty-icon" aria-hidden="true">▦</span><h3><?= $search !== '' || $status !== 'all' ? 'No matching products' : 'The catalog is empty' ?></h3><p><?= $search !== '' || $status !== 'all' ? 'Try another search or view all products.' : 'Products will appear here when your admin adds them.' ?></p><?php if ($search !== '' || $status !== 'all'): ?><a class="button secondary" href="stockroom.php">View all products</a><?php endif; ?></div>
<?php endif; ?>
