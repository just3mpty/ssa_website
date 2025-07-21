<section class="hero">
    <div class="overlay"></div>
    <h1>Sécurité Sociale de l’Alimentation – Pays de Morlaix</h1>
    <p class="slogan">Pour un droit fondamental et universel à une alimentation saine et durable.</p>
    <div class="cta-buttons">
        <a href="projet.html" class="btn primary">En savoir plus</a>
        <a href="participer.html" class="btn secondary">Participer</a>
        <a href="contact.html" class="btn secondary">Contact</a>
    </div>
</section>
<?= $this->renderComponent('apropos.php') ?>
<?= $this->renderComponent('actualites.php') ?>
<div class="separator"></div>
<section id="agenda" class="agenda">
    <h2>Agenda</h2>
    <p>Retrouvez nos événements à venir :</p>
    <div class="events">
        <?php if (empty($events)): ?>
            <p class="no-events">Aucun événement à venir.</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <article class="event">
                    <div class="date-time">
                        <p><?= h_specialchar($event['date_event']) ?></p>
                        <p><?= h_specialchar(substr($event['hours'], 0, 5)) ?></p>
                    </div>
                    <div class="description">
                        <p><?= nl2br(h_specialchar($event['description'])) ?></p>
                    </div>

                    <?php if (!empty($event['image'])): ?>
                        <img src="<?= h_specialchar($event['image'], ENT_QUOTES) ?>" alt="Image de l'événement" style="max-width:300px">
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<div class="separator"></div>
<?= $this->renderComponent('partenaires.php') ?>
<div class="separator"></div>
<?= $this->renderComponent('contact.php') ?>
