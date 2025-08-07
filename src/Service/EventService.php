<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\EventRepository;

/**
 * Service de gestion métier des événements.
 *
 * Encapsule la logique de création, mise à jour, suppression et validation
 * des événements, en déléguant la persistance au repository.
 */
class EventService
{
    private EventRepository $eventRepository;

    /**
     * Constructeur.
     *
     * @param EventRepository $eventRepository Instance du repository d’événements.
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Récupère tous les événements à venir.
     *
     * @return array[] Liste d’événements (DTO ou tableau associatif).
     */
    public function getUpcoming(): array
    {
        return $this->eventRepository->upcoming();
    }

    /**
     * Récupère un événement par son ID.
     *
     * @param int $id Identifiant de l’événement.
     * @return array|null Données de l’événement ou null si non trouvé.
     */
    public function find(int $id): ?array
    {
        return $this->eventRepository->find($id);
    }

    /**
     * Crée un nouvel événement à partir des données du formulaire et de l’utilisateur.
     *
     * Effectue la sanitation et la validation des données.
     *
     * @param array $input Données brutes issues du formulaire.
     * @param array $user  Données utilisateur, doit contenir au minimum un identifiant.
     * @return array Tableau contenant 'errors' (tableau associatif champ => message) si erreurs, sinon vide.
     */
    public function create(array $input, array $user): array
    {
        $data   = $this->sanitize($input);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            return ['errors' => $errors, 'data' => $data];
        }

        $this->eventRepository->create([
            ...$data,
            'author_id'  => $user['id'] ?? null,
        ]);
        return [];
    }

    /**
     * Met à jour un événement existant.
     *
     * Effectue la sanitation et la validation des données.
     *
     * @param int $id Identifiant de l’événement à mettre à jour.
     * @param array $input Données mises à jour issues du formulaire.
     * @return array Tableau contenant 'errors' si erreurs, sinon vide.
     */
    public function update(int $id, array $input): array
    {
        $data   = $this->sanitize($input);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            return ['errors' => $errors, 'data' => $data];
        }

        $this->eventRepository->update($id, [
            ...$data,
            // Remplacement du 'T' ISO par espace si présent (convention date SQL)
            'date_event' => str_replace('T', ' ', $data['date_event']),
        ]);
        return [];
    }

    /**
     * Supprime un événement par son ID.
     *
     * @param int $id Identifiant de l’événement à supprimer.
     * @return void
     */
    public function delete(int $id): void
    {
        $this->eventRepository->delete($id);
    }

    /**
     * Récupère tous les événements/articles (admin/dashboard).
     *
     * @return EventDTO[]
     */
    public function getAll(): array
    {
        $rows = $this->eventRepository->all();
        return array_map(function ($row) {
            // On hydrate comme dans EventRepository
            return new \App\Dto\EventDTO(
                id: (int)$row['id'],
                titre: $row['titre'],
                resume: $row['resume'],
                description: $row['description'] ?? null,
                date_event: $row['date_event'],
                hours: $row['hours'],
                lieu: $row['lieu'] ?? null,
                image: $row['image'] ?? null,
                created_at: $row['created_at'],
                author_id: (int)$row['author_id'],
            );
        }, $rows);
    }

    /**
     * Nettoie et prépare les données brutes du formulaire.
     *
     * Trim et strip_tags sauf pour la description qui conserve son contenu.
     *
     * @param array $input Données brutes.
     * @return array Données nettoyées.
     */
    private function sanitize(array $input): array
    {
        $fields = ['titre', 'description', 'date_event', 'hours', 'lieu', 'resume'];
        $clean = [];
        foreach ($fields as $field) {
            $value = trim($input[$field] ?? '');
            $clean[$field] = $field === 'description'
                ? $value
                : strip_tags($value);
        }
        return $clean;
    }

    /**
     * Valide les données nettoyées.
     *
     * Vérifie la présence obligatoire des champs et le format date/heure.
     *
     * @param array $data Données nettoyées.
     * @return array Tableau associatif champ => message d’erreur, vide si valide.
     */
    private function validate(array $data): array
    {
        $errors = [];
        foreach (['titre', 'description', 'date_event', 'hours', 'lieu'] as $field) {
            if ($data[$field] === '') {
                $errors[$field] = 'Ce champ est obligatoire.';
            }
        }
        // Format date attendu : YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_event'])) {
            $errors['date_event'] = "Format date invalide (attendu : AAAA-MM-JJ)";
        }
        // Format heure attendu : HH:MM ou HH:MM:SS
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data['hours'])) {
            $errors['hours'] = "Format heure invalide (attendu : HH:MM ou HH:MM:SS)";
        }
        return $errors;
    }
}
