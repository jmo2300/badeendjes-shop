<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Als al ingelogd als admin, redirect naar dashboard
if (isAdminLoggedIn()) {
    redirect('admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vul alle velden in';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('admin/dashboard.php');
        } else {
            $error = 'Onjuiste gebruikersnaam of wachtwoord';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Badeendjes Shop</title>
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
                <li><a href="../index.php">Terug naar shop</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h2> Admin Login</h2>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error">
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Gebruikersnaam:</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo escape($_POST['username'] ?? ''); ?>" 
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
            
            <div style="margin-top: 2rem; padding: 1rem; background-color: var(--light-bg); border-radius: 5px; font-size: 0.9rem;">
                <strong>Demo credentials:</strong><br>
                Gebruikersnaam: admin<br>
                Wachtwoord: admin123
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop. Alle rechten voorbehouden.</p>
    </footer>
</body>
</html>
