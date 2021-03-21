<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\MessageDto;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageThreadRepository;
use App\Service\MessageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends AbstractController
{
    private MessageManager $messageManager;
    private EntityManagerInterface $entityManager;

    public function __construct(MessageManager $messageManager, EntityManagerInterface $entityManager)
    {
        $this->messageManager = $messageManager;
        $this->entityManager  = $entityManager;
    }

    public function threads(MessageThreadRepository $repository, Request $request): Response
    {
        $messageThreads = $repository->findUserMessages($this->getUser(),  (int) $request->get('strona', 1));

        return $this->render('user/message/threads.html.twig', [
            'threads' => $messageThreads,
        ]);
    }

    public function createThread(User $receiver, Request $request): Response
    {
        $dto = new MessageDto();

        $form = $this->createForm(MessageType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread = $this->messageManager->toThread($dto, $this->getUserOrThrow(), $receiver);

//            return $this->redirectToRoute(
//                'message_thread',
//                [
//                    'id' => $thread->getId(),
//                ]
//            );
        }

        return new Response('');
    }
}
