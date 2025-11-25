<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Haal product op
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit();
}

// Haal flash message op
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($product['name']); ?> - Badeendjes Shop</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo url('favicon.svg'); ?>">
    <link rel="alternate icon" href="<?php echo url('favicon.ico'); ?>">
    <link rel="apple-touch-icon" href="<?php echo url('apple-touch-icon.png'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <style>
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .product-detail-image {
            width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .product-detail-info h1 {
            text-align: left;
            margin-bottom: 1rem;
        }
        
        .product-detail-price {
            font-size: 2.5rem;
            color: var(--secondary-color);
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .quantity-selector input {
            width: 80px;
            padding: 0.75rem;
            text-align: center;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1.2rem;
        }
        
        .quantity-selector button {
            width: 40px;
            height: 40px;
            border: none;
            background-color: var(--secondary-color);
            color: white;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .quantity-selector button:hover {
            background-color: #FF8C00;
        }
        
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="<?php echo url('index.php'); ?>" class="logo"> Badeendjes Shop</a>
            <ul class="nav-links">
                <li><a href="<?php echo url('index.php'); ?>">Producten</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo url('customer/account.php'); ?>">Mijn Account</a></li>
                    <li><a href="<?php echo url('customer/orders.php'); ?>">Bestellingen</a></li>
                    <li><a href="<?php echo url('customer/logout.php'); ?>">Uitloggen</a></li>
                <?php else: ?>
                    <li><a href="<?php echo url('customer/login.php'); ?>">Inloggen</a></li>
                    <li><a href="<?php echo url('customer/register.php'); ?>">Registreren</a></li>
                <?php endif; ?>
                <li class="cart-icon">
                    <a href="<?php echo url('cart.php'); ?>"> Winkelmandje
                        <?php if (getCartItemCount() > 0): ?>
                            <span class="cart-count"><?php echo getCartItemCount(); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div class="product-detail">
            <div>
                <img src="<?php echo escape($product['image_url']); ?>" 
                     alt="<?php echo escape($product['name']); ?>" 
                     class="product-detail-image">
            </div>
            
            <div class="product-detail-info">
                <h1><?php echo escape($product['name']); ?></h1>
                
                <div class="product-detail-price">
                    <?php echo formatPrice($product['price']); ?>
                </div>
                
                <div class="product-stock mb-3">
                    <?php if ($product['stock'] > $product['low_stock_threshold']): ?>
                        <span class="stock-available" style="font-size: 1.1rem;">✓ Op voorraad (<?php echo $product['stock']; ?> stuks beschikbaar)</span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="stock-low" style="font-size: 1.1rem;">⚠ Nog maar <?php echo $product['stock']; ?> beschikbaar!</span>
                    <?php else: ?>
                        <span class="stock-out" style="font-size: 1.1rem;">✗ Momenteel uitverkocht</span>
                    <?php endif; ?>
                </div>
                
                <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 2rem;">
                    <?php echo nl2br(escape($product['description'])); ?>
                </p>
                
                <?php if ($product['stock'] > 0): ?>
                    <form method="POST" action="cart_add.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="quantity-selector">
                            <label style="font-weight: bold; font-size: 1.1rem;">Aantal:</label>
                            <button type="button" onclick="changeQuantity(-1)">-</button>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="1" 
                                   min="1" 
                                   max="<?php echo $product['stock']; ?>" 
                                   required>
                            <button type="button" onclick="changeQuantity(1)">+</button>
                        </div>
                        
                        <button type="submit" class="btn btn-success" style="font-size: 1.2rem; padding: 1rem 2rem;">
                             Toevoegen aan winkelmandje
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn" disabled style="background-color: #ccc; cursor: not-allowed; font-size: 1.2rem; padding: 1rem 2rem;">
                        Momenteel niet beschikbaar
                    </button>
                <?php endif; ?>
                
                <div style="margin-top: 2rem; padding: 1rem; background-color: var(--light-bg); border-radius: 5px;">
                    <p style="margin: 0;"><strong> Tip:</strong> Bestel 4 of meer eendjes en krijg automatisch 20% korting!</p>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="<?php echo url('index.php'); ?>" class="btn">← Terug naar alle producten</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>

    <script>
        function changeQuantity(delta) {
            const input = document.getElementById('quantity');
            let newValue = parseInt(input.value) + delta;
            
            if (newValue < 1) newValue = 1;
            if (newValue > <?php echo $product['stock']; ?>) newValue = <?php echo $product['stock']; ?>;
            
            input.value = newValue;
        }
    </script>
</body>
</html>
