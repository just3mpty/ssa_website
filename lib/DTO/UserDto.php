<?php

declare(strict_types=1);

namespace CapsuleLib\DTO;

/**
 * User Data Transfer Object
 * - Strictement un container de données typées (immutable par défaut)
 * - Ne contient aucune logique métier ou d'accès DB
 */
class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $password_hash,
        public readonly string $role,
        public readonly string $email,
        public readonly string $created_at,
    ) {}
}
