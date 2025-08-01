<section class="account">
    <h1>Mon compte</h1>

    <!-- display username, email, role -->

    <p><?= $user['username'] ?></p>
    <p><?= $user['role'] ?></p>
    <p><?= $user['email'] ?></p>

    <button id="changePasswordBtn">Changer mot de passe</button>
    <!-- fonction updatePassword -->
    <div id="update-password-form" class="modal">
        <form method="post" action="/update-password">
            <input type="password" name="old_password" placeholder="Ancien mot de passe" required>
            <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
            <button type="submit">Mettre à jour</button>
        </form>
    </div>
    

</section>
