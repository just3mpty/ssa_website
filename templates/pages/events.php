<!-- templates/pages/events.php -->
<h1>Liste des événements à venir</h1>

<!-- templates/pages/events.php -->
<?php if ($isAdmin): ?>
    <a href="/events/create" class="btn">Créer un événement</a>
<?php endif; ?>
<!-- Boucle sur $events ici -->
<?php if (empty($events)): ?>
    <p>Aucun événement à venir.</p>
<?php else: ?>
    <ul>
        <?php foreach ($events as $event): ?>
            <li>
                <strong><?= htmlspecialchars($event['titre']) ?></strong><br>
                <?= nl2br(htmlspecialchars($event['description'])) ?><br>
                Date : <?= htmlspecialchars($event['date_event']) ?><br>
                Lieu : <?= htmlspecialchars($event['lieu']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
