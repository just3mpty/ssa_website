<?php

declare(strict_types=1);

/**
 * Encode une chaîne pour affichage HTML sans risque de XSS.
 *
 * @param string $str  Chaîne brute utilisateur
 * @return string      Chaîne sécurisée
 *
 * usage : <?= h_specialchar($event['titre']) ?
 */
function h_specialchar(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
