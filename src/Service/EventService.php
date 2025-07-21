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
            'date_event' => str_replace('T', ' ', $data['date_event']),
            'image'      => null, // À gérer si besoin
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
        // Adaptable : stricte, mais permissive pour 'description'
        $fields = ['titre', 'description', 'date_event', 'lieu'];
        $clean = [];
        foreach ($fields as $field) {
            $value = trim($input[$field] ?? '');
            // description : tu veux du HTML ou pas ? Ajoute strip_tags si besoin
            $clean[$field] = $field === 'description'
                ? $value
                : strip_tags($value);
        }
        return $clean;
    }

    private function validate(array $data): array
    {
        $errors = [];
        foreach (['titre', 'description', 'date_event', 'lieu'] as $field) {
            if ($data[$field] === '') $errors[$field] = 'Ce champ est obligatoire.';
        }
        // Gère le format HTML5 <input type="datetime-local">
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $data['date_event'])) {
            $errors['date_event'] = "Format date/heure invalide (attendu : AAAA-MM-JJTHH:MM)";
        }
        // Optionnel : vérifie que la date n'est pas passée
        // if (strtotime($data['date_event']) < time()) $errors['date_event'] = "La date doit être future.";
        return $errors;
    }
}
