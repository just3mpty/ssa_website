<?php

declare(strict_types=1);

namespace App\Model;

use CapsuleLib\Framework\BaseModel;
use PDO;

class User extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Récupère un utilisateur par username.
     */
    public function findByUsername(string $username): ?array
    {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE username = :username", [
            'username' => $username,
        ]);
    }

    // Exemple méthode métier supplémentaire
    public function isAdmin(array $user): bool
    {
        return ($user['role'] ?? '') === 'admin';
    }
}
