<?php declare(strict_types=1);

namespace App\Controller\Message;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Form\MessageType;
use App\Service\MessageManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageCreateThreadController extends AbstractController
{
    public function __construct(
        private MessageManager $manager,
    ) {
    }

    /**
     * @IsGranted("message", subject="receiver")
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(User $receiver, Request $request): Response
    {
        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->toThread($form->getData(), $this->getUserOrThrow(), $receiver);

            return $this->redirectToRoute(
                'user_profile_messages'
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
