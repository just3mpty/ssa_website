<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\EventDTO;
use CapsuleLib\Database\Repository\BaseRepository;
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
     * Récupère tous les événements à venir, triés par date (array d’EventDTO).
     */
    public function upcoming(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE date_event >= :today ORDER BY date_event ASC"
        );
        $stmt->execute(['today' => date('Y-m-d')]);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * Récupère les événements d’un auteur (array d’EventDTO).
     */
    public function findByAuthor(int $authorId): array
    {
        $rows = $this->query(
            "SELECT * FROM {$this->table} WHERE author_id = :author_id ORDER BY date_event DESC",
            ['author_id' => $authorId]
        );
        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * Récupère un événement par ID (EventDTO|null).
     */
    public function findById(int $id): ?EventDTO
    {
        $row = $this->find($id);
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Publie un nouvel événement (retourne l’ID).
     */
    public function create(array $data): int
    {
        // Ne PAS passer de champ "image"
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

    /**
     * Hydrate un EventDTO à partir d’un array SQL.
     */
    private function hydrate(array $data): EventDTO
    {
        return new EventDTO(
            id: (int)$data['id'],
            titre: $data['titre'],
            description: $data['description'],
            date_event: $data['date_event'],
            hours: $data['hours'],
            lieu: $data['lieu'] ?? null,
            created_at: $data['created_at'],
            author_id: (int)$data['author_id'],
        );
    }
}
