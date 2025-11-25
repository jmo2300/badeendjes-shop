<?php
// Helper functies voor de webshop

/**
 * Escape output voor XSS bescherming
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Genereer URL pad relatief aan de root directory
 */
function url($path = '') {
    // Bepaal het basis pad van de applicatie
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_path = str_replace('\\', '/', dirname($script_name));
    
    // Als we in admin of customer folder zitten, ga naar parent
    if (basename($base_path) === 'admin' || basename($base_path) === 'customer') {
        $base_path = dirname($base_path);
    }
    
    // Normaliseer paden
    $base_path = rtrim($base_path, '/');
    $path = ltrim($path, '/');
    
    if (empty($path)) {
        return $base_path . '/';
    }
    
    return $base_path . '/' . $path;
}

/**
 * Redirect naar een andere pagina
 */
function redirect($url) {
    // Als het al een volledige URL is, gebruik die
    if (strpos($url, 'http') === 0) {
        header("Location: " . $url);
    } else {
        // Gebruik url() helper voor relatieve paden
        header("Location: " . url($url));
    }
    exit();
}

/**
 * Controleer of gebruiker is ingelogd
 */
function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}

/**
 * Controleer of admin is ingelogd
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Haal huidige klant op
 */
function getCurrentCustomer($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    return $stmt->fetch();
}

/**
 * Formatteer prijs
 */
function formatPrice($price) {
    return '€ ' . number_format($price, 2, ',', '.');
}

/**
 * Haal winkelmandje op uit sessie
 */
function getCart() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

/**
 * Voeg product toe aan winkelmandje
 */
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

/**
 * Update product aantal in winkelmandje
 */
function updateCartQuantity($product_id, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($product_id);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

/**
 * Verwijder product uit winkelmandje
 */
function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

/**
 * Leeg winkelmandje
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Bereken totaal aantal items in winkelmandje
 */
function getCartItemCount() {
    $cart = getCart();
    return array_sum($cart);
}

/**
 * Haal winkelmandje items op met productinformatie
 */
function getCartItems($pdo) {
    $cart = getCart();
    if (empty($cart)) {
        return [];
    }
    
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    
    $items = [];
    foreach ($products as $product) {
        $product['quantity'] = $cart[$product['id']];
        $product['subtotal'] = $product['price'] * $product['quantity'];
        $items[] = $product;
    }
    
    return $items;
}

/**
 * Bereken subtotaal van winkelmandje
 */
function getCartSubtotal($pdo) {
    $items = getCartItems($pdo);
    $subtotal = 0;
    
    foreach ($items as $item) {
        $subtotal += $item['subtotal'];
    }
    
    return $subtotal;
}

/**
 * Haal actieve kortingsregels op
 */
function getActiveDiscounts($pdo) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT * FROM discount_rules 
        WHERE active = 1 
        AND (start_date IS NULL OR start_date <= ?)
        AND (end_date IS NULL OR end_date >= ?)
        ORDER BY discount_percentage DESC
    ");
    $stmt->execute([$today, $today]);
    return $stmt->fetchAll();
}

/**
 * Bereken kortingen
 */
function calculateDiscounts($pdo) {
    $items = getCartItems($pdo);
    $totalQuantity = getCartItemCount();
    $subtotal = getCartSubtotal($pdo);
    $discounts = getActiveDiscounts($pdo);
    
    $bestDiscount = 0;
    $appliedRule = null;
    
    foreach ($discounts as $rule) {
        $discount = 0;
        
        if ($rule['type'] == 'quantity' && $totalQuantity >= $rule['min_quantity']) {
            $discount = $subtotal * ($rule['discount_percentage'] / 100);
        } elseif ($rule['type'] == 'seasonal') {
            $discount = $subtotal * ($rule['discount_percentage'] / 100);
        }
        
        if ($discount > $bestDiscount) {
            $bestDiscount = $discount;
            $appliedRule = $rule;
        }
    }
    
    return [
        'discount_amount' => $bestDiscount,
        'discount_rule' => $appliedRule
    ];
}

/**
 * Bereken totaalprijs inclusief kortingen
 */
function getCartTotal($pdo) {
    $subtotal = getCartSubtotal($pdo);
    $discounts = calculateDiscounts($pdo);
    return $subtotal - $discounts['discount_amount'];
}

/**
 * Controleer of er voldoende voorraad is
 */
function checkStock($pdo, $product_id, $quantity) {
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    return $product && $product['stock'] >= $quantity;
}

/**
 * Haal producten met lage voorraad op
 */
function getLowStockProducts($pdo) {
    $stmt = $pdo->query("
        SELECT * FROM products 
        WHERE stock <= low_stock_threshold 
        ORDER BY stock ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Bereken totale voorraadwaarde
 */
function getTotalStockValue($pdo) {
    $stmt = $pdo->query("SELECT SUM(stock * price) as total FROM products");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Flash message systeem
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * CSRF Token generatie
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
