<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check of ingelogd
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Je moet inloggen om af te rekenen');
    redirect('customer/login.php');
}

// Check of winkelmandje leeg is
$items = getCartItems($pdo);
if (empty($items)) {
    setFlashMessage('warning', 'Je winkelmandje is leeg');
    redirect('cart.php');
}

$customer = getCurrentCustomer($pdo);
$subtotal = getCartSubtotal($pdo);
$discounts = calculateDiscounts($pdo);
$total = getCartTotal($pdo);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token (optioneel maar aanbevolen)
    
    // Check voorraad voor alle items
    $stock_error = false;
    foreach ($items as $item) {
        if (!checkStock($pdo, $item['id'], $item['quantity'])) {
            $error = "Niet genoeg voorraad voor " . $item['name'];
            $stock_error = true;
            break;
        }
    }
    
    if (!$stock_error) {
        try {
            // Start transactie
            $pdo->beginTransaction();
            
            // Maak order aan
            $stmt = $pdo->prepare("
                INSERT INTO orders (customer_id, total_price, discount_applied, status) 
                VALUES (?, ?, ?, 'nieuw')
            ");
            $stmt->execute([
                $customer['id'], 
                $total, 
                $discounts['discount_amount']
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Voeg order items toe en update voorraad
            $stmt_item = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price_per_unit) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt_update_stock = $pdo->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");
            
            foreach ($items as $item) {
                // Voeg order item toe
                $stmt_item->execute([
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
                
                // Update voorraad
                $stmt_update_stock->execute([
                    $item['quantity'],
                    $item['id']
                ]);
            }
            
            // Commit transactie
            $pdo->commit();
            
            // Leeg winkelmandje
            clearCart();
            
            // Redirect naar order detail
            setFlashMessage('success', 'Bestelling succesvol geplaatst! Bedankt voor je aankoop.');
            redirect('customer/order_detail.php?id=' . $order_id);
            
        } catch (Exception $e) {
            // Rollback bij fout
            $pdo->rollBack();
            $error = 'Er ging iets mis bij het plaatsen van je bestelling. Probeer het opnieuw.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afrekenen - Badeendjes Shop</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo url('favicon.svg'); ?>">
    <link rel="alternate icon" href="<?php echo url('favicon.ico'); ?>">
    <link rel="apple-touch-icon" href="<?php echo url('apple-touch-icon.png'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo"> Badeendjes Shop</a>
            <ul class="nav-links">
                <li><a href="index.php">Producten</a></li>
                <li><a href="customer/account.php">Mijn Account</a></li>
                <li><a href="customer/orders.php">Bestellingen</a></li>
                <li><a href="customer/logout.php">Uitloggen</a></li>
                <li class="cart-icon">
                    <a href="cart.php"> Winkelmandje
                        <span class="cart-count"><?php echo getCartItemCount(); ?></span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Bestelling Afronden</h1>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; max-width: 1200px; margin: 0 auto;">
            <!-- Bestelling overzicht -->
            <div>
                <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
                    <h2>Jouw Gegevens</h2>
                    <div style="margin-top: 1rem;">
                        <p><strong>Naam:</strong> <?php echo escape($customer['name']); ?></p>
                        <p><strong>E-mail:</strong> <?php echo escape($customer['email']); ?></p>
                    </div>
                </div>
                
                <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem;">
                    <h2>Bestelling Overzicht</h2>
                    
                    <div style="margin-top: 1.5rem;">
                        <?php foreach ($items as $item): ?>
                            <div style="display: flex; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color); align-items: center;">
                                <img src="<?php echo escape($item['image_url']); ?>" 
                                     alt="<?php echo escape($item['name']); ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                
                                <div style="flex: 1;">
                                    <strong><?php echo escape($item['name']); ?></strong><br>
                                    <span style="color: #666;">
                                        <?php echo $item['quantity']; ?>x <?php echo formatPrice($item['price']); ?>
                                    </span>
                                </div>
                                
                                <div style="font-weight: bold;">
                                    <?php echo formatPrice($item['subtotal']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Prijs overzicht & bevestigen -->
            <div>
                <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; position: sticky; top: 2rem;">
                    <h2>Totaal</h2>
                    
                    <div style="margin: 1.5rem 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span>Subtotaal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <?php if ($discounts['discount_amount'] > 0): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: var(--success-color);">
                                <span>Korting (<?php echo $discounts['discount_rule']['discount_percentage']; ?>%):</span>
                                <span>- <?php echo formatPrice($discounts['discount_amount']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; justify-content: space-between; padding-top: 1rem; margin-top: 1rem; border-top: 2px solid var(--border-color); font-size: 1.5rem; font-weight: bold; color: var(--secondary-color);">
                            <span>Totaal:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($discounts['discount_rule']): ?>
                        <div style="background-color: #D4EDDA; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                             <strong><?php echo escape($discounts['discount_rule']['name']); ?></strong> toegepast!
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="checkout.php">
                        <button type="submit" class="btn btn-success btn-full-width" style="font-size: 1.2rem; padding: 1rem;">
                            ✓ Bestelling Bevestigen
                        </button>
                    </form>
                    
                    <a href="cart.php" style="display: block; text-align: center; margin-top: 1rem; color: #666;">
                        ← Terug naar winkelmandje
                    </a>
                    
                    <div style="margin-top: 1.5rem; padding: 1rem; background-color: var(--light-bg); border-radius: 5px; font-size: 0.9rem;">
                        <p style="margin: 0;"><strong>ℹ️ Let op:</strong> Dit is een demo webshop. Er wordt geen echte betaling verwerkt.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>
</body>
</html>
