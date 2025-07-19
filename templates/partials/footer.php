<?php

declare(strict_types=1);

use CapsuleLib\Service\Lang\Translate;

Translate::load(default: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php'));
?>

<footer>
    <div class="footer-infos">
        <div class="infos">
            <p><?= Translate::t('footer_address') ?></p>
            <p>
                <?= Translate::t('footer_tel') ?> |
                Email : <a href="mailto:<?= Translate::t('footer_email_1') ?>"><?= Translate::t('footer_email_1') ?></a> |
                <a href="mailto:<?= Translate::t('footer_email_2') ?>"><?= Translate::t('footer_email_2') ?></a>
            </p>
            <p><?= Translate::t('footer_siret') ?></p>
        </div>
        <img src="/assets/img/logoSSA.png" alt="SSA logo">
    </div>
    <p class="copyright"><?= Translate::t('footer_copyright') ?></p>
</footer>