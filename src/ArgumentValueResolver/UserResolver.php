<?php

declare(strict_types=1);

namespace App\ArgumentValueResolver;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly ActivityPubManager $activityPubManager,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if ($argument->getType() !== User::class) {
            return;
        }

        if (!$username = $request->attributes->get('username') ?? $request->attributes->get('user')) {
            return;
        }

        // @todo case-insensitive
        if (!$user = $this->repository->findOneByUsername($username)) {
            if (str_ends_with($username, '@'.$this->settingsManager->get('KBIN_DOMAIN'))) {
                $username = ltrim($username, '@');
                $username = str_replace('@'.$this->settingsManager->get('KBIN_DOMAIN'), '', $username);
                $user = $this->repository->findOneByUsername($username);
            }

            if (!$user && substr_count($username, '@') > 1) {
                try {
                    $user = $this->activityPubManager->findActorOrCreate($username);
                } catch (\Exception $e) {
                    $user = null;
                }
            }
        }

        if (!$user instanceof User) {
            throw new NotFoundHttpException();
        }

        yield $user;
    }
}
