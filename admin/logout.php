<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Verwijder admin sessie
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

setFlashMessage('success', 'Je bent uitgelogd');
redirect('admin/login.php');
?>
