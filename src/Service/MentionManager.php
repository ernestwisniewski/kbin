<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;

class MentionManager
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    private string $val;

    public function getUsersFromArray(?array $users): array
    {
        if ($users) {
            return $this->userRepository->findByUsernames($users);
        }

        return [];
    }

    public function extract(string $val): ?array
    {
        $this->val = $val;

        $result = array_merge(
            $this->byApPrefix(),
            $this->byPrefix(),
        );

        return count($result) ? array_unique($result) : null;
    }

    private function byPrefix(): array
    {
        preg_match_all("/\B@(\w{2,35})./", $this->val, $matches);
        $results = array_filter($matches[0], fn($val) => !str_ends_with($val, '@'));

        $results = array_map(function ($val) {
            if (str_ends_with($val, '@')) {
                return substr($val, 0, -1);
            }

            return $val;
        }, $results);

        return count($results) ? array_unique(array_values($results)) : [];
    }

    private function byApPrefix(): array
    {
        preg_match_all(
            '/(@\w{2,35})(@)(([a-z0-9|-]+\.)*[a-z0-9|-]+\.[a-z]+)/',
            $this->val,
            $matches
        );

        return count($matches[0]) ? array_unique(array_values($matches[0])) : [];
    }
}
