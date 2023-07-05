<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Controller\AbstractController;
use App\Repository\MessageThreadRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessageThreadListController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    public function __invoke(MessageThreadRepository $repository, Request $request): Response
    {
        return $this->render(
            'messages/front.html.twig',
            [
                'threads' => $repository->findUserMessages($this->getUser(), $this->getPageNb($request)),
            ]
        );
    }
}
