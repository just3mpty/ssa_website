<h1>Connexion</h1>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Nom d'utilisateur : <input name="username" required></label><br>
    <label>Mot de passe : <input name="password" type="password" required></label><br>
    <button type="submit">Se connecter</button>
</form>
