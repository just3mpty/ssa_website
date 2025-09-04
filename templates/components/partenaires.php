<?php

/** @var array<string, string> $str */

$partenaires = [
    ['name' => 'BUZUK', 'role' => 'partenaire', 'url' => '/', 'logo' => '/assets/img/buzuk.jpg'],
    ['name' => 'Région Bretagne', 'role' => 'financeur', 'url' => '/', 'logo' => '/assets/img/region-bretagne.jpg'],
    ['name' => 'ULAMIR-CPIE', 'role' => 'partenaire', 'url' => '/', 'logo' => '/assets/img/ulamircpie.png'],
    ['name' => 'Pôle ESS Pays de Morlaix', 'role' => 'partenaire', 'url' => '/', 'logo' => '/assets/img/ess.jpg'],
    ['name' => 'RESAM', 'role' => 'partenaire', 'url' => '/', 'logo' => '/assets/img/resam.png'],
    ['name' => 'Leader financement Européen', 'role' => 'financeur', 'url' => '/', 'logo' => '/assets/img/feader.jpg'],
];

// Séparer partenaires et financeurs
$onlyPartenaires = array_filter($partenaires, fn($p) => $p['role'] === 'partenaire' || $p['role'] === '');
$onlyFinanceurs  = array_filter($partenaires, fn($p) => $p['role'] === 'financeur');

?>

<section class="partenaires">
    <h2><?= secure_html($str['partners_title']) ?></h2>

    <div class="icons partners">
        <h3>Partenaires</h3>
        <div class="logos">

            <?php foreach ($onlyPartenaires as $p): ?>
                <a href="<?= secure_url($p['url']) ?>" target="_blank" rel="noreferrer noopener">
                    <img src="<?= secure_attr($p['logo']) ?>" alt="<?= secure_attr($p['name']) ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="separator"></div>

    <div class="icons financeurs">
        <h3>Financeurs</h3>
        <div class="logos">

            <?php foreach ($onlyFinanceurs as $p): ?>
                <a href="<?= secure_url($p['url']) ?>" target="_blank" rel="noreferrer noopener">
                    <img src="<?= secure_attr($p['logo']) ?>" alt="<?= secure_attr($p['name']) ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>