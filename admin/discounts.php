<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin login
if (!isAdminLoggedIn()) {
    redirect('admin/login.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_discount'])) {
        $name = trim($_POST['name']);
        $type = $_POST['type'];
        $min_quantity = (int)$_POST['min_quantity'];
        $discount_percentage = (float)$_POST['discount_percentage'];
        $active = isset($_POST['active']) ? 1 : 0;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        
        $stmt = $pdo->prepare("
            INSERT INTO discount_rules (name, type, min_quantity, discount_percentage, active, start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $type, $min_quantity, $discount_percentage, $active, $start_date, $end_date]);
        
        setFlashMessage('success', 'Kortingsregel toegevoegd');
        redirect('admin/discounts.php');
    }
    
    if (isset($_POST['toggle_active'])) {
        $discount_id = (int)$_POST['discount_id'];
        $stmt = $pdo->prepare("UPDATE discount_rules SET active = NOT active WHERE id = ?");
        $stmt->execute([$discount_id]);
        setFlashMessage('success', 'Status bijgewerkt');
        redirect('admin/discounts.php');
    }
    
    if (isset($_POST['delete_discount'])) {
        $discount_id = (int)$_POST['discount_id'];
        $stmt = $pdo->prepare("DELETE FROM discount_rules WHERE id = ?");
        $stmt->execute([$discount_id]);
        setFlashMessage('success', 'Kortingsregel verwijderd');
        redirect('admin/discounts.php');
    }
}

// Haal alle kortingsregels op
$stmt = $pdo->query("SELECT * FROM discount_rules ORDER BY active DESC, created_at DESC");
$discounts = $stmt->fetchAll();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kortingsbeheer - Admin</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="alternate icon" href="../favicon.ico">
    <link rel="apple-touch-icon" href="../apple-touch-icon.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-header">
        <nav style="max-width: 1200px; margin: 0 auto; padding: 0 2rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="dashboard.php" class="logo"> Admin Panel</a>
            <ul class="admin-nav">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Producten</a></li>
                <li><a href="orders.php">Bestellingen</a></li>
                <li><a href="discounts.php">Kortingen</a></li>
                <li><a href="statistics.php">Statistieken</a></li>
                <li><a href="../index.php">Naar Shop</a></li>
                <li><a href="logout.php">Uitloggen</a></li>
            </ul>
        </nav>
    </div>

    <main>
        <h1>Kortingsbeheer</h1>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Nieuwe kortingsregel toevoegen -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 2rem; margin-bottom: 2rem;">
            <h2>Nieuwe Kortingsregel Toevoegen</h2>
            
            <form method="POST" action="discounts.php">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="name">Naam van de korting:</label>
                        <input type="text" id="name" name="name" required 
                               placeholder="Bijv: Zomeractie 2024">
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type korting:</label>
                        <select id="type" name="type" required>
                            <option value="quantity">Aantal-gebaseerd</option>
                            <option value="seasonal">Seizoens</option>
                            <option value="combo">Combinatie</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="min_quantity">Minimum aantal:</label>
                        <input type="number" id="min_quantity" name="min_quantity" 
                               value="0" min="0" required>
                        <small>0 = geen minimum</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="discount_percentage">Kortingspercentage:</label>
                        <input type="number" id="discount_percentage" name="discount_percentage" 
                               min="0" max="100" step="0.01" required 
                               placeholder="Bijv: 20.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="start_date">Startdatum (optioneel):</label>
                        <input type="date" id="start_date" name="start_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">Einddatum (optioneel):</label>
                        <input type="date" id="end_date" name="end_date">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="active" checked>
                        Direct activeren
                    </label>
                </div>
                
                <button type="submit" name="add_discount" class="btn btn-success">
                    Kortingsregel Toevoegen
                </button>
            </form>
        </div>

        <!-- Bestaande kortingsregels -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
            <div style="padding: 2rem; border-bottom: 1px solid var(--border-color);">
                <h2>Bestaande Kortingsregels</h2>
            </div>
            
            <?php if (empty($discounts)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎫</div>
                    <h3>Nog geen kortingsregels</h3>
                    <p>Voeg hierboven je eerste kortingsregel toe.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <th>Type</th>
                            <th>Min. Aantal</th>
                            <th>Korting %</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discounts as $discount): ?>
                            <tr style="<?php echo !$discount['active'] ? 'opacity: 0.6;' : ''; ?>">
                                <td><strong><?php echo escape($discount['name']); ?></strong></td>
                                <td>
                                    <?php 
                                        $types = [
                                            'quantity' => 'Aantal-gebaseerd',
                                            'seasonal' => 'Seizoens',
                                            'combo' => 'Combinatie'
                                        ];
                                        echo $types[$discount['type']];
                                    ?>
                                </td>
                                <td><?php echo $discount['min_quantity']; ?></td>
                                <td><strong><?php echo $discount['discount_percentage']; ?>%</strong></td>
                                <td>
                                    <?php if ($discount['start_date'] || $discount['end_date']): ?>
                                        <?php echo $discount['start_date'] ? date('d-m-Y', strtotime($discount['start_date'])) : '...'; ?>
                                        <br>tot<br>
                                        <?php echo $discount['end_date'] ? date('d-m-Y', strtotime($discount['end_date'])) : '...'; ?>
                                    <?php else: ?>
                                        Onbeperkt
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($discount['active']): ?>
                                        <span style="color: var(--success-color); font-weight: bold;">✓ Actief</span>
                                    <?php else: ?>
                                        <span style="color: #999;">✗ Inactief</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="discounts.php" style="display: inline;">
                                        <input type="hidden" name="discount_id" value="<?php echo $discount['id']; ?>">
                                        <button type="submit" name="toggle_active" class="btn btn-small" 
                                                style="<?php echo $discount['active'] ? 'background-color: #6c757d;' : 'background-color: var(--success-color);'; ?>">
                                            <?php echo $discount['active'] ? 'Deactiveren' : 'Activeren'; ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="discounts.php" style="display: inline;" 
                                          onsubmit="return confirm('Weet je zeker dat je deze kortingsregel wilt verwijderen?');">
                                        <input type="hidden" name="discount_id" value="<?php echo $discount['id']; ?>">
                                        <button type="submit" name="delete_discount" class="btn btn-danger btn-small">
                                            Verwijder
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop - Admin Panel</p>
    </footer>
</body>
</html>
