<?php

/** @var array<string, string> $str */
/** @var string $username */
/** @var bool $isAdmin */
?>

<section class="admin-dashboard">
    <h1><?= secure_html($str['dashboard_title']) ?></h1>
    <p>
        <?= secure_html($str['welcome']) ?>
        <?= $username !== '' ? ', ' . secure_html($username) . ' !' : ' !' ?>
    </p>

    <ul>
        <li><a href="/admin/events"><?= secure_html($str['admin_manage_events']) ?></a></li>
        <li><a href="/admin/contacts"><?= secure_html($str['admin_contacts']) ?></a></li>
        <?php if (!empty($isAdmin)): ?>
            <li><a href="/events/create" class="btn"><?= secure_html($str['admin_create_event']) ?></a></li>
        <?php endif; ?>
        <li><a href="/logout"><?= secure_html($str['logout']) ?></a></li>
    </ul>
</section>
