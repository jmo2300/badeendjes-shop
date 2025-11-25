<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Als al ingelogd, redirect naar account
if (isLoggedIn()) {
    redirect('customer/account.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Vul alle velden in';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();
        
        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            setFlashMessage('success', 'Welkom terug, ' . $customer['name'] . '!');
            
            // Redirect naar checkout als er items in winkelmandje zijn
            if (getCartItemCount() > 0) {
                redirect('checkout.php');
            } else {
                redirect('customer/account.php');
            }
        } else {
            $error = 'Onjuist e-mailadres of wachtwoord';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - Badeendjes Shop</title>
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
                <li><a href="login.php">Inloggen</a></li>
                <li><a href="register.php">Registreren</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h2>Inloggen</h2>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error">
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">E-mailadres:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo escape($_POST['email'] ?? ''); ?>" 
                           required 
                           autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Wachtwoord:</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-success btn-full-width">
                    Inloggen
                </button>
            </form>
            
            <p class="text-center mt-3">
                Nog geen account? <a href="register.php">Registreer hier</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>
</body>
</html>
