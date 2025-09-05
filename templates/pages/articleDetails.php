<section class="details">
    <div style="margin-top: 120px;">
        <h1><?= secure_html($article->titre) ?></h1>
        <h2><?= secure_html($article->resume) ?></h2>
        <p><?= secure_html($article->description) ?></p>
        <p>Date : <?= secure_html($article->date_event) ?></p>
        <p>Lieu : <?= secure_html($article->lieu) ?></p>
        <div class="separator"></div>
        <p>Article rédigé par : <?= secure_html($article->author) ?></p>
    </div>
</section>