<section class="form-section">
    <h2>Créer un événement</h2>
    <form method="post" action="/events/create" autocomplete="off" class="event-form">
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
            Date et heure<br>
            <input type="datetime-local" name="date_event" required>
        </label><br><br>
        <label>
            Lieu<br>
            <input type="text" name="lieu" required maxlength="100">
        </label><br><br>
        <!-- Optionnel : image à gérer plus tard
        <label>
            Image<br>
            <input type="file" name="image" accept="image/*">
        </label><br><br>
        -->
        <button type="submit">Créer</button>
    </form>
</section>
