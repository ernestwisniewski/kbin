<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\EntryComment;
use App\Entity\PostComment;
use App\Repository\UserRepository;

class MentionManager
{
    const ALL = 1;
    const LOCAL = 2;
    const REMOTE = 3;

    public function __construct(private UserRepository $userRepository, private SettingsManager $settingsManager)
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

    public function extract(string $val, $type = self::ALL): ?array
    {
        $this->val = $val;

        $result = match ($type) {
            self::ALL => array_merge($this->byApPrefix(), $this->byPrefix()),
            self::LOCAL => $this->byPrefix(),
            self::REMOTE => $this->byApPrefix()
        };

        $result = array_map(fn($val) => trim($val), $result);

        return count($result) ? array_unique($result) : null;
    }

    private function byPrefix(): array
    {
        preg_match_all("/\B@([a-zA-Z0-9_-]{2,30})./", $this->val, $matches);
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
            '/(@\w{2,30})(@)(([a-z0-9|-]+\.)*[a-z0-9|-]+\.[a-z]+)/',
            $this->val,
            $matches
        );

        return count($matches[0]) ? array_unique(array_values($matches[0])) : [];
    }

    public function handleChain(ActivityPubActivityInterface $activity): array
    {
        $subject = match (true) {
            $activity instanceof EntryComment => $activity->parent ?? $activity->entry,
            $activity instanceof PostComment => $activity->parent ?? $activity->post,
            default => throw new \LogicException(),
        };

        $activity->mentions = array_unique(
            array_merge($activity->mentions ?? [], $this->extract($activity->body) ?? [])
        );

        $subjectActor = ['@'.ltrim($subject->user->username, '@')];

        $result = array_unique(
            array_merge(
                empty($subject->mentions) ? [] : $subject->mentions,
                empty($activity->mentions) ? [] : $activity->mentions,
                $subjectActor
            )
        );

        return array_filter(
            $result,
            fn($val) => !in_array(
                $val,
                [
                    '@'.$activity->user->username,
                    '@'.$activity->user->username.'@'.$this->settingsManager->get('KBIN_DOMAIN'),
                ]
            )
        );
    }

    public function joinMentionsToBody(string $body, array $mentions): string
    {
        $current = $this->extract($body) ?? [];

        $join = array_unique(array_merge(array_diff($mentions, $current)));

        if (!empty($join)) {
            $body = implode(' ', $join).' '.$body;
        }

        return $body;
    }
}
