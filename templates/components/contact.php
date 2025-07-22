<?php

/** @var array<string, string> $str */
?>

<div class="background">
    <section id="contact" class="contact">
        <h2><?= secure_html($str['contact_title']) ?></h2>
        <p><?= secure_html($str['contact_intro']) ?></p>

        <div class="infos">
            <div class="contact-info">
                <h3><?= secure_html($str['contact_coords_title']) ?></h3>

                <p><strong><?= secure_html($str['contact_address_label']) ?></strong>
                    <?= secure_html($str['contact_address']) ?>
                </p>

                <p><strong><?= secure_html($str['contact_phone_label']) ?></strong>
                    <a href="tel:+33298675154"><?= secure_html($str['contact_phone']) ?></a>
                </p>

                <p><strong><?= secure_html($str['contact_email_label']) ?></strong>
                    <a href="mailto:g.gabilletcpie@gmail.com"><?= secure_html($str['contact_email1']) ?></a> |
                    <a href="mailto:nicolas@buzuk.bzh"><?= secure_html($str['contact_email2']) ?></a>
                </p>
            </div>

            <div class="contact-form">
                <h3><?= secure_html($str['contact_form_title']) ?></h3>

                <form id="contact-form" method="post" action="/contact-handler.php">
                    <label for="name"><?= secure_html($str['contact_form_name']) ?></label>
                    <input type="text" id="name" name="name" required>

                    <label for="email"><?= secure_html($str['contact_form_email']) ?></label>
                    <input type="email" id="email" name="email" required>

                    <label for="message"><?= secure_html($str['contact_form_message']) ?></label>
                    <textarea id="message" name="message" rows="5" required></textarea>


                    <button type="submit" class="btn primary"><?= secure_html($str['contact_form_submit']) ?></button>
                </form>
            </div>
        </div>
    </section>
</div>
