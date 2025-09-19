<?php

declare(strict_types=1);

namespace App\Controller;

use Capsule\Core\RenderController;
use Capsule\Service\UserService;
use Capsule\Service\PasswordService;
use Capsule\Http\RequestUtils;
use Capsule\Http\Redirect;
use Capsule\Security\CsrfTokenManager;

final class UserController extends RenderController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly PasswordService $passwords,
    ) {}

    /* ===== Utilisateurs (admin) ===== */
    /** POST /dashboard/users/create */
    public function usersCreate(): void
    {
        RequestUtils::ensurePostOrRedirect('/dashboard/users');
        CsrfTokenManager::requireValidToken();

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null;
        $role     = trim((string)($_POST['role'] ?? 'employee'));

        $errors = [];
        if ($username === '') $errors['username'] = 'Requis.';
        if ($password === '') $errors['password'] = 'Requis.';
        if (!$email)          $errors['email']    = 'Email invalide.';

        if ($errors !== []) {
            Redirect::withErrors(
                '/dashboard/users',
                'Le formulaire contient des erreurs.',
                $errors,
                ['username' => $username, 'email' => (string)$email, 'role' => $role]
            );
        }

        try {
            $this->userService->createUser($username, $password, (string)$email, $role);
            Redirect::withSuccess('/dashboard/users', 'Utilisateur créé avec succès.');
        } catch (\Throwable $e) {
            Redirect::withErrors(
                '/dashboard/users',
                'Erreur lors de la création.',
                ['_global' => 'Création impossible.'],
                ['username' => $username, 'email' => (string)$email, 'role' => $role]
            );
        }
    }

    /** POST /dashboard/users/delete */
    public function usersDelete(): void
    {
        RequestUtils::ensurePostOrRedirect('/dashboard/users');
        //CsrfTokenManager::requireValidToken();

        $ids = array_map('intval', (array)($_POST['user_ids'] ?? []));
        $ids = array_values(array_filter($ids, fn(int $id) => $id > 0));

        if ($ids === []) {
            Redirect::withErrors('/dashboard/users', 'Aucun utilisateur sélectionné.', ['_global' => 'Aucun utilisateur sélectionné.']);
        }

        $deleted = 0;
        foreach ($ids as $id) {
            try {
                $this->userService->deleteUser($id);
                $deleted++;
            } catch (\Throwable $e) {
                // on continue pour les autres
            }
        }
        if ($deleted > 0) {
            Redirect::withSuccess('/dashboard/users', "Utilisateur(s) supprimé(s) : {$deleted}.");
        }
        Redirect::withErrors('/dashboard/users', 'Aucune suppression effectuée.', ['_global' => 'Aucune suppression effectuée.']);
    }

    public function usersUpdate(): void
    {

        RequestUtils::ensurePostOrRedirect('/dashboard/users');
        //CsrfTokenManager::requireValidToken();
        $id       = (int)($_POST['id'] ?? 0);
        $username = trim((string)($_POST['username'] ?? ''));
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null;
        $role     = trim((string)($_POST['role'] ?? 'employee'));


        $errors = [];
        if ($id <= 0)        $errors['_global'] = 'ID utilisateur invalide.';
        if ($username === '') $errors['username'] = 'Requis.';
        if (!$email)          $errors['email']    = 'Email invalide.';

        if ($errors !== []) {
            Redirect::withErrors(
                "/dashboard/users/{$id}",
                'Le formulaire contient des erreurs.',
                $errors,
                ['username' => $username, 'email' => (string)$email, 'role' => $role]
            );
        }

        try {
            $input = ['username' => $username, 'email' => (string)$email, 'role' => $role];

            $this->userService->updateUser($id, $input);
            Redirect::withSuccess("/dashboard/users", 'Utilisateur modifié avec succès.');
        } catch (\Throwable $e) {
            Redirect::withErrors(
                // '/dashboard/users',
                '/dashboard/account',
                'Erreur lors de la modification.',
                ['_global' => 'Modification impossible.'],
                ['username' => $username, 'email' => (string)$email, 'role' => $role]
            );
        }
    }
}
