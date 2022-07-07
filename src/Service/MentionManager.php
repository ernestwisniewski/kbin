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
            $this->byPrefix('@'),
            $this->byPrefix('\\/u\\/'),
            $this->byUserPrefix()
        );

        return count($result) ? array_unique($result) : null;
    }

    private function byPrefix(string $prefix): array
    {
        preg_match_all("/\B{$prefix}(\w{2,35})/", $this->val, $matches);

        return count($matches[1]) ? array_unique(array_values($matches[1])) : [];
    }

    private function byUserPrefix(): array
    {
        preg_match_all('/\bu\\/(\w{2,35})/', $this->val, $matches);

        return count($matches[1]) ? array_unique(array_values($matches[1])) : [];
    }
}
