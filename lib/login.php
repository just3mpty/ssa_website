
<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php'; // adapte le chemin selon ta structure

$username = 'admin'; // adapte si tu as choisi un autre login
$password = 'admin'; // met ici le vrai mdp que tu veux tester

$path_db = "/../data/ssapays.db";
$db = "sqlite:";
$pdo = get_pdo($path_db, $db);

$stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "Échec : admin inconnu.\n";
    exit(1);
}
if (password_verify($password, $row['password_hash'])) {
    echo "Connexion admin OK.\n";
} else {
    echo "Échec : mot de passe incorrect.\n";
}
