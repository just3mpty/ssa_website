<footer>
    <div class="footer-infos">
        <div class="infos">
            <p><?= secure_html($str['footer_address']) ?></p>
            <p>
                <?= secure_html($str['footer_tel']) ?> |
                Email :
                <a href="mailto:<?= secure_attr($str['footer_email_1']) ?>">
                    <?= secure_html($str['footer_email_1']) ?>
                </a> |
                <a href="mailto:<?= secure_attr($str['footer_email_2']) ?>">
                    <?= secure_html($str['footer_email_2']) ?>
                </a>
            </p>
            <p><?= secure_html($str['footer_siret']) ?></p>
        </div>
        <img src="/assets/img/logo.svg" alt="SSA logo">
    </div>
    <p class="copyright"><?= secure_html($str['footer_copyright']) ?></p>
</footer>