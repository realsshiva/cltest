<?php
require_once __DIR__ . '/db.php';

function dashE($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function dashBase(float $qty, string $unit): float
{
    return ($unit === 'kg' || $unit === 'L') ? $qty * 1000.0 : $qty;
}

function dashFormat(float $base, string $unitType): string
{
    if ($unitType === 'g' || $unitType === 'kg') {
        return $base >= 1000.0 ? round($base / 1000.0, 2) . ' kg' : round($base, 2) . ' g';
    }
    if ($unitType === 'mL' || $unitType === 'L') {
        return $base >= 1000.0 ? round($base / 1000.0, 2) . ' L' : round($base, 2) . ' mL';
    }
    if ($unitType === 'pcs') {
        return round($base) . ' pcs';
    }
    return round($base, 2) . ' ' . $unitType;
}

// ---- Counts (read-only) ----
$countIngCategories = 0;
$countIngredients = 0;
$countRecCategories = 0;
$countRecipes = 0;
$countActiveBatches = 0;

if ($r = $conn->query('SELECT COUNT(*) AS c FROM `m1-ingredient_category`')) { $countIngCategories = (int)$r->fetch_assoc()['c']; }
if ($r = $conn->query('SELECT COUNT(*) AS c FROM `m11-ingredient_master`')) { $countIngredients = (int)$r->fetch_assoc()['c']; }
if ($r = $conn->query('SELECT COUNT(*) AS c FROM `m2-recipe_category`')) { $countRecCategories = (int)$r->fetch_assoc()['c']; }
if ($r = $conn->query('SELECT COUNT(*) AS c FROM `m22-recipe_master`')) { $countRecipes = (int)$r->fetch_assoc()['c']; }

// ---- Stock levels per ingredient (same rules as master.php: only unexpired batches with stock) ----
$masters = [];
if ($r = $conn->query('SELECT `ingmaster-Id`,`ingmaster-Name`,`ingmaster-UnitType`,`ingmaster-Threshold`,`ingcat-Id` FROM `m11-ingredient_master` ORDER BY `ingmaster-Name` ASC')) {
    while ($row = $r->fetch_assoc()) {
        $masters[$row['ingmaster-Id']] = $row + ['total' => 0.0];
    }
}

$nearExpiry = [];
if ($r = $conn->query('SELECT `ingmaster-Id`,`ing-Brand`,`ing-BatchCode`,`ing-UnitType`,`ing-StockQty`,`ing-Price`,`ing-ExpiryDate`, DATEDIFF(`ing-ExpiryDate`, CURDATE()) AS days_left FROM `m111-ingredient_item` WHERE `ing-StockQty` > 0 AND `ing-ExpiryDate` > CURDATE() ORDER BY `ing-ExpiryDate` ASC')) {
    while ($row = $r->fetch_assoc()) {
        $countActiveBatches++;
        $id = $row['ingmaster-Id'];
        if (isset($masters[$id])) {
            $masters[$id]['total'] += dashBase((float)$row['ing-StockQty'], $row['ing-UnitType']);
        }
        if ((int)$row['days_left'] <= 7) {
            $row['name'] = $masters[$id]['ingmaster-Name'] ?? $id;
            $nearExpiry[] = $row;
        }
    }
}

$lowStock = [];
$outOfStock = 0;
foreach ($masters as $id => $m) {
    $threshold = dashBase((float)$m['ingmaster-Threshold'], (string)$m['ingmaster-UnitType']);
    if ($m['total'] <= 0.0) {
        $outOfStock++;
        $lowStock[] = $m + ['status' => 'Out Of Stock'];
    } elseif ($m['total'] <= $threshold) {
        $lowStock[] = $m + ['status' => 'Low'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/cl-test/invtest/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="top">
        <div>
            <h1>Dashboard</h1>
            <div class="subtle"><?php echo dashE(date('l, F j, Y')); ?></div>
        </div>
        <a class="btn btn-primary" href="/cl-test/invtest/prodform.php">+ New Production</a>
    </div>

    <div class="summary">
        <a class="summary-card stat-link" href="/cl-test/invtest/category.php">
            <h3>Ingredients</h3>
            <div><?php echo $countIngredients; ?></div>
            <span class="stat-note"><?php echo $countIngCategories; ?> categories</span>
        </a>
        <a class="summary-card stat-link" href="/cl-test/invtest/rcategory.php">
            <h3>Recipes</h3>
            <div><?php echo $countRecipes; ?></div>
            <span class="stat-note"><?php echo $countRecCategories; ?> categories</span>
        </a>
        <div class="summary-card">
            <h3>Active batches</h3>
            <div><?php echo $countActiveBatches; ?></div>
            <span class="stat-note">in stock &amp; not expired</span>
        </div>
        <div class="summary-card">
            <h3>Needs attention</h3>
            <div class="<?php echo count($lowStock) > 0 ? 'status-low' : 'status-ok'; ?>"><?php echo count($lowStock); ?></div>
            <span class="stat-note"><?php echo $outOfStock; ?> out of stock</span>
        </div>
    </div>

    <div class="dash-grid">
        <section class="dash-panel">
            <div class="dash-panel-head">
                <h2>Low &amp; out of stock</h2>
                <span class="count-pill"><?php echo count($lowStock); ?></span>
            </div>
            <?php if (count($lowStock) === 0): ?>
                <div class="empty-state">All ingredients are above their threshold.</div>
            <?php else: ?>
                <table>
                    <thead><tr><th>Ingredient</th><th>On hand</th><th>Threshold</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($lowStock as $m): ?>
                        <tr>
                            <td><a href="/cl-test/invtest/master.php?ingmaster-Id=<?php echo rawurlencode($m['ingmaster-Id']); ?>"><?php echo dashE($m['ingmaster-Name']); ?></a></td>
                            <td><?php echo dashE(dashFormat($m['total'], (string)$m['ingmaster-UnitType'])); ?></td>
                            <td><?php echo dashE($m['ingmaster-Threshold'] . ' ' . $m['ingmaster-UnitType']); ?></td>
                            <td><span class="badge <?php echo $m['status'] === 'Low' ? 'badge-near' : 'badge-out'; ?>"><?php echo dashE($m['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="dash-panel">
            <div class="dash-panel-head">
                <h2>Expiring within 7 days</h2>
                <span class="count-pill"><?php echo count($nearExpiry); ?></span>
            </div>
            <?php if (count($nearExpiry) === 0): ?>
                <div class="empty-state">No batches are close to expiry.</div>
            <?php else: ?>
                <table>
                    <thead><tr><th>Ingredient</th><th>Brand / Batch</th><th>Qty</th><th>Expiry</th></tr></thead>
                    <tbody>
                    <?php foreach ($nearExpiry as $b): ?>
                        <tr>
                            <td><a href="/cl-test/invtest/master.php?ingmaster-Id=<?php echo rawurlencode($b['ingmaster-Id']); ?>"><?php echo dashE($b['name']); ?></a></td>
                            <td><?php echo dashE($b['ing-Brand'] . ' · ' . $b['ing-BatchCode']); ?></td>
                            <td><?php echo dashE($b['ing-StockQty'] . ' ' . $b['ing-UnitType']); ?></td>
                            <td><span class="badge badge-near"><?php echo (int)$b['days_left']; ?> day<?php echo (int)$b['days_left'] === 1 ? '' : 's'; ?></span> <span class="subtle"><?php echo dashE($b['ing-ExpiryDate']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
