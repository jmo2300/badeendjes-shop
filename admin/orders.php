<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin login
if (!isAdminLoggedIn()) {
    redirect('admin/login.php');
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $allowed_statuses = ['nieuw', 'verwerkt', 'verzonden'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        setFlashMessage('success', 'Status bijgewerkt naar: ' . $new_status);
        redirect('admin/orders.php');
    }
}

// Filters
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_date = isset($_GET['date']) ? $_GET['date'] : 'all';

// Build query
$query = "
    SELECT o.*, c.name as customer_name, c.email as customer_email,
           COUNT(oi.id) as item_count
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE 1=1
";
$params = [];

if ($filter_status !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $filter_status;
}

if ($filter_date !== 'all') {
    $days_ago = (int)$filter_date;
    $query .= " AND o.order_date >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params[] = $days_ago;
}

$query .= " GROUP BY o.id ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellingen - Admin</title>
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
        <h1>Bestellingen Overzicht</h1>
        
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem;">
            <form method="GET" action="orders.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                    <label>Status:</label>
                    <select name="status" style="width: 100%;">
                        <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>Alle statussen</option>
                        <option value="nieuw" <?php echo $filter_status == 'nieuw' ? 'selected' : ''; ?>>Nieuw</option>
                        <option value="verwerkt" <?php echo $filter_status == 'verwerkt' ? 'selected' : ''; ?>>Verwerkt</option>
                        <option value="verzonden" <?php echo $filter_status == 'verzonden' ? 'selected' : ''; ?>>Verzonden</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                    <label>Periode:</label>
                    <select name="date" style="width: 100%;">
                        <option value="all" <?php echo $filter_date == 'all' ? 'selected' : ''; ?>>Alle datums</option>
                        <option value="1" <?php echo $filter_date == '1' ? 'selected' : ''; ?>>Laatste dag</option>
                        <option value="7" <?php echo $filter_date == '7' ? 'selected' : ''; ?>>Laatste week</option>
                        <option value="30" <?php echo $filter_date == '30' ? 'selected' : ''; ?>>Laatste maand</option>
                        <option value="90" <?php echo $filter_date == '90' ? 'selected' : ''; ?>>Laatste 3 maanden</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Filteren</button>
                <a href="orders.php" class="btn" style="background-color: #6c757d;">Reset</a>
            </form>
        </div>

        <!-- Bestellingen tabel -->
        <div style="background: white; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <h2>Geen bestellingen gevonden</h2>
                    <p>Er zijn geen bestellingen die overeenkomen met de filters.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Bestelnr</th>
                            <th>Klant</th>
                            <th>Datum</th>
                            <th>Items</th>
                            <th>Totaal</th>
                            <th>Korting</th>
                            <th>Status</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td>
                                    <?php echo escape($order['customer_name']); ?><br>
                                    <small style="color: #666;"><?php echo escape($order['customer_email']); ?></small>
                                </td>
                                <td><?php echo date('d-m-Y H:i', strtotime($order['order_date'])); ?></td>
                                <td><?php echo $order['item_count']; ?> stuks</td>
                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                <td>
                                    <?php if ($order['discount_applied'] > 0): ?>
                                        <span style="color: var(--success-color);">
                                            -<?php echo formatPrice($order['discount_applied']); ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="orders.php" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" 
                                                style="padding: 0.25rem 0.5rem; border-radius: 5px; border: 1px solid var(--border-color);">
                                            <option value="nieuw" <?php echo $order['status'] == 'nieuw' ? 'selected' : ''; ?>>Nieuw</option>
                                            <option value="verwerkt" <?php echo $order['status'] == 'verwerkt' ? 'selected' : ''; ?>>Verwerkt</option>
                                            <option value="verzonden" <?php echo $order['status'] == 'verzonden' ? 'selected' : ''; ?>>Verzonden</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-small">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Statistieken -->
        <div style="margin-top: 2rem; background: var(--light-bg); padding: 1.5rem; border-radius: 10px;">
            <strong>Totaal getoond:</strong> <?php echo count($orders); ?> bestellingen
            <?php if (!empty($orders)): ?>
                | <strong>Totale waarde:</strong> <?php echo formatPrice(array_sum(array_column($orders, 'total_price'))); ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Badeendjes Shop - Admin Panel</p>
    </footer>
</body>
</html>
