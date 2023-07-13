<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Controller\AbstractController;
use App\Entity\MessageThread;
use App\Form\MessageType;
use App\Service\MessageManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessageThreadController extends AbstractController
{
    public function __construct(private readonly MessageManager $manager)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('show', subject: 'thread', statusCode: 403)]
    public function __invoke(MessageThread $thread, Request $request): Response
    {
        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->toMessage($form->getData(), $thread, $this->getUserOrThrow());

            return $this->redirectToRoute(
                'messages_front',
                ['id' => $thread->getId()]
            );
        }

        $this->manager->readMessages($thread, $this->getUserOrThrow());

        return $this->render(
            'messages/single.html.twig',
            [
                'user' => $this->getUserOrThrow(),
                'thread' => $thread,
                'form' => $form->createView(),
            ]
        );
    }
}
