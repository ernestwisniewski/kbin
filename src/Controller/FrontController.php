<?php declare(strict_types = 1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EntryRepository;
use App\Repository\Criteria;

class FrontController extends AbstractController
{
    public function front(EntryRepository $entryRepository, Request $request): Response
    {
        $criteria = new Criteria((int) $request->get('strona', 1));

        return $this->render(
            'front/front.html.twig',
            [
                'entries' => $entryRepository->findByCriteria($criteria),
            ]
        );
    }
}
