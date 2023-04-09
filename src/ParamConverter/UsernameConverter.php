<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ActivityPubManager;
use App\Service\SettingsManager;
use JetBrains\PhpStorm\Pure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UsernameConverter implements ParamConverterInterface
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly ActivityPubManager $activityPubManager,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function apply(Request $request, ParamConverter $configuration): void
    {
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

        if (!$user) {
            throw new NotFoundHttpException();
        }

        $request->attributes->set($configuration->getName(), $user);
    }

    #[Pure]
    public function supports(ParamConverter $configuration): bool
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        if (User::class !== $configuration->getClass()) {
            return false;
        }

        return true;
    }
}
