<?php

/** @var array<string, string> $str */
?>

<div class="background">
    <section id="about" class="about">
        <div class="text">
            <h2><?= secure_html($str['about_title']) ?></h2>
            <h3><?= secure_html($str['about_subtitle']) ?></h3>

            <p><?= secure_html($str['about_intro']) ?></p>

            <p><strong><?= secure_html($str['about_axes']) ?></strong></p>
            <ul>
                <li><?= secure_html($str['about_axes_1']) ?>;</li>
                <li><?= secure_html($str['about_axes_2']) ?></li>
            </ul>

            <p><strong><?= secure_html($str['about_goal_label']) ?></strong>
                <?= secure_html($str['about_goal']) ?>
            </p>

            <button><?= secure_html($str['about_cta']) ?></button>
        </div>

        <div class="illustration">
            <img src="/assets/img/logoSSA.png" alt="<?= secure_attr($str['about_logo_alt']) ?>">
        </div>
    </section>
</div>
