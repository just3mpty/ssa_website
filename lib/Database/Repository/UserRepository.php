<?php

declare(strict_types=1);

namespace CapsuleLib\Database\Repository;

use CapsuleLib\Database\Repository\BaseRepository;
use CapsuleLib\DTO\UserDTO;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    public function findById(int $id): ?UserDTO
    {
        $row = $this->find($id);
        return $row ? $this->hydrate($row) : null;
    }

    public function findByUsername(string $username): ?UserDTO
    {
        $row = $this->queryOne("SELECT * FROM {$this->table} WHERE username = :username", [
            'username' => $username,
        ]);
        return $row ? $this->hydrate($row) : null;
    }

    public function findByEmail(string $email): ?UserDTO
    {
        $row = $this->queryOne("SELECT * FROM {$this->table} WHERE email = :email", [
            'email' => $email,
        ]);
        return $row ? $this->hydrate($row) : null;
    }

    public function allUsers(): array
    {
        $rows = $this->all();
        return array_map([$this, 'hydrate'], $rows);
    }

    // --- Helpers d’unicité (utiles pour UserService) ---
    public function existsUsername(string $username): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->table} WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return (bool)$stmt->fetchColumn();
    }

    public function existsEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Hydrate un UserDTO à partir d'un array SQL.
     */
    private function hydrate(array $data): UserDTO
    {
        return new UserDTO(
            id: (int)$data['id'],
            username: $data['username'],
            password_hash: $data['password_hash'],
            role: $data['role'],
            email: $data['email'],
            created_at: $data['created_at'],
        );
    }
}
