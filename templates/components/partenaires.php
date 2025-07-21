<?php $partenaires = [
    ['name' => 'BUZUK', 'url'           => '/', 'logo' => '/assets/img/buzuk.jpg',],
    ['name' => 'Région Bretagne', 'url' => '/', 'logo' => '/assets/img/region-bretagne.jpg',],
    ['name' => 'Inconnu', 'url'         => '/', 'logo' => '/assets/img/FEADER_leader_logoCMJN_vect.jpg',],
] ?>


<section class="partenaires">
    <h2>Ils nous accompagnent</h2>
    <div class="icons">
        <?php foreach ($partenaires as $partenaire): ?>
            <a href="<?= h_specialchar($partenaire['url']) ?>" target="_blank" rel="noreferrer">
                <img src="<?= h_specialchar($partenaire['logo']) ?>" alt="<?= h_specialchar($partenaire['name']) ?>">
            </a>
        <?php endforeach; ?>
    </div>
</section>
</section>
