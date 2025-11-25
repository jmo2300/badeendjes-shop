<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            updateCartQuantity($product_id, (int)$quantity);
        }
        setFlashMessage('success', 'Winkelmandje bijgewerkt');
        redirect('cart.php');
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = (int)$_POST['product_id'];
        removeFromCart($product_id);
        setFlashMessage('success', 'Product verwijderd uit winkelmandje');
        redirect('cart.php');
    }
    
    if (isset($_POST['clear_cart'])) {
        clearCart();
        setFlashMessage('success', 'Winkelmandje geleegd');
        redirect('cart.php');
    }
}

$items = getCartItems($pdo);
$subtotal = getCartSubtotal($pdo);
$discounts = calculateDiscounts($pdo);
$total = getCartTotal($pdo);
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelmandje - Badeendjes Shop</title>
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
                <?php if (isLoggedIn()): ?>
                    <li><a href="customer/account.php">Mijn Account</a></li>
                    <li><a href="customer/orders.php">Bestellingen</a></li>
                    <li><a href="customer/logout.php">Uitloggen</a></li>
                <?php else: ?>
                    <li><a href="customer/login.php">Inloggen</a></li>
                    <li><a href="customer/register.php">Registreren</a></li>
                <?php endif; ?>
                <li class="cart-icon">
                    <a href="cart.php"> Winkelmandje
                        <?php if (getCartItemCount() > 0): ?>
                            <span class="cart-count"><?php echo getCartItemCount(); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Winkelmandje</h1>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"></div>
                <h2>Je winkelmandje is leeg</h2>
                <p>Voeg wat leuke badeendjes toe om te beginnen!</p>
                <a href="index.php" class="btn mt-3">Naar producten</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <form method="POST" action="cart.php">
                    <div class="cart-items">
                        <?php foreach ($items as $item): ?>
                            <div class="cart-item">
                                <img src="<?php echo escape($item['image_url']); ?>" 
                                     alt="<?php echo escape($item['name']); ?>" 
                                     class="cart-item-image">
                                
                                <div class="cart-item-info">
                                    <div class="cart-item-name"><?php echo escape($item['name']); ?></div>
                                    <div class="cart-item-price"><?php echo formatPrice($item['price']); ?> per stuk</div>
                                </div>
                                
                                <div class="cart-item-quantity">
                                    <label>Aantal:</label>
                                    <input type="number" 
                                           name="quantities[<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="0" 
                                           max="<?php echo $item['stock']; ?>">
                                    <span>(max: <?php echo $item['stock']; ?>)</span>
                                </div>
                                
                                <div>
                                    <strong><?php echo formatPrice($item['subtotal']); ?></strong>
                                </div>
                                
                                <form method="POST" action="cart.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger btn-small">
                                        Verwijder
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                        <button type="submit" name="update_cart" class="btn">
                            Winkelmandje bijwerken
                        </button>
                        <button type="submit" name="clear_cart" class="btn btn-danger" 
                                onclick="return confirm('Weet je zeker dat je het winkelmandje wilt legen?')">
                            Winkelmandje legen
                        </button>
                    </div>
                </form>
                
                <?php if ($discounts['discount_rule']): ?>
                    <div class="discount-info">
                         <strong><?php echo escape($discounts['discount_rule']['name']); ?></strong> toegepast: 
                        <?php echo $discounts['discount_rule']['discount_percentage']; ?>% korting 
                        (<?php echo formatPrice($discounts['discount_amount']); ?>)
                    </div>
                <?php endif; ?>
                
                <div class="cart-summary">
                    <div class="cart-summary-row">
                        <span>Subtotaal:</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    
                    <?php if ($discounts['discount_amount'] > 0): ?>
                        <div class="cart-summary-row" style="color: var(--success-color);">
                            <span>Korting:</span>
                            <span>- <?php echo formatPrice($discounts['discount_amount']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="cart-summary-row cart-summary-total">
                        <span>Totaal:</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="checkout.php" class="btn btn-success btn-full-width mt-3" style="font-size: 1.2rem;">
                            Bestelling afronden
                        </a>
                    <?php else: ?>
                        <p class="text-center mt-3">
                            <a href="customer/login.php" class="btn btn-success btn-full-width" style="font-size: 1.2rem;">
                                Inloggen om af te rekenen
                            </a>
                        </p>
                        <p class="text-center mt-2">
                            Nog geen account? <a href="customer/register.php">Registreer hier</a>
                        </p>
                    <?php endif; ?>
                </div>
                
                <?php if (getCartItemCount() >= 4): ?>
                    <div class="mt-3" style="background-color: #D4EDDA; padding: 1rem; border-radius: 5px; text-align: center;">
                        <strong> Super! Je krijgt 20% korting omdat je 4 of meer eendjes bestelt!</strong>
                    </div>
                <?php elseif (getCartItemCount() >= 1): ?>
                    <div class="mt-3" style="background-color: #FFF3CD; padding: 1rem; border-radius: 5px; text-align: center;">
                         Bestel nog <?php echo 4 - getCartItemCount(); ?> eendjes en krijg 20% korting op je hele bestelling!
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>
</body>
</html>
