<?php $partenaires = [
    ['name' => 'BUZUK', 'url'           => '/', 'logo' => '/assets/img/buzuk.jpg',],
    ['name' => 'Région Bretagne', 'url' => '/', 'logo' => '/assets/img/region-bretagne.jpg',],
    ['name' => 'Inconnu', 'url'         => '/', 'logo' => '/assets/img/FEADER_leader_logoCMJN_vect.jpg',],
] ?>

<section class="partenaires">
    <h2>Ils nous accompagnent</h2>
    <div class="icons">

        <?php foreach ($partenaires as $partenaire): ?>
            <a href="<?= htmlspecialchars($partenaire['url']) ?>" target="_blank" rel="noreferrer">
                <img src="<?= $partenaire['logo'] ?>" alt="<?= $partenaire['name'] ?>">
            </a>
        <?php endforeach; ?>
    </div>
</section>