<?php

declare(strict_types=1);

namespace App\Controller\Entry;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\Magazine;
use App\Repository\MagazineRepository;
use App\Service\EntryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryChangeMagazineController extends AbstractController
{
    public function __construct(
        private readonly EntryManager $manager,
        private readonly MagazineRepository $repository
    ) {
    }

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('entry', options: ['mapping' => ['entry_id' => 'id']])]
    #[IsGranted('moderate', subject: 'entry')]
    public function __invoke(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('change_magazine', $request->request->get('token'));

        $newMagazine = $this->repository->findOneByName($request->get('change_magazine')['new_magazine']);

        $this->manager->changeMagazine($entry, $newMagazine);

        return $this->redirectToRefererOrHome($request);
    }
}
