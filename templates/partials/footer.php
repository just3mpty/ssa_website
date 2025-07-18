<?php

declare(strict_types=1);

use CapsuleLib\Lang\Translate;

Translate::load(default: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php')); // détecte automatiquement la page courante
?>

<footer>
    <div class="container">
        <p><?= Translate::t('footer_address') ?></p>
        <p>
            <?= Translate::t('footer_tel') ?> |
            Email : <a href="mailto:<?= Translate::t('footer_email_1') ?>"><?= Translate::t('footer_email_1') ?></a> |
            <a href="mailto:<?= Translate::t('footer_email_2') ?>"><?= Translate::t('footer_email_2') ?></a>
        </p>
        <p><?= Translate::t('footer_siret') ?></p>
    </div>
</footer>

<button class="scroll-top-btn" title="<?= Translate::t('btn_top') ?>">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M12 5L12 19M12 5L5 12M12 5L19 12"
            stroke="#fff"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
</button>
