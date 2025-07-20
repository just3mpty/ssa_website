<?php

declare(strict_types=1);
#
/**
 * Table de correspondance entre les namespaces racines et les répertoires réels.
 *
 * @var array<string, string>
 *
 * Exemple :
 * - 'CapsuleLib\Controller\X' → 'lib/Controller/X.php'
 * - 'App\Model\Y' → 'src/Model/Y.php'
 */
const ALIASES = [
    'CapsuleLib' => 'lib',
    'App'        => 'src',
];

/**
 * Autoloader PSR-4 simplifié.
 *
 * Gère uniquement les namespaces qui commencent par une clé définie dans ALIASES.
 * Convertit les namespaces en chemins de fichiers relatifs, puis les charge.
 *
 * @throws Exception Si le namespace racine est invalide ou si le fichier cible n’existe pas.
 */
spl_autoload_register(function (string $class): void {
    // Exemple : CapsuleLib\Controller\AbstractController
    $namespaceParts = explode('\\', $class);
    $rootNamespace = $namespaceParts[0];

    // Remplace le namespace racine par son alias (dossier)
    if (array_key_exists($rootNamespace, ALIASES)) {
        $namespaceParts[0] = ALIASES[$rootNamespace];
    } else {
        throw new Exception(
            "Namespace « $rootNamespace » invalide. "
                . "Un namespace doit commencer par : « " . implode(' », « ', array_keys(ALIASES)) . " »"
        );
    }

    // Construit le chemin absolu du fichier PHP à inclure
    $filepath = dirname(__DIR__) . '/' . implode('/', $namespaceParts) . '.php';

    if (!file_exists($filepath)) {
        throw new Exception(
            "Fichier introuvable : « $filepath » pour la classe « $class ». "
                . "Vérifie le nom de fichier, la casse et le namespace."
        );
    }

    require $filepath;
});
