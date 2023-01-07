<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ReputationRepository;

class ReputationManager
{
    public function resolveType(?string $value, ?string $default = null): string
    {
        $routes = [
            'threads' => ReputationRepository::TYPE_ENTRY,
            'comments' => ReputationRepository::TYPE_ENTRY_COMMENT,
            'posts' => ReputationRepository::TYPE_POST,
            'replies' => ReputationRepository::TYPE_POST_COMMENT,

            'treści' => ReputationRepository::TYPE_ENTRY,
            'komentarze' => ReputationRepository::TYPE_ENTRY_COMMENT,
            'wpisy' => ReputationRepository::TYPE_POST,
            'odpowiedzi' => ReputationRepository::TYPE_POST_COMMENT,
        ];

        return $routes[$value] ?? $routes[$default ?? ReputationRepository::TYPE_ENTRY];
    }
}
