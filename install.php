<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installatie Checker - Badeendjes Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            padding: 2rem;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
        }
        .check-item {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .check-item.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .check-item.warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .status {
            font-weight: bold;
            font-size: 1.2rem;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #FFA500;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #FF8C00;
        }
        .credentials {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> Badeendjes Shop - Installatie Checker</h1>
        
        <?php
        $checks = [];
        $all_good = true;
        
        // PHP versie check
        $php_version = phpversion();
        $php_ok = version_compare($php_version, '7.4.0', '>=');
        $checks[] = [
            'name' => 'PHP Versie',
            'status' => $php_ok ? 'success' : 'error',
            'message' => $php_ok ? "✓ PHP $php_version (OK)" : "✗ PHP $php_version (Minimaal 7.4 vereist)",
        ];
        if (!$php_ok) $all_good = false;
        
        // PDO check
        $pdo_ok = extension_loaded('pdo') && extension_loaded('pdo_mysql');
        $checks[] = [
            'name' => 'PDO MySQL',
            'status' => $pdo_ok ? 'success' : 'error',
            'message' => $pdo_ok ? '✓ PDO MySQL extensie geïnstalleerd' : '✗ PDO MySQL extensie niet gevonden',
        ];
        if (!$pdo_ok) $all_good = false;
        
        // Config bestand check
        $config_exists = file_exists('includes/config.php');
        $checks[] = [
            'name' => 'Config Bestand',
            'status' => $config_exists ? 'success' : 'error',
            'message' => $config_exists ? '✓ Config bestand bestaat' : '✗ Config bestand niet gevonden',
        ];
        if (!$config_exists) $all_good = false;
        
        // Mappen check
        $dirs_ok = true;
        $required_dirs = ['assets/images', 'assets/css', 'includes', 'admin', 'customer'];
        foreach ($required_dirs as $dir) {
            if (!is_dir($dir)) {
                $dirs_ok = false;
                break;
            }
        }
        $checks[] = [
            'name' => 'Mappen Structuur',
            'status' => $dirs_ok ? 'success' : 'warning',
            'message' => $dirs_ok ? '✓ Alle mappen aanwezig' : '⚠ Sommige mappen ontbreken',
        ];
        
        // Database connectie check
        if ($config_exists) {
            try {
                require_once 'includes/config.php';
                $checks[] = [
                    'name' => 'Database Connectie',
                    'status' => 'success',
                    'message' => '✓ Database verbinding succesvol',
                ];
                
                // Check of tabellen bestaan
                $tables = ['products', 'customers', 'orders', 'order_items', 'discount_rules', 'admin_users'];
                $missing_tables = [];
                foreach ($tables as $table) {
                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() == 0) {
                        $missing_tables[] = $table;
                    }
                }
                
                if (empty($missing_tables)) {
                    $checks[] = [
                        'name' => 'Database Tabellen',
                        'status' => 'success',
                        'message' => '✓ Alle tabellen aanwezig',
                    ];
                } else {
                    $checks[] = [
                        'name' => 'Database Tabellen',
                        'status' => 'error',
                        'message' => '✗ Ontbrekende tabellen: ' . implode(', ', $missing_tables),
                    ];
                    $all_good = false;
                }
                
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'Database Connectie',
                    'status' => 'error',
                    'message' => '✗ Database connectie mislukt: ' . $e->getMessage(),
                ];
                $all_good = false;
            }
        }
        
        // Display checks
        foreach ($checks as $check) {
            echo "<div class='check-item {$check['status']}'>";
            echo "<span><strong>{$check['name']}:</strong> {$check['message']}</span>";
            echo "</div>";
        }
        ?>
        
        <?php if ($all_good): ?>
            <div class="info-box" style="background: #d4edda; border-color: #c3e6cb;">
                <h3 style="color: #155724; margin-bottom: 1rem;">✓ Installatie Compleet!</h3>
                <p style="margin-bottom: 1rem;">Alle checks zijn succesvol. Je webshop is klaar voor gebruik.</p>
                
                <div class="credentials">
                    <strong>Admin Login:</strong><br>
                    URL: <a href="admin/login.php">admin/login.php</a><br>
                    Gebruikersnaam: admin<br>
                    Wachtwoord: admin123
                </div>
                
                <div class="credentials">
                    <strong>Test Klant Account:</strong><br>
                    Email: <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="364253454276534e575b465a531855595b">[email&#160;protected]</a><br>
                    Wachtwoord: test123
                </div>
                
                <p style="margin-top: 1rem; color: #721c24; background: #f8d7da; padding: 0.5rem; border-radius: 3px;">
                     <strong>BELANGRIJK:</strong> Wijzig het admin wachtwoord na eerste login!
                </p>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="index.php" class="btn">→ Naar de Webshop</a>
                    <a href="admin/login.php" class="btn" style="background: #333;">→ Naar Admin Panel</a>
                </div>
            </div>
        <?php else: ?>
            <div class="info-box" style="background: #f8d7da; border-color: #f5c6cb;">
                <h3 style="color: #721c24; margin-bottom: 1rem;">⚠ Installatie Incompleet</h3>
                <p style="margin-bottom: 1rem;">Er zijn problemen gevonden. Volg deze stappen:</p>
                <ol style="margin-left: 1.5rem; line-height: 1.8;">
                    <li>Controleer of alle bestanden correct geüpload zijn</li>
                    <li>Pas database credentials aan in <code>includes/config.php</code></li>
                    <li>Importeer <code>database_step1_user.sql</code> in je database</li>
                    <li>Importeer <code>database_step2_schema.sql</code> in je database</li>
                    <li>Importeer <code>database_step3_seed.sql</code> voor initiële data</li>
                    <li>Herlaad deze pagina om opnieuw te checken</li>
                </ol>
                
                <p style="margin-top: 1rem;">
                    Zie <strong>README.md</strong> voor gedetailleerde installatie instructies.
                </p>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h3 style="margin-bottom: 1rem;">📚 Snelle Links</h3>
            <ul style="margin-left: 1.5rem; line-height: 2;">
                <li><a href="README.md">Lees de README</a></li>
                <li><a href="database_step1_user.sql">Database User</a></li>
                <li><a href="database_step2_schema.sql">Database Schema</a></li>
                <li><a href="database_step3_seed.sql">Database Seed Data</a></li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 2rem; color: #666; font-size: 0.9rem;">
            <p> Badeendjes Shop v1.0</p>
            <p style="margin-top: 0.5rem;">
                Na succesvolle install
