<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin login
if (!isAdminLoggedIn()) {
    redirect('admin/login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Haal bestelling op
$stmt = $pdo->prepare("
    SELECT o.*, c.name as customer_name, c.email as customer_email
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Bestelling niet gevonden');
    redirect('admin/orders.php');
}

// Haal order items op
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestelling #<?php echo $order['id']; ?> - Admin</title>
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
        <h1>Bestelling #<?php echo $order['id']; ?></h1>
        
        <div style="max-width: 900px; margin: 0 auto;">
            <!-- Klant info en status -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem;">
                    <h2>Klantgegevens</h2>
                    <div style="margin-top: 1rem;">
                        <p><strong>Naam:</strong><br><?php echo escape($order['customer_name']); ?></p>
                        <p><strong>E-mail:</strong><br><?php echo escape($order['customer_email']); ?></p>
                    </div>
                </div>
                
                <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem;">
                    <h2>Bestellingsinformatie</h2>
                    <div style="margin-top: 1rem;">
                        <p><strong>Besteldatum:</strong><br><?php echo date('d-m-Y H:i', strtotime($order['order_date'])); ?></p>
                        <p>
                            <strong>Status:</strong><br>
                            <span class="status-badge status-<?php echo $order['status']; ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Bestelde producten -->
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
                <h2>Bestelde Producten</h2>
                
                <div style="margin-top: 1.5rem;">
                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; gap: 1.5rem; padding: 1.5rem; border-bottom: 1px solid var(--border-color); align-items: center;">
                            <img src="../<?php echo escape($item['image_url']); ?>" 
                                 alt="<?php echo escape($item['name']); ?>" 
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                            
                            <div style="flex: 1;">
                                <strong style="font-size: 1.1rem;"><?php echo escape($item['name']); ?></strong><br>
                                <span style="color: #666;">
                                    Aantal: <?php echo $item['quantity']; ?>x
                                </span><br>
                                <span style="color: #666;">
                                    Prijs per stuk: <?php echo formatPrice($item['price_per_unit']); ?>
                                </span>
                            </div>
                            
                            <div style="font-weight: bold; font-size: 1.2rem;">
                                <?php echo formatPrice($item['quantity'] * $item['price_per_unit']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Totalen -->
                <div style="margin-top: 2rem; padding: 1.5rem; background-color: var(--light-bg); border-radius: 5px;">
                    <?php if ($order['discount_applied'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 1.1rem;">
                            <span>Subtotaal:</span>
                            <span><?php echo formatPrice($order['total_price'] + $order['discount_applied']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 1.1rem; color: var(--success-color);">
                            <span>Korting:</span>
                            <span>- <?php echo formatPrice($order['discount_applied']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; padding-top: 1rem; border-top: 2px solid var(--border-color); font-size: 1.8rem; font-weight: bold; color: var(--secondary-color);">
                        <span>Totaal:</span>
                        <span><?php echo formatPrice($order['total_price']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Status wijzigen -->
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
                <h2>Status Wijzigen</h2>
                
                <form method="POST" action="orders.php">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                        <label style="font-weight: bold;">Nieuwe status:</label>
                        <select name="status" style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 5px;">
                            <option value="nieuw" <?php echo $order['status'] == 'nieuw' ? 'selected' : ''; ?>>Nieuw</option>
                            <option value="verwerkt" <?php echo $order['status'] == 'verwerkt' ? 'selected' : ''; ?>>Verwerkt</option>
                            <option value="verzonden" <?php echo $order['status'] == 'verzonden' ? 'selected' : ''; ?>>Verzonden</option>
                        </select>
                        <button type="submit" class="btn btn-success">Status Bijwerken</button>
                    </div>
                </form>
            </div>
            
            <div style="text-align: center;">
                <a href="orders.php" class="btn">← Terug naar bestellingen</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop - Admin Panel</p>
    </footer>
</body>
</html>
