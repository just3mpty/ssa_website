<?php

$links = [
    ['title' => 'Mon compte',     'url' => 'admin/account',  'icon' => 'account'],
    ['title' => 'Utilisateurs',   'url' => 'admin/users',    'icon' => 'users'],
    ['title' => 'Mes articles',   'url' => 'admin/articles', 'icon' => 'articles'],
    ['title' => 'Accueil',        'url' => '../index',                'icon' => 'home'],
    ['title' => 'Déconnexion',    'url' => '../logout',               'icon' => 'logout'],
];

?>

<section class="admin-dashboard">
    <aside>
        <h2>Dashboard</h2>
        <ul>
            <?php foreach ($links as $link): ?>
                <li>
                    <a href="<?= htmlspecialchars($link['url']) ?>">
                        <img src="/assets/icons/<?= htmlspecialchars($link['icon']) ?>.svg" alt="">
                        <?= htmlspecialchars($link['title']) ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    </aside>
</section>