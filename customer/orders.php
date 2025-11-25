<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check of ingelogd
if (!isLoggedIn()) {
    redirect('customer/login.php');
}

$customer = getCurrentCustomer($pdo);

// Haal bestellingen op
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.customer_id = ?
    GROUP BY o.id
    ORDER BY o.order_date DESC
");
$stmt->execute([$customer['id']]);
$orders = $stmt->fetchAll();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Bestellingen - Badeendjes Shop</title>
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
        <h1>Mijn Bestellingen</h1>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"></div>
                <h2>Nog geen bestellingen</h2>
                <p>Je hebt nog geen bestellingen geplaatst.</p>
                <a href="../index.php" class="btn mt-3">Start met winkelen</a>
            </div>
        <?php else: ?>
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
                <table>
                    <thead>
                        <tr>
                            <th>Bestelnummer</th>
                            <th>Datum</th>
                            <th>Aantal Items</th>
                            <th>Totaal</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($order['order_date'])); ?></td>
                                <td><?php echo $order['item_count']; ?></td>
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
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>
</body>
</html>
