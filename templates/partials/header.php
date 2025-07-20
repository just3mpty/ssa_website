<?php

declare(strict_types=1);

use App\Lang\Translate;
use CapsuleLib\Security\Authenticator;

Translate::load(default: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php'));

// Fonction utilitaire pour ajouter la classe 'active'
function isActive(string $path): string
{
    $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return $currentUri === $path ? 'active' : '';
}
?>

<header id="header">
    <a href="/" class="logo-link">
        <img src="assets/img/logoSSA.png" alt="<?= Translate::t('nav_title') ?>" class="logo">
    </a>
    <nav>
        <ul>
            <li><a href="/" class="<?= isActive('/') ?>"><?= Translate::t('nav_home') ?></a></li>
            <li><a href="/#about" class="<?= isActive('/apropos') ?>"><?= Translate::t('nav_apropos') ?></a></li>
            <li><a href="/#news" class="<?= isActive('/actualites') ?>"><?= Translate::t('nav_actualites') ?></a></li>
            <li><a href="/#agenda" class="<?= isActive('/events') ?>"><?= Translate::t('nav_agenda') ?></a></li>
            <li><a href="/projet" class="<?= isActive('/projet') ?>"><?= Translate::t('nav_project') ?></a></li>
            <li><a href="/galerie" class="<?= isActive('/galerie') ?>"><?= Translate::t('nav_galerie') ?></a></li>
            <li><a href="/#contact" class="<?= isActive('/contact') ?>"><?= Translate::t('nav_contact') ?></a></li>

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
                <li><a class="icons <?= isActive('/dashboard') ?>" href="/dashboard">
                        <img src="/assets/icons/dashboard.svg" alt="Dashboard icon">
                    </a></li>
                <li><a class="icons" href="/logout">
                        <img src="/assets/icons/logout.svg" alt="Logout icon">
                    </a></li>
            <?php else: ?>
                <li><a class="icons <?= isActive('/login') ?>" href="/login">
                        <img src="/assets/icons/login.svg" alt="Login icon">
                    </a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>