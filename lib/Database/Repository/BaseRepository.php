<?php

declare(strict_types=1);

namespace CapsuleLib\Database\Repository;

use PDO;

/**
 * BaseRepository ultra-générique pour opérations CRUD minimales sur une table.
 *
 * Cette classe abstraite fournit une implémentation basique pour :
 * - Trouver un enregistrement par clé primaire
 * - Récupérer tous les enregistrements
 * - Exécuter des requêtes préparées (retour unique ou multiples lignes)
 * - Insérer, mettre à jour et supprimer un enregistrement
 *
 * Elle est conçue pour être étendue par des classes métier spécifiques (ex : EventRepository).
 * Elle ne contient aucune logique métier, elle est indépendante du domaine applicatif.
 *
 * Compatible avec PDO (MySQL, SQLite, etc.).
 */
abstract class BaseRepository
{
    /**
     * @var PDO Instance PDO pour la connexion à la base.
     */
    protected PDO $pdo;

    /**
     * @var string Nom de la table dans la base de données.
     *             Doit être défini dans la classe enfant.
     */
    protected string $table;

    /**
     * @var string Nom de la clé primaire de la table.
     *             Doit être défini dans la classe enfant (exemple : "id").
     */
    protected string $primaryKey;

    /**
     * Constructeur.
     *
     * @param PDO $pdo Instance PDO connectée à la base.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Trouve un enregistrement par son identifiant (clé primaire).
     *
     * @param mixed $id Valeur de la clé primaire à rechercher.
     * @return array|null Tableau associatif de la ligne trouvée ou null si non trouvé.
     */
    public function find($id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Récupère tous les enregistrements de la table.
     *
     * @return array Liste de tableaux associatifs représentant chaque ligne.
     */
    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Exécute une requête préparée retournant une seule ligne.
     *
     * @param string $sql    Requête SQL avec placeholders nommés.
     * @param array  $params Paramètres à binder dans la requête.
     * @return array|null Ligne retournée ou null si aucune.
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Exécute une requête préparée retournant plusieurs lignes.
     *
     * @param string $sql    Requête SQL avec placeholders nommés.
     * @param array  $params Paramètres à binder dans la requête.
     * @return array Liste des lignes retournées.
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Insère un nouvel enregistrement dans la table.
     *
     * @param array $data Tableau associatif colonne => valeur.
     * @return int ID du nouvel enregistrement inséré.
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
     * Met à jour un enregistrement existant par son ID.
     *
     * @param mixed $id    Identifiant de l’enregistrement à modifier.
     * @param array $data  Données à mettre à jour (colonne => valeur).
     * @return bool True si succès, false sinon.
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
     * Supprime un enregistrement par son ID.
     *
     * @param mixed $id Identifiant de l’enregistrement à supprimer.
     * @return bool True si succès, false sinon.
     */
    public function delete($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }
}
