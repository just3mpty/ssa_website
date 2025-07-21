<?php
$articles = [
    [
        'title' => 'Atelier sur la santé alimentaire',
        'content' => 'Participez à notre atelier pour découvrir les bienfaits d’une alimentation durable.',
        'category' => 'sante',
        'image' => '/assets/img/banner.webp',
        'link' => 'actualites.html'
    ],
    [
        'title' => 'Rencontre avec les paysans locaux',
        'content' => 'Échangez avec les producteurs pour une agriculture respectueuse de l’environnement.',
        'category' => 'environnement',
        'image' => '/assets/img/banner.webp',
        'link' => 'actualites.html'
    ],
    [
        'title' => 'Marche citoyenne pour le climat',
        'content' => 'Joignez-vous à notre mobilisation pour un avenir plus vert.',
        'category' => 'mobilisation',
        'image' => '/assets/img/banner.webp',
        'link' => '/#news'
    ],
];
?>

<section id="news" class="news">
    <h2>Actualités</h2>
    <div class="filters">
        <button class="filter-btn" data-filter="all">Toutes</button>
        <button class="filter-btn" data-filter="sante">Santé</button>
        <button class="filter-btn" data-filter="environnement">Environnement</button>
        <button class="filter-btn" data-filter="mobilisation">Mobilisation</button>
    </div>
    <div class="news-grid">
        <?php foreach ($articles as $article): ?>
            <article class="news-item" data-category="<?= htmlspecialchars($article['category']) ?>">
                <h3><?= htmlspecialchars($article['title']) ?></h3>
                <p><?= htmlspecialchars($article['content']) ?></p>
                <img src="<?= htmlspecialchars($article['image']) ?>" alt="illustration event">
                <a href="<?= htmlspecialchars($article['link']) ?>" class="read-more">Lire plus</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>