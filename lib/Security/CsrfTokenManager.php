<?php

//À la génération du formulaire
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
//<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ">

// À la réception

if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit;
}
