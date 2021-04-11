<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\MessageDto;
use App\Entity\MessageThread;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageThreadRepository;
use App\Service\MessageManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends AbstractController
{
    public function __construct(
        private MessageManager $manager,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function threads(MessageThreadRepository $repository, Request $request): Response
    {
        $messageThreads = $repository->findUserMessages($this->getUser(), $this->getPageNb($request));

        return $this->render(
            'user/profile/messages.html.twig',
            [
                'threads' => $messageThreads,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("show", subject="thread", statusCode=403)
     */
    public function thread(MessageThread $thread, Request $request): Response
    {
        $dto = new MessageDto();

        $form = $this->createForm(MessageType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $this->manager->toMessage($dto, $thread, $this->getUserOrThrow());

            return $this->redirectToRoute(
                'user_profile_message',
                ['id' => $thread->getId()]
            );
        }

        $this->manager->readMessages($thread, $this->getUserOrThrow());

        return $this->render(
            'user/profile/message.html.twig',
            [
                'user'   => $this->getUserOrThrow(),
                'thread' => $thread,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * @IsGranted("message", subject="receiver")
     * @IsGranted("ROLE_USER")
     */
    public function createThread(User $receiver, Request $request): Response
    {
        $dto = new MessageDto();

        $form = $this->createForm(MessageType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread = $this->manager->toThread($dto, $this->getUserOrThrow(), $receiver);

            return $this->redirectToRoute(
                'user_profile_messages'
            );
        }

        return $this->render(
            'user/message.html.twig',
            [
                'user' => $receiver,
                'form' => $form->createView(),
            ]
        );
    }
}
