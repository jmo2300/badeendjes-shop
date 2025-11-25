<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin login
if (!isAdminLoggedIn()) {
    redirect('admin/login.php');
}

// Periode selectie
$period = isset($_GET['period']) ? $_GET['period'] : '30';

// Bepaal datumbereik
$date_condition = "";
switch($period) {
    case '7':
        $date_condition = "WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $period_label = "Laatste 7 dagen";
        break;
    case '30':
        $date_condition = "WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $period_label = "Laatste 30 dagen";
        break;
    case '90':
        $date_condition = "WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        $period_label = "Laatste 90 dagen";
        break;
    case '365':
        $date_condition = "WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 365 DAY)";
        $period_label = "Laatste jaar";
        break;
    default:
        $date_condition = "";
        $period_label = "Alle tijd";
}

// Omzet statistieken
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as order_count,
        SUM(total_price) as total_revenue,
        AVG(total_price) as avg_order_value,
        SUM(discount_applied) as total_discounts
    FROM orders o
    $date_condition
");
$revenue_stats = $stmt->fetch();

// Best verkochte producten
$stmt = $pdo->query("
    SELECT 
        p.id,
        p.name,
        SUM(oi.quantity) as total_sold,
        SUM(oi.quantity * oi.price_per_unit) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    $date_condition
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
");
$best_sellers = $stmt->fetchAll();

// Minst verkochte producten
$stmt = $pdo->query("
    SELECT 
        p.id,
        p.name,
        COALESCE(SUM(oi.quantity), 0) as total_sold,
        COALESCE(SUM(oi.quantity * oi.price_per_unit), 0) as revenue
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id
    " . ($date_condition ? "AND o.order_date >= DATE_SUB(NOW(), INTERVAL $period DAY)" : "") . "
    GROUP BY p.id
    ORDER BY total_sold ASC
    LIMIT 7
");
$least_sellers = $stmt->fetchAll();

// Orders per dag/week voor grafiek
$stmt = $pdo->query("
    SELECT 
        DATE(order_date) as order_day,
        COUNT(*) as order_count,
        SUM(total_price) as daily_revenue
    FROM orders o
    $date_condition
    GROUP BY DATE(order_date)
    ORDER BY order_day DESC
    LIMIT 30
");
$daily_stats = $stmt->fetchAll();

// Status verdeling
$stmt = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count
    FROM orders o
    $date_condition
    GROUP BY status
");
$status_stats = $stmt->fetchAll();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistieken - Admin</title>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Verkoopstatistieken</h1>
            
            <form method="GET" action="statistics.php">
                <select name="period" onchange="this.form.submit()" 
                        style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 5px; font-size: 1rem;">
                    <option value="7" <?php echo $period == '7' ? 'selected' : ''; ?>>Laatste 7 dagen</option>
                    <option value="30" <?php echo $period == '30' ? 'selected' : ''; ?>>Laatste 30 dagen</option>
                    <option value="90" <?php echo $period == '90' ? 'selected' : ''; ?>>Laatste 90 dagen</option>
                    <option value="365" <?php echo $period == '365' ? 'selected' : ''; ?>>Laatste jaar</option>
                    <option value="all" <?php echo $period == 'all' ? 'selected' : ''; ?>>Alle tijd</option>
                </select>
            </form>
        </div>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div style="background: var(--light-bg); padding: 1rem; border-radius: 10px; margin-bottom: 2rem; text-align: center;">
            <strong>Periode:</strong> <?php echo $period_label; ?>
        </div>

        <!-- Hoofdstatistieken -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo formatPrice($revenue_stats['total_revenue'] ?? 0); ?></div>
                <div class="stat-label">Totale Omzet</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $revenue_stats['order_count'] ?? 0; ?></div>
                <div class="stat-label">Aantal Bestellingen</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo formatPrice($revenue_stats['avg_order_value'] ?? 0); ?></div>
                <div class="stat-label">Gem. Bestelwaarde</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value" style="color: var(--success-color);">
                    <?php echo formatPrice($revenue_stats['total_discounts'] ?? 0); ?>
                </div>
                <div class="stat-label">Totale Kortingen</div>
            </div>
        </div>

        <!-- Status verdeling -->
        <?php if (!empty($status_stats)): ?>
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
                <h2>Status Verdeling</h2>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1.5rem;">
                    <?php foreach ($status_stats as $stat): ?>
                        <div style="text-align: center; padding: 1.5rem; background: var(--light-bg); border-radius: 10px;">
                            <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">
                                <?php echo $stat['count']; ?>
                            </div>
                            <div style="color: #666;">
                                <?php echo ucfirst($stat['status']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Best verkochte producten -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
            <h2>Best Verkochte Producten</h2>
            
            <?php if (empty($best_sellers)): ?>
                <p style="color: #666; margin-top: 1rem;">Nog geen verkopen in deze periode</p>
            <?php else: ?>
                <table style="margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Product</th>
                            <th>Aantal Verkocht</th>
                            <th>Omzet</th>
                            <th>% van Totaal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        $total_revenue = $revenue_stats['total_revenue'] ?? 1;
                        foreach ($best_sellers as $product): 
                            $percentage = ($product['revenue'] / $total_revenue) * 100;
                        ?>
                            <tr>
                                <td><strong>#<?php echo $rank++; ?></strong></td>
                                <td><?php echo escape($product['name']); ?></td>
                                <td><?php echo $product['total_sold']; ?> stuks</td>
                                <td><?php echo formatPrice($product['revenue']); ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <?php echo number_format($percentage, 1); ?>%
                                        <div style="flex: 1; height: 8px; background: var(--light-bg); border-radius: 4px; overflow: hidden;">
                                            <div style="height: 100%; background: var(--secondary-color); width: <?php echo min($percentage, 100); ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Minst verkochte producten -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
            <h2>Minst Verkochte Producten</h2>
            
            <table style="margin-top: 1rem;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Aantal Verkocht</th>
                        <th>Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($least_sellers as $product): ?>
                        <tr>
                            <td><?php echo escape($product['name']); ?></td>
                            <td><?php echo $product['total_sold']; ?> stuks</td>
                            <td><?php echo formatPrice($product['revenue']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Dagelijkse verkopen -->
        <?php if (!empty($daily_stats)): ?>
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem;">
                <h2>Dagelijkse Verkopen (Laatste 30 dagen)</h2>
                
                <table style="margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Bestellingen</th>
                            <th>Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($daily_stats) as $stat): ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($stat['order_day'])); ?></td>
                                <td><?php echo $stat['order_count']; ?></td>
                                <td><?php echo formatPrice($stat['daily_revenue']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop - Admin Panel</p>
    </footer>
</body>
</html>
