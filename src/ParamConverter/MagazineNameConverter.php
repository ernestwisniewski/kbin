<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use JetBrains\PhpStorm\Pure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MagazineNameConverter implements ParamConverterInterface
{
    public function __construct(private readonly MagazineRepository $repository)
    {
    }

    public function apply(Request $request, ParamConverter $configuration): void
    {
        $magazineName = $request->attributes->get('magazine_name') ?? $request->attributes->get('name');

        $magazine = $this->repository->findOneByName($magazineName);

        $request->attributes->set($configuration->getName(), $magazine);
    }

    #[Pure]
    public function supports(ParamConverter $configuration): bool
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        if (Magazine::class !== $configuration->getClass()) {
            return false;
        }

        // @todo test coverage
        return true;
    }
}
