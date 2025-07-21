<h1><?= h_specialchar($title ?? 'Connexion') ?></h1>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= h_specialchar($error) ?></p>
<?php endif; ?>

<section class="login">
    <form method="POST">
        <?= \CapsuleLib\Security\CsrfTokenManager::insertInput(); ?>
        <label>Nom d'utilisateur :</label>
        <input name="username" required />
        <label>Mot de passe : </label>
        <input name="password" type="password" required />
        <button type="submit">Se connecter</button>
    </form>
</section>
