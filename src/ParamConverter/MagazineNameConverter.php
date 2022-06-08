<?php declare(strict_types=1);

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
    public function __construct(private MagazineRepository $repository)
    {
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $magazineName = $request->attributes->get('magazine_name') ?? $request->attributes->get('name');

        // @todo case-insensitive
        if (!$magazine = $this->repository->findOneByName($magazineName)) {
            throw new NotFoundHttpException();
        }

        $request->attributes->set($configuration->getName(), $magazine);
    }

    #[Pure] public function supports(ParamConverter $configuration): bool
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        if ($configuration->getClass() !== Magazine::class) {
            return false;
        }

        // @todo test coverage
        return false;
    }
}
