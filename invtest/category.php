<?php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Categories</title>
    <link rel="stylesheet" href="/cl-test/invtest/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <h1>Raw Ingredients</h1>
    <div class="container">
        <?php
        $sql = "SELECT c.`ingcat-Id` AS id, c.`ingcat-Name` AS name, (SELECT COUNT(*) FROM `m11-ingredient_master` m WHERE m.`ingcat-Id` = c.`ingcat-Id`) AS item_count FROM `m1-ingredient_category` c";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // pass the original category id string in the URL (do not cast to int)
                echo '<a class="card" href="/cl-test/invtest/item.php?ingcat-Id=' . rawurlencode($row['id']) . '">';
                echo '<h2>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</h2>';
                echo '<span class="card-meta">' . (int)$row['item_count'] . ' item' . ((int)$row['item_count'] === 1 ? '' : 's') . '</span>';
                echo '</a>';
            }
        } else {
            echo '<div class="empty-state">No categories found.</div>';
        }
        ?>
    </div>
</body>
</html>
