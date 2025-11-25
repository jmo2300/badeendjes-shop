<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check of ingelogd
if (!isLoggedIn()) {
    redirect('customer/login.php');
}

$customer = getCurrentCustomer($pdo);
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Haal bestelling op en check of deze van de klant is
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->execute([$order_id, $customer['id']]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Bestelling niet gevonden');
    redirect('customer/orders.php');
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
    <title>Bestelling #<?php echo $order['id']; ?> - Badeendjes Shop</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="alternate icon" href="../favicon.ico">
    <link rel="apple-touch-icon" href="../apple-touch-icon.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="../index.php" class="logo"> Badeendjes Shop</a>
            <ul class="nav-links">
                <li><a href="../index.php">Producten</a></li>
                <li><a href="account.php">Mijn Account</a></li>
                <li><a href="orders.php">Bestellingen</a></li>
                <li><a href="logout.php">Uitloggen</a></li>
                <li class="cart-icon">
                    <a href="../cart.php"> Winkelmandje
                        <?php if (getCartItemCount() > 0): ?>
                            <span class="cart-count"><?php echo getCartItemCount(); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Bestelling #<?php echo $order['id']; ?></h1>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
                <h2>Bestelling Informatie</h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1rem;">
                    <div>
                        <strong>Besteldatum:</strong><br>
                        <?php echo date('d-m-Y H:i', strtotime($order['order_date'])); ?>
                    </div>
                    
                    <div>
                        <strong>Status:</strong><br>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem;">
                <h2>Bestelde Producten</h2>
                
                <div style="margin-top: 1rem;">
                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; gap: 1.5rem; padding: 1.5rem; border-bottom: 1px solid var(--border-color); align-items: center;">
                            <img src="../<?php echo escape($item['image_url']); ?>" 
                                 alt="<?php echo escape($item['name']); ?>" 
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                            
                            <div style="flex: 1;">
                                <strong><?php echo escape($item['name']); ?></strong><br>
                                <span style="color: #666;">
                                    <?php echo $item['quantity']; ?>x <?php echo formatPrice($item['price_per_unit']); ?>
                                </span>
                            </div>
                            
                            <div style="font-weight: bold;">
                                <?php echo formatPrice($item['quantity'] * $item['price_per_unit']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px solid var(--border-color);">
                    <?php if ($order['discount_applied'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Subtotaal:</span>
                            <span><?php echo formatPrice($order['total_price'] + $order['discount_applied']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--success-color);">
                            <span>Korting:</span>
                            <span>- <?php echo formatPrice($order['discount_applied']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 1.5rem; font-weight: bold; color: var(--secondary-color);">
                        <span>Totaal:</span>
                        <span><?php echo formatPrice($order['total_price']); ?></span>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="orders.php" class="btn">← Terug naar bestellingen</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>
</body>
</html>
