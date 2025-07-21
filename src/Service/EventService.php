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

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imgResult = $this->handleImageUpload($_FILES['image']);
            if (isset($imgResult['error'])) {
                $errors['image'] = $imgResult['error'];
            } else {
                $imagePath = $imgResult['path'];
            }
        }

        if (!empty($errors)) {
            return ['errors' => $errors, 'data' => $data];
        }

        $this->eventModel->create([
            ...$data,
            'image'      => $imagePath,
            'author_id'  => $user['id'] ?? null,
        ]);
        return [];
    }

    // ---- Méthode d'upload sécurisée ----
    private function handleImageUpload(array $file): array
    {
        // Taille max 2 Mo
        $maxSize = 2 * 1024 * 1024;
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Erreur de téléchargement.'];
        }
        if ($file['size'] > $maxSize) {
            return ['error' => 'Image trop volumineuse (max 2 Mo).'];
        }

        // Vérification MIME
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            return ['error' => 'Format d\'image non autorisé (jpg, png, webp seulement).'];
        }

        // Génère un nom de fichier unique et sûr
        $ext = $allowed[$mime];
        $filename = 'event_' . uniqid('', true) . '.' . $ext;
        $targetDir = dirname(__DIR__, 2) . '/public/uploads/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $targetPath = $targetDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['error' => 'Impossible de sauvegarder l\'image.'];
        }

        // Retourne le chemin relatif web (pour affichage)
        return ['path' => '/uploads/' . $filename];
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
