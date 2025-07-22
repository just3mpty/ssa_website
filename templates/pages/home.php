<?php

/** @var array<string, string> $str */ ?>

<section class="hero">
    <div class="overlay"></div>
    <h1><?= secure_html($str['hero_title']) ?></h1>
    <p class="slogan"><?= secure_html($str['hero_slogan']) ?></p>
    <div class="cta-buttons">
        <a href="/projet" class="btn primary"><?= secure_html($str['hero_cta_more']) ?></a>
        <a href="/participer" class="btn secondary"><?= secure_html($str['hero_cta_participate']) ?></a>
        <a href="/#contact" class="btn secondary"><?= secure_html($str['hero_cta_contact']) ?></a>
    </div>
</section>

<?= $this->renderComponent('apropos.php', ['str' => $str]) ?>
<?= $this->renderComponent('actualites.php', ['str' => $str]) ?>

<div class="separator"></div>

<section id="agenda" class="agenda">
    <h2>Agenda</h2>
    <p><?= secure_html($str['agenda_intro']) ?></p>

    <div class="events">
        <?php if (empty($events)): ?>
            <p class="no-events"><?= secure_html($str['no_upcoming_events']) ?></p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <article class="event">
                    <div class="date-time">
                        <p><?= secure_html($event->date_event) ?></p>
                        <p><?= secure_html(substr($event->hours, 0, 5)) ?></p>
                    </div>
                    <div class="description">
                        <h3><?= secure_html($event->titre) ?></h3>
                        <p><?= nl2br(secure_html($event->description)) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<div class="separator"></div>

<?= $this->renderComponent('partenaires.php', ['str' => $str]) ?>
<div class="separator"></div>
<?= $this->renderComponent('contact.php', ['str' => $str]) ?>
