<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArticleRepository;
use App\Dto\ArticleDTO;

/**
 * Service de gestion métier des événements.
 *
 * Encapsule la logique de création, mise à jour, suppression et validation
 * des événements, en déléguant la persistance au repository.
 */
class ArticleService
{
    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * Récupère tous les événements à venir.
     *
     * @return array<ArticleDTO> Liste d’articles à venir sous forme de DTO.
     */
    public function getUpcoming(): array
    {
        // Si ton repository retourne déjà des DTO, laisse tel quel.
        // Sinon, hydrate comme dans getAll().
        /** @var array<ArticleDTO> $rows */
        $rows = $this->articleRepository->upcoming();
        return $rows;
    }

    /**
     * Récupère un événement par son ID.
     *
     * @param int $id Identifiant de l’événement.
     * @return array<string,mixed>|null Données de l’événement (row) ou null si non trouvé.
     */
    public function find(int $id): ?array
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID doit être positif');
        }
        /** @var array<string,mixed>|null $row */
        $row = $this->articleRepository->find($id);
        return $row;
    }

    /**
     * Crée un nouvel événement à partir des données du formulaire et de l’utilisateur.
     *
     * @param array<string,mixed> $input Données brutes issues du formulaire.
     * @param array<string,mixed> $user  Données utilisateur (doit contenir au moins 'id').
     * @return array{errors?: array<string,string>, data?: array{
     *     titre:string, resume:string, description:string, date_event:string, hours:string, lieu:string
     * }}
     */
    public function create(array $input, array $user): array
    {
        $data   = $this->sanitize($input);
        $errors = $this->validate($data);

        if ($errors !== []) {
            return ['errors' => $errors, 'data' => $data];
        }

        $this->articleRepository->create([
            ...$data,
            'author_id'  => $user['id'] ?? null,
        ]);

        return []; // pas d’erreurs
    }

    /**
     * Met à jour un événement existant.
     *
     * @param int $id Identifiant de l’événement à mettre à jour.
     * @param array<string,mixed> $input Données mises à jour issues du formulaire.
     * @return array{errors?: array<string,string>, data?: array{
     *     titre:string, resume:string, description:string, date_event:string, hours:string, lieu:string
     * }}
     */
    public function update(int $id, array $input): array
    {
        $data   = $this->sanitize($input);
        $errors = $this->validate($data);

        if ($errors !== []) {
            return ['errors' => $errors, 'data' => $data];
        }

        $this->articleRepository->update($id, [
            ...$data,
            // si besoin (selon ton format SQL)
            'date_event' => \str_replace('T', ' ', $data['date_event']),
        ]);

        return []; // pas d’erreurs
    }

    /**
     * Supprime un événement par son ID.
     */
    public function delete(int $id): void
    {
        $this->articleRepository->delete($id);
    }

    /**
     * Récupère tous les événements/articles (admin/dashboard).
     *
     * @return array<ArticleDTO>
     */
    public function getAll(): array
    {
        /** @var array<array<string,mixed>> $rows */
        $rows = $this->articleRepository->all();

        return array_map(
            /**
             * @param array<string,mixed> $row
             */
            static function (array $row): ArticleDTO {
                return new ArticleDTO(
                    id: (int)$row['id'],
                    titre: (string)$row['titre'],
                    resume: (string)$row['resume'],
                    description: isset($row['description']) ? (string)$row['description'] : null,
                    date_event: (string)$row['date_event'],
                    hours: (string)$row['hours'],
                    lieu: isset($row['lieu']) ? (string)$row['lieu'] : null,
                    image: isset($row['image']) ? (string)$row['image'] : null,
                    created_at: (string)$row['created_at'],
                    author_id: (int)$row['author_id'],
                    author: $row['author'] ?? null
                );
            },
            $rows
        );
    }

    /**
     * Rend les détails d'un article par son ID.
     */
    public function getById(int $id): ?ArticleDTO
    {
        /** @var array<string,mixed>|null $row */
        $row = $this->articleRepository->find($id);

        if ($row === null) {
            return null;
        }

        return new ArticleDTO(
            id: (int)$row['id'],
            titre: (string)$row['titre'],
            resume: (string)$row['resume'],
            description: isset($row['description']) ? (string)$row['description'] : null,
            date_event: (string)$row['date_event'],
            hours: (string)$row['hours'],
            lieu: isset($row['lieu']) ? (string)$row['lieu'] : null,
            image: isset($row['image']) ? (string)$row['image'] : null,
            created_at: (string)$row['created_at'],
            author_id: (int)$row['author_id'],
            author: $row['author'] ?? null
        );
    }

    /**
     * Nettoie et prépare les données brutes du formulaire.
     *
     * @param array<string,mixed> $input
     * @return array{
     *   titre:string,
     *   resume:string,
     *   description:string,
     *   date_event:string,
     *   hours:string,
     *   lieu:string
     * }
     */
    private function sanitize(array $input): array
    {
        $fields = ['titre', 'description', 'date_event', 'hours', 'lieu', 'resume'];
        $clean = [
            'titre'        => '',
            'resume'       => '',
            'description'  => '',
            'date_event' => '',
            'hours'        => '',
            'lieu'         => '',
        ];

        foreach ($fields as $field) {
            $value = \trim((string)($input[$field] ?? ''));
            $clean[$field] = ($field === 'description') ? $value : \strip_tags($value);
        }

        /** @var array{
         *   titre:string, resume:string, description:string, date_event:string, hours:string, lieu:string
         * } $clean */
        return $clean;
    }

    /**
     * Valide les données nettoyées.
     *
     * @param array{
     *   titre:string, resume:string, description:string, date_event:string, hours:string, lieu:string
     * } $data
     * @return array<string,string> Tableau associatif champ => message d’erreur, vide si valide.
     */
    private function validate(array $data): array
    {
        $errors = [];

        foreach (['titre', 'resume', 'description', 'date_event', 'hours', 'lieu'] as $field) {
            if ($data[$field] === '') {
                $errors[$field] = 'Ce champ est obligatoire.';
            }
        }

        // YYYY-MM-DD
        if ($data['date_event'] !== '' && !\preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_event'])) {
            $errors['date_event'] = "Format date invalide (attendu : AAAA-MM-JJ)";
        }
        // HH:MM ou HH:MM:SS
        if ($data['hours'] !== '' && !\preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data['hours'])) {
            $errors['hours'] = "Format heure invalide (attendu : HH:MM ou HH:MM:SS)";
        }

        return $errors;
    }
}
