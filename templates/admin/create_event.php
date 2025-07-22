<section class="form-section">
    <h2>Créer un événement</h2>
    <form method="post" action="/events/create" enctype="multipart/form-data" autocomplete="off" class="event-form">
        <?= \CapsuleLib\Security\CsrfTokenManager::insertInput(); ?>
        <label>
            Titre
        </label>
        <input type="text" name="titre" required maxlength="100" autofocus>
        <label>
            Description
        </label>
        <textarea name="description" required rows="4" maxlength="1000"></textarea>
        <label>
            Date
        </label>
        <input type="date" name="date_event" required>
        <label>
            Heure
        </label>
        <input type="time" name="hours" required>
        <label>
            Lieu
        </label>
        <input type="text" name="lieu" required maxlength="100">
        <label>
            Image
        </label>
        <input type="file" name="image" accept="image/*">
        <button type="submit">Créer</button>
    </form>
</section>