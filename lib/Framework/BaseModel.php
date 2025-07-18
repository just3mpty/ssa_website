<?php

declare(strict_types=1);

namespace CapsuleLib\Framework;

use PDO;

/**
 * BaseModel ultra-générique (CRUD minimaliste).
 * - 100% indépendant du métier.
 * - À étendre dans tes modèles métier (ex: EventModel).
 * - Compatible SQLite, MySQL, etc.
 */
abstract class BaseModel
{
    protected PDO $pdo;
    protected string $table;      // Nom de la table (doit être défini dans la sous-classe)
    protected string $primaryKey; // Clé primaire (par défaut "id")

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ===== CRUD GÉNÉRIQUE =====

    /**
     * Récupère un enregistrement par clé primaire.
     */
    public function find($id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Récupère tous les enregistrements.
     */
    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Insère un nouvel enregistrement (array [col => value]).
     * Retourne l’ID inséré.
     */
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

    /**
     * Met à jour un enregistrement par clé primaire.
     * $data = [col => value], $id = valeur de la PK.
     */
    public function update($id, array $data): bool
    {
        $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $data[$this->primaryKey] = $id;
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = :{$this->primaryKey}";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Supprime un enregistrement par clé primaire.
     */
    public function delete($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }
}
