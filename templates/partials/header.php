<?php

declare(strict_types=1);

use CapsuleLib\Service\Lang\Translate;
use CapsuleLib\Security\Authenticator;

var_dump(Authenticator::isAuthenticated());
var_dump($_SESSION["admin"]);

Translate::load(default: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php'));
?>

<header id="header">
    <a href="/" class="logo-link">
        <img src="assets/img/logoSSA.png" alt="<?= Translate::t('nav_title') ?>" class="logo">
    </a>
    <nav>
        <ul>
            <li><a href="/" class="active"><?= Translate::t('nav_home') ?></a></li>
            <li><a href="/projet"><?= Translate::t('nav_project') ?></a></li>
            <li><a href="/participer"><?= Translate::t('nav_participer') ?></a></li>
            <li><a href="/actualites"><?= Translate::t('nav_actualites') ?></a></li>
            <li><a href="galerie"><?= Translate::t('nav_galerie') ?></a></li>
            <li><a href="/apropos"><?= Translate::t('nav_apropos') ?></a></li>
            <li><a href="/contact"><?= Translate::t('nav_contact') ?></a></li>
            <li><a href="/wiki"><?= Translate::t('nav_wiki') ?></a></li>
            <li>
                <form method="get" action="">
                    <select name="lang" id="lang-switch" onchange="this.form.submit()">
                        <option value="fr" <?= ($_SESSION['lang'] ?? 'fr') === 'fr' ? 'selected' : '' ?>>
                            <?= Translate::t('lang_fr') ?>
                        </option>
                        <option value="br" <?= ($_SESSION['lang'] ?? 'fr') === 'br' ? 'selected' : '' ?>>
                            <?= Translate::t('lang_br') ?>
                        </option>
                    </select>
                </form>
            </li>
            <?php if (Authenticator::isAuthenticated()): ?>
                <li><a href="/admin">Admin</a></li>
                <li><a href="/logout">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="/login">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
