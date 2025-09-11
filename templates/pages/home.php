<?php
/** @var array<string, string> $str */ ?>

<?php
$e = static fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$action = $e($articleGenerateIcsAction ?? '/home/generate_ics');
?>



<section class="hero">
    <div class="overlay"></div>
    <h1><?= secure_html($str['hero_title']) ?></h1>
    <p class="slogan"><?= secure_html($str['hero_slogan']) ?></p>
    <div class="cta-buttons">
        <a href="/projet" class="btn primary"><?= secure_html($str['hero_cta_more']) ?></a>
        <a href="/projet/#recrutement" class="btn secondary"><?= secure_html($str['hero_cta_participate']) ?></a>
        <a href="/#contact" class="btn secondary"><?= secure_html($str['hero_cta_contact']) ?></a>
    </div>
</section>

<?= $this->renderComponent('apropos.php', ['str' => $str]) ?>
<?= $this->renderComponent('actualites.php', ['str' => $str]) ?>

<div class="separator"></div>

<section id="agenda" class="agenda">
    <h2>Agenda</h2>
    <p><?= secure_html($str['agenda_intro']) ?></p>

    <div class="articles">
        <?php if (empty($articles)): ?>
            <p class="no-articles"><?= secure_html($str['no_upcoming_articles']) ?></p>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <article class="article">
                    <div class="date-time">
                        <p><?= secure_html($article->date_article) ?></p>
                        <p><?= secure_html(substr($article->hours, 0, 5)) ?></p>
                    </div>
                    <div class="description">
                        <h3><?= secure_html($article->titre) ?></h3>
                        <p><?= nl2br(secure_html($article->resume)) ?></p>
                    </div>
                    <!-- bouton ics -->
                    <form action="<?= $action ?>" method="post">
                    <?= \CapsuleLib\Security\CsrfTokenManager::insertInput(); ?>

                        <!-- <button type="submit" name="article_id" value="generer_ics"> -->
                        <button type="submit" name="submit">

                            <input type="hidden" name="eventDate" value="<?= $article->date_article . ' ' . substr($article->hours, 0, 5) . ':00' ?>">
                            <input type="hidden" name="eventTitle" value="<?= $article->titre ?>">
                            <input type="hidden" name="eventDescription" value="<?= $article->resume ?>">
                            <input type="hidden" name="eventLocation" value="<?= $article->lieu ?>">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path d="M21 11.5V8.8C21 7.11984 21 6.27976 20.673 5.63803C20.3854 5.07354 19.9265 4.6146 19.362 4.32698C18.7202 4 17.8802 4 16.2 4H7.8C6.11984 4 5.27976 4 4.63803 4.32698C4.07354 4.6146 3.6146 5.07354 3.32698 5.63803C3 6.27976 3 7.11984 3 8.8V17.2C3 18.8802 3 19.7202 3.32698 20.362C3.6146 20.9265 4.07354 21.3854 4.63803 21.673C5.27976 22 6.11984 22 7.8 22H12.5M21 10H3M16 2V6M8 2V6M18 21V15M15 18H21" stroke="#fdb544" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </g>
                            </svg>
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<div class="separator"></div>

<?= $this->renderComponent('partenaires.php', ['str' => $str]) ?>
<div class="separator"></div>
<?= $this->renderComponent('contact.php', ['str' => $str]) ?>