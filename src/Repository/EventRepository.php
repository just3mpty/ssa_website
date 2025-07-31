<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\EventDTO;
use CapsuleLib\Database\Repository\BaseRepository;
use PDO;

/**
 * Repository dédié à la gestion des événements.
 *
 * Fournit les opérations CRUD spécifiques aux événements,
 * avec un mapping vers des DTOs typés (`EventDTO`).
 *
 * Hérite du `BaseRepository` pour les opérations SQL basiques.
 */
class EventRepository extends BaseRepository
{
    /**
     * Nom de la table associée aux événements.
     *
     * @var string
     */
    protected string $table = 'events';

    /**
     * Clé primaire de la table.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Constructeur.
     *
     * @param PDO $pdo Instance PDO pour la connexion à la base.
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Récupère tous les événements à venir (date >= aujourd’hui).
     *
     * Les événements sont triés par date croissante.
     *
     * @return EventDTO[] Liste des événements futurs.
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
     * Récupère les événements créés par un auteur donné.
     *
     * Triés par date décroissante (les plus récents en premier).
     *
     * @param int $authorId ID de l’auteur.
     * @return EventDTO[] Liste des événements de l’auteur.
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
     * Récupère un événement via son ID.
     *
     * @param int $id ID de l’événement.
     * @return EventDTO|null DTO de l’événement ou null si non trouvé.
     */
    public function findById(int $id): ?EventDTO
    {
        $row = $this->find($id);
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Crée un nouvel événement en base.
     *
     * N’accepte pas de champ image.
     *
     * @param array<string, mixed> $data Données de l’événement.
     * @return int ID nouvellement créé.
     */
    public function create(array $data): int
    {
        return $this->insert($data);
    }

    /**
     * Supprime tous les événements d’un auteur donné.
     *
     * Utile lors de la suppression d’un utilisateur ou purge.
     *
     * @param int $authorId ID de l’auteur.
     * @return int Nombre de lignes supprimées.
     */
    public function deleteByAuthor(int $authorId): int
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE author_id = :author_id");
        $stmt->execute(['author_id' => $authorId]);
        return $stmt->rowCount();
    }

    /**
     * Convertit un tableau de données SQL en DTO EventDTO.
     *
     * @param array<string, mixed> $data Données issues de la requête SQL.
     * @return EventDTO Objet DTO strictement typé.
     */
    private function hydrate(array $data): EventDTO
    {
        return new EventDTO(
            id: (int)$data['id'],
            titre: $data['titre'],
            resume: $data['resume'],
            description: $data['description'] ?? null,
            date_event: $data['date_event'],
            hours: $data['hours'],
            image: $data['image'] ?? null,
            lieu: $data['lieu'] ?? null,
            created_at: $data['created_at'],
            author_id: (int)$data['author_id'],
        );
    }
}
