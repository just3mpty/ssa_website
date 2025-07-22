<?php

declare(strict_types=1);

namespace CapsuleLib\Database\Repository;

use PDO;

/**
 * BaseRepository ultra-générique (CRUD minimaliste).
 * - 100% indépendant du métier.
 * - À étendre dans tes modèles métier (ex: EventRepository).
 * - Compatible SQLite, MySQL, etc.
 */
abstract class BaseRepository
{
    protected PDO $pdo;
    protected string $table;      // Nom de la table (à définir dans la sous-classe)
    protected string $primaryKey; // Clé primaire (ex: "id")

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find($id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function insert(array $data): int
    {
        $cols = array_keys($data);
        $fields = implode(', ', $cols);
        $placeholders = implode(', ', array_map(fn($c) => ':' . $c, $cols));
        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int)$this->pdo->lastInsertId();
    }

    public function update($id, array $data): bool
    {
        $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $data[$this->primaryKey] = $id;
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = :{$this->primaryKey}";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }
}
