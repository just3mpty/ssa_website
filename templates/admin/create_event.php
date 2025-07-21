<section class="form-section">
    <br><br><br>
    <h2>Créer un événement</h2>
    <form method="post" action="/events/create" enctype="multipart/form-data" autocomplete="off" class="event-form">
        <?= \CapsuleLib\Security\CsrfTokenManager::insertInput(); ?>
        <label>
            Titre<br>
            <input type="text" name="titre" required maxlength="100" autofocus>
        </label><br><br>
        <label>
            Description<br>
            <textarea name="description" required rows="4" maxlength="1000"></textarea>
        </label><br><br>
        <label>
            Date<br>
            <input type="date" name="date_event" required>
        </label><br><br>
        <label>
            Heure<br>
            <input type="time" name="hours" required>
        </label><br><br>
        <label>
            Lieu<br>
            <input type="text" name="lieu" required maxlength="100">
        </label><br><br>
        <label>
            Image<br>
            <input type="file" name="image" accept="image/*">
        </label><br><br>
        <button type="submit">Créer</button>
    </form>
</section>
