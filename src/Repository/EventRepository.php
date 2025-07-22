<?php

declare(strict_types=1);

namespace App\Repository;

use CapsuleLib\Repository\BaseRepository;
use PDO;

class EventRepository extends BaseRepository
{
    protected string $table = 'events';
    protected string $primaryKey = 'id';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Récupère tous les événements à venir, triés par date.
     */
    public function upcoming(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE date_event >= :today ORDER BY date_event ASC");
        $stmt->execute(['today' => date('Y-m-d')]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les événements d’un utilisateur (auteur).
     */
    public function findByAuthor(int $authorId): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE author_id = :author_id ORDER BY date_event DESC",
            ['author_id' => $authorId]
        );
    }

    /**
     * Publie un nouvel événement.
     */
    public function create(array $data): int
    {
        // Filtrage/sécurisation possible ici
        return $this->insert($data);
    }

    /**
     * Supprime tous les événements d’un utilisateur (utile si user supprimé, ou pour un "purge").
     */
    public function deleteByAuthor(int $authorId): int
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE author_id = :author_id");
        $stmt->execute(['author_id' => $authorId]);
        return $stmt->rowCount();
    }
}
