<?php

declare(strict_types=1);

namespace CapsuleLib\Http;

/**
 * Redirect
 * --------
 * Façade minimaliste pour les redirections HTTP (PRG).
 *
 * Contrat:
 *  - Méthodes terminent le flux par `exit;` → type-effect 'never' quand redirection déclenchée.
 *  - `withErrors()` persiste erreurs + data formulaire (PRG) et flash 'error' avant redirection.
 *  - `withSuccess()` push un flash 'success' avant redirection.
 *
 * Sécu:
 *  - GUARD: empêche CRLF injection dans l'en-tête Location.
 *  - GUARD: refuse codes hors 300–399.
 *  - Requiert une session active pour FormState/FlashBag.
 *
 * Erreurs:
 *  - Si des en-têtes ont déjà été envoyés, lève RuntimeException (plutôt que rediriger silencieusement).
 */
final class Redirect
{
    /**
     * Redirige vers $path avec un code 3xx.
     *
     * @param non-empty-string $path   Chemin relatif ou URL absolue.
     * @param int $status              303 par défaut (PRG); 300–399 uniquement.
     * @return never
     */
    public static function to(string $path, int $status = 303): never
    {
        // GUARD: codes valides
        if ($status < 300 || $status > 399) {
            throw new \InvalidArgumentException("Invalid redirect status: {$status}");
        }
        // GUARD: CRLF injection
        if (\str_contains($path, "\r") || \str_contains($path, "\n")) {
            throw new \InvalidArgumentException('Invalid Location header (CRLF detected).');
        }
        // GUARD: en-têtes déjà envoyés → on ne tente pas une redirection silencieuse
        if (\headers_sent($file, $line)) {
            throw new \RuntimeException("Cannot redirect: headers already sent at {$file}:{$line}");
        }

        \header('Location: ' . $path, replace: true, response_code: $status);
        exit;
    }

    /**
     * Redirige avec erreurs de formulaire et données repopulées (pattern PRG).
     *
     * @param array<string,string> $errors  Erreurs par champ (déjà localisées)
     * @param array<string,mixed>  $data    Données (sans champs sensibles)
     * @return never
     */
    public static function withErrors(
        string $to,
        string $message,
        array $errors,
        array $data = [],
        int $status = 303
    ): never {
        // NOTE: FormState & FlashBag exigent une session active (leurs propres guards).
        FormState::set($errors, $data);
        FlashBag::add('error', $message);
        self::to($to, $status);
    }

    /**
     * Redirige avec message de succès (pattern PRG).
     *
     * @return never
     */
    public static function withSuccess(string $to, string $message, int $status = 303): never
    {
        FlashBag::add('success', $message);
        self::to($to, $status);
    }
}
