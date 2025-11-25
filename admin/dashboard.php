<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin login
if (!isAdminLoggedIn()) {
    redirect('admin/login.php');
}

// Statistieken ophalen
$stats = [];

// Totale omzet
$stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders");
$stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

// Totaal aantal bestellingen
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $stmt->fetch()['count'];

// Aantal nieuwe bestellingen
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'nieuw'");
$stats['new_orders'] = $stmt->fetch()['count'];

// Totale voorraadwaarde
$stats['stock_value'] = getTotalStockValue($pdo);

// Best verkochte producten
$stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price_per_unit) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
$best_sellers = $stmt->fetchAll();

// Lage voorraad producten
$low_stock_products = getLowStockProducts($pdo);

// Recente bestellingen
$stmt = $pdo->query("
    SELECT o.*, c.name as customer_name, c.email as customer_email
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    ORDER BY o.order_date DESC
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Badeendjes Shop</title>
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
        <h1>Dashboard</h1>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistieken -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
                <div class="stat-label">Totale Omzet</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Totaal Bestellingen</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value" style="color: var(--success-color);">
                    <?php echo $stats['new_orders']; ?>
                </div>
                <div class="stat-label">Nieuwe Bestellingen</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo formatPrice($stats['stock_value']); ?></div>
                <div class="stat-label">Voorraadwaarde</div>
            </div>
        </div>

        <!-- Waarschuwingen -->
        <?php if (!empty($low_stock_products)): ?>
            <div class="flash-message flash-warning">
                <strong> Lage Voorraad Waarschuwing!</strong><br>
                De volgende producten hebben lage voorraad:
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    <?php foreach ($low_stock_products as $product): ?>
                        <li>
                            <?php echo escape($product['name']); ?>: 
                            <strong><?php echo $product['stock']; ?> stuks</strong>
                            <a href="products.php" style="margin-left: 0.5rem;">[Voorraad aanvullen]</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Best verkochte producten -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
            <h2>Best Verkochte Producten</h2>
            
            <?php if (empty($best_sellers)): ?>
                <p style="color: #666;">Nog geen verkopen</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Aantal Verkocht</th>
                            <th>Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($best_sellers as $product): ?>
                            <tr>
                                <td><?php echo escape($product['name']); ?></td>
                                <td><?php echo $product['total_sold']; ?> stuks</td>
                                <td><?php echo formatPrice($product['revenue']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Recente bestellingen -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem;">
            <h2>Recente Bestellingen</h2>
            
            <?php if (empty($recent_orders)): ?>
                <p style="color: #666;">Nog geen bestellingen</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Bestelnr</th>
                            <th>Klant</th>
                            <th>Datum</th>
                            <th>Totaal</th>
                            <th>Status</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo escape($order['customer_name']); ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($order['order_date'])); ?></td>
                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-small">
                                        Bekijk
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="text-center mt-3">
                    <a href="orders.php" class="btn">Alle bestellingen bekijken</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop - Admin Panel</p>
    </footer>
</body>
</html>
