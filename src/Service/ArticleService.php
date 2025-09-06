<?php

declare(strict_types=1);

namespace app\service;

use app\repository\articlerepository;
use app\dto\articledto;

final class articleservice
{
    public function __construct(private articlerepository $articlerepository) {}

    /** champs requis et optionnels (pour lisibilité & évolutivité) */
    private const required_fields  = ['titre', 'resume', 'description', 'date_article', 'hours'];
    private const optional_fields  = ['lieu', 'image'];

    /* =======================
       ======= queries =======
       ======================= */

    /** @return articledto[] */
    public function getupcoming(): array
    {
        /** @var array<array<string,mixed>> $rows */
        return $this->articlerepository->upcoming();
    }

    /** @return articledto[] */
    public function getall(): array
    {
        /** @var array<array<string,mixed>> $rows */
        return $this->articlerepository->all();
    }

    public function getbyid(int $id): ?articledto
    {
        if ($id <= 0) {
            throw new \invalidargumentexception('id doit être positif.');
        }
        return $this->articlerepository->findbyid($id);
    }

    /** @deprecated préfère getbyid(); gardé pour compat temporaire */
    public function find(int $id): ?array
    {
        if ($id <= 0) {
            throw new \invalidargumentexception('id doit être positif.');
        }
        /** @var array<string,mixed>|null $row */
        return $this->articlerepository->find($id);
    }

    /* =======================
       ===== mutations =======
       ======================= */

    /**
     * @param array<string,mixed> $input
     * @param array<string,mixed> $user  (doit contenir au moins 'id')
     * @return array{errors?: array<string,string>, data?: array<string,mixed>}
     */
    public function create(array $input, array $user): array
    {
        $data   = $this->sanitize($input);
        $errors = $this->validate($data);

        if ($errors !== []) {
            return ['errors' => $errors, 'data' => $data];
        }

        try {
            $payload = $this->topersistencearray($data) + [
                'author_id' => isset($user['id']) ? (int)$user['id'] : null,
            ];
            $this->articlerepository->create($payload);
        } catch (\throwable $e) {
            return ['errors' => ['_global' => 'erreur lors de la création.'], 'data' => $data];
        }

        return [];
    }

    /**
     * @param array<string,mixed> $input
     * @return array{errors?: array<string,string>, data?: array<string,mixed>}
     */
    public function update(int $id, array $input): array
    {
        if ($id <= 0) {
            return ['errors' => ['_global' => 'identifiant invalide.'], 'data' => $input];
        }

        $data   = $this->sanitize($input);
        $errors = $this->validate($data);

        if ($errors !== []) {
            return ['errors' => $errors, 'data' => $data];
        }

        try {
            $payload = $this->topersistencearray($data);
            $this->articlerepository->update($id, $payload);
        } catch (\throwable $e) {
            return ['errors' => ['_global' => 'erreur lors de la mise à jour.'], 'data' => $data];
        }

        return [];
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            throw new \invalidargumentexception('id doit être positif.');
        }
        $this->articlerepository->delete($id);
    }

    /* =======================
       ===== helpers =======
       ======================= */

    /**
     * normalise les données utilisateur (sans sécurité xss ici).
     * - trim global
     * - requis: string non vide
     * - optionnels: null si vide
     *
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    private function sanitize(array $input): array
    {
        $out = [];

        foreach (array_merge(self::required_fields, self::optional_fields) as $field) {
            $val = isset($input[$field]) ? trim((string)$input[$field]) : '';
            $out[$field] = $val;
        }

        // optionnels → null si vide
        foreach (self::optional_fields as $opt) {
            if ($out[$opt] === '') {
                $out[$opt] = null;
            }
        }

        return $out;
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,string> champ => message
     */
    private function validate(array $data): array
    {
        $errors = [];

        // requis non vides
        foreach (self::required_fields as $f) {
            if ($data[$f] === '' || $data[$f] === null) {
                $errors[$f] = 'ce champ est obligatoire.';
            }
        }

        // date (yyyy-mm-dd) valide
        if (!empty($data['date_article'])) {
            $d = \datetime::createfromformat('y-m-d', (string)$data['date_article']);
            $ok = $d && $d->format('y-m-d') === $data['date_article'];
            if (!$ok) {
                $errors['date_article'] = "format date invalide (attendu : aaaa-mm-jj)";
            }
        }

        // heure (hh:mm ou hh:mm:ss) → on normalise en hh:mm:ss lors de la persistance
        if (!empty($data['hours'])) {
            $h = \datetime::createfromformat('h:i:s', (string)$data['hours'])
                ?: \datetime::createfromformat('h:i', (string)$data['hours']);
            if (!$h) {
                $errors['hours'] = "format heure invalide (attendu : hh:mm ou hh:mm:ss)";
            }
        }

        return $errors;
    }

    /**
     * transforme les données validées en format prêt pour la db.
     * - date_article : yyyy-mm-dd
     * - hours        : normalisé en hh:mm:ss
     *
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function topersistencearray(array $data): array
    {
        $out = $data;

        // hours → hh:mm:ss
        if (!empty($out['hours'])) {
            $h = \datetime::createfromformat('h:i:s', (string)$out['hours'])
                ?: \datetime::createfromformat('h:i', (string)$out['hours']);
            if ($h) {
                $out['hours'] = $h->format('h:i:s');
            }
        }

        // date_article → garde yyyy-mm-dd tel quel (déjà validé)
        // optionnels: null accepté

        return $out;
    }
}
