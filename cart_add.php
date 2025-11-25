<?php
// Prevent any output before JSON
ob_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// For AJAX, always return JSON
if ($isAjax) {
    // Clear any previous output
    ob_clean();
    header('Content-Type: application/json');
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige request methode']);
        exit();
    }
    redirect('index.php');
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validatie
if ($product_id <= 0 || $quantity <= 0) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige product of aantal']);
        exit();
    }
    setFlashMessage('error', 'Ongeldige product of aantal');
    redirect('index.php');
}

// Check voorraad
if (!checkStock($pdo, $product_id, $quantity)) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Niet genoeg voorraad beschikbaar']);
        exit();
    }
    setFlashMessage('error', 'Niet genoeg voorraad beschikbaar voor dit product');
    redirect('index.php');
}

// Voeg toe aan winkelmandje
addToCart($product_id, $quantity);

// Response
if ($isAjax) {
    echo json_encode([
        'success' => true, 
        'cart_count' => getCartItemCount(),
        'message' => 'Product toegevoegd aan winkelmandje'
    ]);
    exit();
}

setFlashMessage('success', 'Product toegevoegd aan winkelmandje!');
redirect('cart.php');
?>
