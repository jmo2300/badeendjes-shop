<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Als al ingelogd, redirect naar account
if (isLoggedIn()) {
    redirect('customer/account.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validatie
    if (empty($name)) {
        $errors[] = 'Naam is verplicht';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geldig e-mailadres is verplicht';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Wachtwoord moet minimaal 6 karakters zijn';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'Wachtwoorden komen niet overeen';
    }
    
    // Check of email al bestaat
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Dit e-mailadres is al geregistreerd';
        }
    }
    
    // Registreer gebruiker
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashed_password])) {
            $_SESSION['customer_id'] = $pdo->lastInsertId();
            setFlashMessage('success', 'Account succesvol aangemaakt! Welkom ' . $name);
            redirect('index.php');
        } else {
            $errors[] = 'Er ging iets mis bij het aanmaken van je account';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren - Badeendjes Shop</title>
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
            <h2>Registreren</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo escape($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">Naam:</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?php echo escape($_POST['name'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mailadres:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo escape($_POST['email'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Wachtwoord:</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           minlength="6" 
                           required>
                    <small>Minimaal 6 karakters</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Bevestig wachtwoord:</label>
                    <input type="password" 
                           id="password_confirm" 
                           name="password_confirm" 
                           required>
                </div>
                
                <button type="submit" class="btn btn-success btn-full-width">
                    Registreren
                </button>
            </form>
            
            <p class="text-center mt-3">
                Al een account? <a href="login.php">Log hier in</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>
</body>
</html>
