<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Contracts\ActivityPubActivityInterface;
use App\Entity\EntryComment;
use App\Entity\PostComment;
use App\Repository\UserRepository;
use App\Utils\RegPatterns;

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
        preg_match_all("/\B@([a-zA-Z0-9_-]{1,30})./", $this->val, $matches);
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
            '/(@\w{1,30})(@)(([a-z0-9|-]+\.)*[a-z0-9|-]+\.[a-z]+)/',
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

        $result = array_filter(
            $result,
            function ($val) {
                preg_match(RegPatterns::LOCAL_USER, $val, $l);
                return preg_match(RegPatterns::AP_USER, $val) || $val === $l[0] ?? '';
            }
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
        $current = $this->addHandle($current);
        $mentions = $this->addHandle($mentions);

        $join = array_unique(array_merge(array_diff($mentions, $current)));

        if (!empty($join)) {
            $body .= PHP_EOL.PHP_EOL.implode(' ', $join);
        }

        return $body;
    }

    public function clearLocal(?array $mentions): array
    {
        if (null === $mentions) {
            return [];
        }

        $domain = '@'.$this->settingsManager->get('KBIN_DOMAIN');

        $mentions = array_map(fn($val) => preg_replace('/'.preg_quote($domain, '/').'$/', '', $val), $mentions);

        $mentions = array_map(fn($val) => ltrim($val, '@'), $mentions);

        return array_filter($mentions, fn($val) => !str_contains($val, '@'));
    }

    public function addHandle(array $mentions): array
    {
        return array_map(
            fn($val) => substr_count($val, '@') < 2 ? $val.'@'.$this->settingsManager->get('KBIN_DOMAIN') : $val,
            $mentions
        );
    }
}
