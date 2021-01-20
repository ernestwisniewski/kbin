<?php declare(strict_types = 1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\CommentManager;
use App\Form\CommentType;
use App\Entity\Magazine;
use App\DTO\CommentDto;
use App\Entity\Comment;
use App\Entity\Entry;

class CommentController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     */
    public function createComment(Magazine $magazine, Entry $entry, ?Comment $comment, Request $request, CommentManager $commentManager): Response
    {
        $commentDto = new CommentDto();
        $commentDto->setEntry($entry);

        $form = $this->createForm(CommentType::class, $commentDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentManager->createComment($commentDto, $this->getUserOrThrow());
            $this->entityManager->flush();

            return $this->redirectToRoute(
                'entry',
                [
                    'magazine_name' => $magazine->getName(),
                    'entry_id'      => $entry->getId(),
                ]
            );
        }

        return $this->render(
            'comment/create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function commentForm(string $magazineName, int $entryId, int $commentId = null): Response
    {
        $routeParams = [
            'magazine_name' => $magazineName,
            'entry_id'      => $entryId,
        ];

        if ($commentId !== null) {
            $routeParams['comment_id'] = $commentId;
        }

        $form = $this->createForm(CommentType::class, null, ['action' => $this->generateUrl('comment_create', $routeParams)]);

        return $this->render(
            'comment/_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
