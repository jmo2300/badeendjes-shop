<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Verwijder sessie
unset($_SESSION['customer_id']);

setFlashMessage('success', 'Je bent uitgelogd');
redirect('index.php');
?>
