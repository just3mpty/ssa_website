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
                        <p><?= htmlspecialchars($event['date_event']) ?></p>
                        <p>17:00</p>
                    </div>
                    <div class="description">
                        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
        <article class="event">
            <div class="date-time">
                <p>18 Juil.</p>
                <p>17:00</p>
            </div>
            <div class="description">
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusantium veritatis possimus distinctio non tempore optio cum ab odio voluptatum fugit?</p>
            </div>
        </article>
    </div>
</section>