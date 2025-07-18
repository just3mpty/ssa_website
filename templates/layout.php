<?php

declare(strict_types=1);

use App\Lang\Translate;

Translate::load(default: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php')); // détecte automatiquement la page courante

?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'fr' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= Translate::t('meta_description') ?>">
    <meta name="keywords" content="<?= Translate::t('meta_keywords') ?>">
    <meta name="author" content="<?= Translate::t('meta_author') ?>">

    <title><?= Translate::t('page_title') ?></title>

    <link rel="stylesheet" href="./assets/css/styles.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap">
    <link rel="icon" type="image/png" href="img/logoSSA.png">
</head>

<body>

    <?php require dirname(__DIR__) . '/templates/partials/header.php'; ?>
    <main>
        <?php echo $viewContent; ?>
    </main>
    <?php require dirname(__DIR__) . '/templates/partials/footer.php'; ?>
    <script src="/assets/js/script.js"></script>
</body>

</html>
