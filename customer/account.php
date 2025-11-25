<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check of ingelogd
if (!isLoggedIn()) {
    redirect('customer/login.php');
}

$customer = getCurrentCustomer($pdo);
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Account - Badeendjes Shop</title>
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
        <h1>Mijn Account</h1>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div style="max-width: 600px; margin: 0 auto;">
            <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem;">
                <h2>Account Informatie</h2>
                
                <div style="margin-bottom: 1.5rem;">
                    <strong>Naam:</strong><br>
                    <?php echo escape($customer['name']); ?>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <strong>E-mailadres:</strong><br>
                    <?php echo escape($customer['email']); ?>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <strong>Lid sinds:</strong><br>
                    <?php echo date('d-m-Y', strtotime($customer['created_at'])); ?>
                </div>
                
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="orders.php" class="btn btn-primary">
                         Mijn Bestellingen
                    </a>
                    <a href="../index.php" class="btn">
                         Verder Winkelen
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>
</body>
</html>
