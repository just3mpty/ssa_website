<?php

declare(strict_types=1);

use CapsuleLib\Security\Authenticator;

$user = Authenticator::getUser();
?>

<section class="admin-dashboard">
    <h1>Tableau de bord Administrateur</h1>
    <p>Bienvenue<?= $user ? ', ' . htmlspecialchars($user['username']) : '' ?> !</p>

    <ul>
        <li><a href="/admin/events">Gérer les événements</a></li>
        <li><a href="/admin/contacts">Voir les messages de contact</a></li>
        <?php if (!empty($isAdmin)): ?>
            <li><a href="/events/create" class="btn">Créer un événement</a></li>
        <?php endif; ?>
        <!-- Ajoute d'autres liens d’administration ici -->
        <li><a href="/logout">Déconnexion</a></li>
    </ul>
</section>
