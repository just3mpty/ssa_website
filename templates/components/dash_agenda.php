<section class='agenda'>
  <h1>Mon agenda</h1>

  <button id='btn-open-modal'>Nouv. Event</button>
  <div id="modalCreateEvent">
    <form method="POST" action="/dashboard/agenda/create">
      <table>
        <tr>
          <th><h2>Créer un Event</h2></th>
          <th><button id='closeModal'>X</button></th>
        <tr>
      </table>
      <table>
        <label>Intitulé</label>
        <input type="text" name="titre" placeholder="..." required>
        
        <label>Date</label>
        <input type="date" name="date" required>
        
        <label>Horaire</label>
        <input type="time" name="heure" required>

        <label>Lieu</label>
        <input type="text" name="lieu" placeholder="..." required>
      </table>
      <button type="submit">Créer l'évenement</button>
    </form>
  </div>

  <?php
// Définir les variables par défaut si elles ne sont pas déjà définies
$days = isset($days) ? $days : ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$hours = isset($hours) ? $hours : range(8, 18); // Heures pleines de 8h à 18h

// Date actuelle (utilisez la date fournie : 22-09-2025)
$currentDate = '22-09-2025'; // Remplacez par date('d-m-Y') pour la date réelle du serveur

// Récupérer la date de référence pour la semaine via GET (par défaut : lundi de la semaine actuelle)
$weekStart = isset($_GET['week']) ? $_GET['week'] : $currentDate;

// Calculer le lundi de la semaine
$dateTime = DateTime::createFromFormat('d-m-Y', $weekStart);
if ($dateTime === false) {
    $dateTime = new DateTime(); // Fallback à la date actuelle si invalide
} else {
    // Ajuster au lundi de la semaine
    $weekday = $dateTime->format('N'); // 1 = Lundi, 7 = Dimanche
    if ($weekday != 1) {
        $dateTime->modify('-' . ($weekday - 1) . ' days');
    }
}
$monday = $dateTime->format('d-m-Y');

// Générer les dates pour chaque jour de la semaine
$weekDays = [];
foreach ($days as $index => $day) {
    $dayDate = clone $dateTime;
    $dayDate->modify('+' . $index . ' days');
    $weekDays[$day] = $dayDate->format('d-m-Y');
}

// Calculer les dates pour la navigation
$previousMonday = (clone $dateTime)->modify('-7 days')->format('d-m-Y');
$nextMonday = (clone $dateTime)->modify('+7 days')->format('d-m-Y');

// Exemple de données d'événements (avec demi-heures)
$events = isset($events) ? $events : [
    [
        'date' => '22-09-2025', // Lundi
        'heure' => '09:00',
        'titre' => 'Réunion d\'équipe',
        'lieu' => 'Salle 101'
    ],
    [
        'date' => '23-09-2025', // Mardi
        'heure' => '14:30', // Demi-heure
        'titre' => 'Point client',
        'lieu' => 'Bureau 202'
    ],
    [
        'date' => '29-09-2025', // Lundi suivant
        'heure' => '10:30', // Demi-heure
        'titre' => 'Formation',
        'lieu' => 'Salle 303'
    ]
];

// Organiser les événements par date et heure pleine
$organizedEvents = [];
foreach ($events as $event) {
    $date = $event['date'];
    if (in_array($date, $weekDays)) { // Filtrer seulement les événements de la semaine en cours
        // Extraire l'heure pleine (ex: 9:30 -> 9)
        $hour = (int)substr($event['heure'], 0, 2);
        $isHalfHour = substr($event['heure'], 3, 2) === '30'; // Vérifier si c'est une demi-heure
        $organizedEvents[$date][$hour][] = [
            'titre' => $event['titre'],
            'lieu' => $event['lieu'],
            'date' => $event['date'],
            'heure' => $event['heure'],
            'isHalfHour' => $isHalfHour
        ];
    }
}
?>

<div class="navigation">
    <a href="?week=<?php echo urlencode($previousMonday); ?>">&lt; Semaine précédente</a>
    <span>Semaine du <?php echo htmlspecialchars($monday); ?></span>
    <a href="?week=<?php echo urlencode($nextMonday); ?>">Semaine suivante &gt;</a>
</div>

<table>
    <thead>
        <tr>
            <th>Heure</th>
            <?php if (!empty($days)): ?>
                <?php foreach ($days as $day): ?>
                    <th><?php echo htmlspecialchars($day); ?><br>
                    (<?php echo htmlspecialchars($weekDays[$day]); ?>)</th>
                <?php endforeach; ?>
            <?php else: ?>
                <th colspan="1">Aucun jour défini</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($hours)): ?>
            <?php foreach ($hours as $hour): ?>
                <tr>
                    <td class="time-slot"><?php echo sprintf("%02d", $hour); ?>:00</td>
                    <?php foreach ($days as $day): ?>
                        <?php $date = $weekDays[$day];?>
                        <td <?php echo isset($organizedEvents[$date][$hour]) ? 'class="event"' : ''; ?>>
                            <?php if (isset($organizedEvents[$date][$hour])): 
                                foreach ($organizedEvents[$date][$hour] as $event): ?>
                                    <div class="<?php echo $event['isHalfHour'] ? 'event-half-hour' : 'event-whole-hour'; ?>">
                                        <strong><?php echo htmlspecialchars($event['titre']); ?></strong><br>
                                        (<?php echo htmlspecialchars($event['date'] . ' ' . $event['heure']); ?>)
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo count($days) + 1; ?>">Aucune heure définie</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</section>