<section class='agenda'>
  <h1>Mon agenda</h1>

  <button id='btn-open-modal'>Nouv. Event</button>
  <div id="modalCreateEvent">
    <form method="POST" action="/dashboard/agenda/create">
      <?= \CapsuleLib\Security\CsrfTokenManager::insertInput(); ?>
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
  // Exemple de données d'événements
  $events = [
    [
      'date' => '10-06-2024',
      'heure' => '09:00',
      'titre' => 'Réunion d\'équipe',
      'lieu' => 'Salle 101'
    ],
    [
      'date' => '11-06-2024',
      'heure' => '14:00',
      'titre' => 'Appel client',
      'lieu' => 'En ligne'
    ],
    [
      'date' => '22-10-2025',
      'heure' => '11:00',
      'titre' => 'Formation PHP',
      'lieu' => 'Salle 202'
    ]
  ];
  ?>

  

  <h2>Agenda Semainier</h2>
  <?php
  // Configuration des jours et heures
  $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
  $hours = range(8, 18); // Heures de 8h à 18h
  ?>
  <table>
      <thead>
    <tr>
        <th>Heure</th>
        <?php foreach ($days as $day): ?>
      <th><?php echo $day; ?></th>
        <?php endforeach; ?>
    </tr>
      </thead>
      <tbody>
    <?php foreach ($hours as $hour): ?>
        <tr>
      <td class="time-slot"><?php echo $hour; ?>:00</td>
      <?php foreach ($days as $day):
        print($events[0]['date']); ?>
          <td></td>
      <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
      </tbody>
    </table>

</section>