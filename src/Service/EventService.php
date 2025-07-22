<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Event;

class EventService
{
    private Event $eventModel;

    public function __construct(Event $eventModel)
    {
        $this->eventModel = $eventModel;
    }

    /** Récupère tous les événements à venir */
    public function getUpcoming(): array
    {
        return $this->eventModel->upcoming();
    }

    /** Récupère un événement par son ID */
    public function find(int $id): ?array
    {
        return $this->eventModel->find($id);
    }

    /** Crée un événement à partir de données de formulaire + user */
    public function create(array $input, array $user): array
    {
        $data   = $this->sanitize($input);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            return ['errors' => $errors, 'data' => $data];
        }

        $this->eventModel->create([
            ...$data,
            'author_id'  => $user['id'] ?? null,
        ]);
        return [];
    }

    /** Met à jour un événement */
    public function update(int $id, array $input): array
    {
        $data   = $this->sanitize($input);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            return ['errors' => $errors, 'data' => $data];
        }

        $this->eventModel->update($id, [
            ...$data,
            'date_event' => str_replace('T', ' ', $data['date_event']),
        ]);
        return [];
    }

    /** Supprime un événement */
    public function delete(int $id): void
    {
        $this->eventModel->delete($id);
    }

    /** --------- Validation / sanitation interne --------- */

    private function sanitize(array $input): array
    {
        $fields = ['titre', 'description', 'date_event', 'hours', 'lieu'];
        $clean = [];
        foreach ($fields as $field) {
            $value = trim($input[$field] ?? '');
            $clean[$field] = $field === 'description'
                ? $value
                : strip_tags($value);
        }
        return $clean;
    }

    private function validate(array $data): array
    {
        $errors = [];
        foreach (['titre', 'description', 'date_event', 'hours', 'lieu'] as $field) {
            if ($data[$field] === '') $errors[$field] = 'Ce champ est obligatoire.';
        }
        // Date format : YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_event'])) {
            $errors['date_event'] = "Format date invalide (attendu : AAAA-MM-JJ)";
        }
        // Heure format : HH:MM ou HH:MM:SS
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data['hours'])) {
            $errors['hours'] = "Format heure invalide (attendu : HH:MM ou HH:MM:SS)";
        }
        return $errors;
    }
}
