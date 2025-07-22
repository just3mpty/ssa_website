<?php

/** @var array<string, string> $str */

$partenaires = [
    ['name' => 'BUZUK',             'url' => '/', 'logo' => '/assets/img/buzuk.jpg'],
    ['name' => 'Région Bretagne',   'url' => '/', 'logo' => '/assets/img/region-bretagne.jpg'],
    ['name' => 'Inconnu',           'url' => '/', 'logo' => '/assets/img/FEADER_leader_logoCMJN_vect.jpg'],
];
?>

<section class="partenaires">
    <h2><?= secure_html($str['partners_title']) ?></h2>
    <div class="icons">
        <?php foreach ($partenaires as $p): ?>
            <a href="<?= secure_url($p['url']) ?>" target="_blank" rel="noreferrer noopener">
                <img src="<?= secure_attr($p['logo']) ?>" alt="<?= secure_attr($p['name']) ?>">
            </a>
        <?php endforeach; ?>
    </div>
</section>
