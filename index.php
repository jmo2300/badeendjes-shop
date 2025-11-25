<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Zoekfunctionaliteit
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($filter == 'in_stock') {
    $query .= " AND stock > 0";
} elseif ($filter == 'low_stock') {
    $query .= " AND stock > 0 AND stock <= low_stock_threshold";
}

$query .= " ORDER BY name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Haal flash message op
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badeendjes Shop - De leukste badeendjes voor jouw bad!</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo url('favicon.svg'); ?>">
    <link rel="alternate icon" href="<?php echo url('favicon.ico'); ?>">
    <link rel="apple-touch-icon" href="<?php echo url('apple-touch-icon.png'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
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
        <h1>Welkom bij Badeendjes Shop!</h1>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div class="search-filter">
            <form method="GET" action="index.php" class="search-box">
                <input type="text" 
                       name="search" 
                       placeholder="Zoek naar een badeendje..." 
                       value="<?php echo escape($search); ?>">
            </form>
            
            <div class="filter-select">
                <select name="filter" onchange="window.location.href='index.php?filter=' + this.value + '<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>'">
                    <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>Alle producten</option>
                    <option value="in_stock" <?php echo $filter == 'in_stock' ? 'selected' : ''; ?>>Op voorraad</option>
                    <option value="low_stock" <?php echo $filter == 'low_stock' ? 'selected' : ''; ?>>Bijna uitverkocht</option>
                </select>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"></div>
                <h2>Geen producten gevonden</h2>
                <p>Probeer een andere zoekterm of filter.</p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" onclick="window.location.href='<?php echo url('product.php?id=' . $product['id']); ?>'">
                        <img src="<?php echo escape($product['image_url']); ?>" 
                             alt="<?php echo escape($product['name']); ?>" 
                             class="product-image">
                        
                        <div class="product-info">
                            <h3 class="product-name"><?php echo escape($product['name']); ?></h3>
                            <p class="product-description">
                                <?php echo escape(substr($product['description'], 0, 100)); ?>...
                            </p>
                            <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                            
                            <div class="product-stock">
                                <?php if ($product['stock'] > $product['low_stock_threshold']): ?>
                                    <span class="stock-available">✓ Op voorraad (<?php echo $product['stock']; ?>)</span>
                                <?php elseif ($product['stock'] > 0): ?>
                                    <span class="stock-low">⚠ Nog maar <?php echo $product['stock']; ?> beschikbaar!</span>
                                <?php else: ?>
                                    <span class="stock-out">✗ Uitverkocht</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($product['stock'] > 0): ?>
                                <button class="btn btn-full-width" 
                                        onclick="event.stopPropagation(); addToCartQuick(<?php echo $product['id']; ?>)">
                                    Toevoegen aan winkelmandje
                                </button>
                            <?php else: ?>
                                <button class="btn btn-full-width" disabled style="background-color: #ccc; cursor: not-allowed;">
                                    Niet beschikbaar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (getCartItemCount() >= 4): ?>
            <div class="discount-info">
                 Je hebt <?php echo getCartItemCount(); ?> eendjes in je winkelmandje - je krijgt 20% korting bij afrekenen!
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
        <p>De leukste badeendjes voor jouw bad! </p>
    </footer>

    <script>
        function addToCartQuick(productId) {
            const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            const cartAddUrl = basePath + '/cart_add.php';
            
            fetch(cartAddUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'product_id=' + productId + '&quantity=1'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Product toegevoegd aan winkelmandje!');
                    location.reload();
                } else {
                    alert(data.message || 'Er ging iets mis');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Er ging iets mis bij het toevoegen aan het winkelmandje');
            });
        }
    </script>
</body>
</html>
