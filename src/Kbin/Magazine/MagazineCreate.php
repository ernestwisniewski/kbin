<?php

declare(strict_types=1);

namespace App\Kbin\Magazine;

use App\Entity\Magazine;
use App\Entity\User;
use App\Kbin\Magazine\DTO\MagazineDto;
use App\Kbin\Magazine\Factory\MagazineFactory;
use App\Service\ActivityPub\KeysGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class MagazineCreate
{
    public function __construct(
        private MagazineSubscribe $magazineSubscribe,
        private MagazineFactory $factory,
        private RateLimiterFactory $magazineLimiter,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(MagazineDto $dto, User $user, bool $rateLimit = true): Magazine
    {
        if ($rateLimit) {
            $limiter = $this->magazineLimiter->create($dto->ip);
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }

        $magazine = $this->factory->createFromDto($dto, $user);
        $magazine->apId = $dto->apId;
        $magazine->apProfileId = $dto->apProfileId;

        if (!$dto->apId) {
            $magazine = KeysGenerator::generate($magazine);
            $magazine->apProfileId = $this->urlGenerator->generate(
                'ap_magazine',
                ['name' => $magazine->name],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $this->entityManager->persist($magazine);
        $this->entityManager->flush();

        if (!$dto->apId) {
            ($this->magazineSubscribe)($magazine, $user);
        }

        return $magazine;
    }
}
