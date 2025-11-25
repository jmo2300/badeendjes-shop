#!/usr/bin/env php
<?php
/**
 * Password Hash Generator
 * Gebruik dit script om wachtwoord hashes te genereren voor de database
 */

if ($argc < 2) {
    echo "Gebruik: php generate_hash.php [wachtwoord]\n";
    echo "Voorbeeld: php generate_hash.php mijnwachtwoord123\n";
    exit(1);
}

$password = $argv[1];
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "\n";
echo "=========================================\n";
echo "Wachtwoord Hash Generator\n";
echo "=========================================\n";
echo "Wachtwoord: $password\n";
echo "Hash: $hash\n";
echo "=========================================\n";
echo "\n";
echo "SQL om admin wachtwoord te updaten:\n";
echo "UPDATE admin_users SET password = '$hash' WHERE username = 'admin';\n";
echo "\n";
?>
