<?php

declare(strict_types=1);

namespace App\Controller\Message;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Form\MessageType;
use App\Service\MessageManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MessageCreateThreadController extends AbstractController
{
    public function __construct(private readonly MessageManager $manager)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('message', subject: 'receiver')]
    public function __invoke(User $receiver, Request $request): Response
    {
        if ($receiver->apId) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->toThread($form->getData(), $this->getUserOrThrow(), $receiver);

            return $this->redirectToRoute(
                'messages_front'
            );
        }

        return $this->render(
            'user/message.html.twig',
            [
                'user' => $receiver,
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
