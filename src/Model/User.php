<?php

declare(strict_types=1);

namespace App\Model;

use CapsuleLib\Framework\BaseModel;
use PDO;

/**
 * Data Transfer Object (optionnel, plus clair pour la suite)
 */
class UserData
{
    public function __construct(
        public int $id,
        public string $username,
        public string $password_hash,
        public string $role,
        public string $email,
        public string $created_at
    ) {}
}

class User extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Récupère un utilisateur par username (retourne UserData|null).
     */
    public function findByUsername(string $username): ?UserData
    {
        $row = $this->queryOne("SELECT * FROM {$this->table} WHERE username = :username", [
            'username' => $username,
        ]);
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Récupère un utilisateur par ID.
     */
    public function findById(int $id): ?UserData
    {
        $row = $this->find($id);
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Hydrate un UserData à partir d’un array SQL.
     */
    private function hydrate(array $data): UserData
    {
        return new UserData(
            id: (int) $data['id'],
            username: $data['username'],
            password_hash: $data['password_hash'],
            role: $data['role'],
            email: $data['email'],
            created_at: $data['created_at'],
        );
    }

    /**
     * Vérifie si un utilisateur a le rôle admin.
     */
    public function isAdmin(UserData|array $user): bool
    {
        if (is_array($user)) {
            return ($user['role'] ?? '') === 'admin';
        }
        return $user->role === 'admin';
    }

    /**
     * Crée un nouvel utilisateur.
     */
    public function createUser(string $username, string $password, string $email, string $role = 'employee'): int
    {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->insert([
            'username'      => $username,
            'password_hash' => $password_hash,
            'role'          => $role,
            'email'         => $email,
        ]);
    }

    /**
     * Met à jour le mot de passe d’un utilisateur.
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password_hash' => $hash]);
    }

    /**
     * Retourne tous les utilisateurs (UserData[]).
     */
    public function allUsers(): array
    {
        $rows = $this->all();
        return array_map([$this, 'hydrate'], $rows);
    }
}
