<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin login
if (!isAdminLoggedIn()) {
    redirect('admin/login.php');
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_stock'])) {
        foreach ($_POST['stock'] as $product_id => $stock) {
            $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmt->execute([(int)$stock, $product_id]);
        }
        setFlashMessage('success', 'Voorraad succesvol bijgewerkt');
        redirect('admin/products.php');
    }
    
    if (isset($_POST['update_price'])) {
        foreach ($_POST['price'] as $product_id => $price) {
            $stmt = $pdo->prepare("UPDATE products SET price = ? WHERE id = ?");
            $stmt->execute([(float)$price, $product_id]);
        }
        setFlashMessage('success', 'Prijzen succesvol bijgewerkt');
        redirect('admin/products.php');
    }
    
    if (isset($_POST['update_threshold'])) {
        foreach ($_POST['low_stock_threshold'] as $product_id => $threshold) {
            $stmt = $pdo->prepare("UPDATE products SET low_stock_threshold = ? WHERE id = ?");
            $stmt->execute([(int)$threshold, $product_id]);
        }
        setFlashMessage('success', 'Drempelwaarden succesvol bijgewerkt');
        redirect('admin/products.php');
    }
}

// Haal alle producten op
$stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();

// Bereken statistieken
$total_stock_value = getTotalStockValue($pdo);
$total_items = array_sum(array_column($products, 'stock'));
$low_stock = getLowStockProducts($pdo);

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productbeheer - Admin</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="alternate icon" href="../favicon.ico">
    <link rel="apple-touch-icon" href="../apple-touch-icon.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-header">
        <nav style="max-width: 1200px; margin: 0 auto; padding: 0 2rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="dashboard.php" class="logo"> Admin Panel</a>
            <ul class="admin-nav">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Producten</a></li>
                <li><a href="orders.php">Bestellingen</a></li>
                <li><a href="discounts.php">Kortingen</a></li>
                <li><a href="statistics.php">Statistieken</a></li>
                <li><a href="../index.php">Naar Shop</a></li>
                <li><a href="logout.php">Uitloggen</a></li>
            </ul>
        </nav>
    </div>

    <main>
        <h1>Productbeheer</h1>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Overzicht -->
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($products); ?></div>
                <div class="stat-label">Totaal Producten</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_items; ?></div>
                <div class="stat-label">Totaal Voorraad Items</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo formatPrice($total_stock_value); ?></div>
                <div class="stat-label">Totale Voorraadwaarde</div>
            </div>
        </div>

        <?php if (!empty($low_stock)): ?>
            <div class="flash-message flash-warning" style="margin-bottom: 2rem;">
                <strong> Producten met lage voorraad:</strong>
                <?php foreach ($low_stock as $product): ?>
                    <br>• <?php echo escape($product['name']); ?>: <?php echo $product['stock']; ?> stuks
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Voorraad Beheer -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
            <h2>Voorraad Aanpassen</h2>
            
            <form method="POST" action="products.php">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Huidige Voorraad</th>
                            <th>Nieuwe Voorraad</th>
                            <th>Drempelwaarde</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <strong><?php echo escape($product['name']); ?></strong>
                                </td>
                                <td><?php echo $product['stock']; ?> stuks</td>
                                <td>
                                    <input type="number" 
                                           name="stock[<?php echo $product['id']; ?>]" 
                                           value="<?php echo $product['stock']; ?>" 
                                           min="0" 
                                           style="width: 100px;">
                                </td>
                                <td>
                                    <input type="number" 
                                           name="low_stock_threshold[<?php echo $product['id']; ?>]" 
                                           value="<?php echo $product['low_stock_threshold']; ?>" 
                                           min="0" 
                                           style="width: 100px;">
                                </td>
                                <td>
                                    <?php if ($product['stock'] > $product['low_stock_threshold']): ?>
                                        <span class="stock-available">✓ Op voorraad</span>
                                    <?php elseif ($product['stock'] > 0): ?>
                                        <span class="stock-low">⚠ Laag</span>
                                    <?php else: ?>
                                        <span class="stock-out">✗ Uitverkocht</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="update_stock" class="btn btn-success">
                        Voorraad Bijwerken
                    </button>
                    <button type="submit" name="update_threshold" class="btn">
                        Drempelwaarden Bijwerken
                    </button>
                </div>
            </form>
        </div>

        <!-- Prijsbeheer -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem;">
            <h2>Prijzen Aanpassen</h2>
            
            <form method="POST" action="products.php">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Huidige Prijs</th>
                            <th>Nieuwe Prijs</th>
                            <th>Voorraadwaarde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <strong><?php echo escape($product['name']); ?></strong>
                                </td>
                                <td><?php echo formatPrice($product['price']); ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        €
                                        <input type="number" 
                                               name="price[<?php echo $product['id']; ?>]" 
                                               value="<?php echo $product['price']; ?>" 
                                               min="0" 
                                               step="0.01" 
                                               style="width: 100px;">
                                    </div>
                                </td>
                                <td><?php echo formatPrice($product['price'] * $product['stock']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <button type="submit" name="update_price" class="btn btn-success mt-3">
                    Prijzen Bijwerken
                </button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop - Admin Panel</p>
    </footer>
</body>
</html>
