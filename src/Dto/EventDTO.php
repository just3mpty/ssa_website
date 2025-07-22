<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Event Data Transfer Object (DTO)
 * - Transport pur, typé, immutable (readonly)
 */
class EventDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $titre,
        public readonly string $description,
        public readonly string $date_event,    // 'YYYY-MM-DD'
        public readonly string $hours,         // 'HH:MM:SS'
        public readonly ?string $lieu,
        public readonly string $created_at,
        public readonly int $author_id,
    ) {}
}
