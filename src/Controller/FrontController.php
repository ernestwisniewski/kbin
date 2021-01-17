<?php

namespace App\Controller;

use App\Repository\EntryRepository;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends AbstractController
{
    public function front(EntryRepository $entryRepository): Response
    {
        return $this->render('front/front.html.twig', [
            'entries' => $entryRepository->findAll()
        ]);
    }
}
