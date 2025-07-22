<?php

declare(strict_types=1);

namespace CapsuleLib\Service;

use CapsuleLib\Repository\UserRepository;
use CapsuleLib\DTO\UserDTO;

use RuntimeException;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Crée un nouvel utilisateur (avec hash sécurisé).
     * Lève une exception si username ou email existe déjà.
     */
    public function createUser(string $username, string $password, string $email, string $role = 'employee'): int
    {
        if ($this->userRepository->existsUsername($username)) {
            throw new RuntimeException("Username already exists");
        }
        if ($this->userRepository->existsEmail($email)) {
            throw new RuntimeException("Email already exists");
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->userRepository->insert([
            'username'      => $username,
            'password_hash' => $hash,
            'role'          => $role,
            'email'         => $email,
        ]);
    }

    /**
     * Met à jour le mot de passe d'un utilisateur (avec hash).
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userRepository->update($userId, ['password_hash' => $hash]);
    }

    /**
     * Vérifie si l'utilisateur a le rôle admin.
     */
    public function isAdmin(UserDTO $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Retourne un utilisateur DTO par username.
     */
    public function getUserByUsername(string $username): ?UserDTO
    {
        return $this->userRepository->findByUsername($username);
    }

    /**
     * Retourne un utilisateur DTO par id.
     */
    public function getUserById(int $id): ?UserDTO
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Retourne tous les utilisateurs (tableau de DTO).
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->allUsers();
    }
}
