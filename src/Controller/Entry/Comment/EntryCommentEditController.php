<?php

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\DTO\EntryCommentDto;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Form\EntryCommentType;
use App\PageView\EntryCommentPageView;
use App\Repository\EntryCommentRepository;
use App\Service\EntryCommentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntryCommentEditController extends AbstractController
{
    use EntryCommentResponseTrait;

    public function __construct(
        private readonly EntryCommentManager $manager,
        private readonly EntryCommentRepository $repository,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('edit', subject: 'comment')]
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['entry_id' => 'id'])]
        Entry $entry,
        #[MapEntity(mapping: ['comment_id' => 'id'])]
        EntryComment $comment,
        Request $request,
    ): Response {
        $dto = $this->manager->createDto($comment);

        $form = $this->getForm($dto, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            return $this->handleValidRequest($dto, $comment, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse(
                $form,
                'entry/comment/_form_comment.html.twig',
                ['comment' => $comment, 'entry' => $entry, 'edit' => true]
            );
        }

        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->entry = $entry;

        return $this->getEntryCommentPageResponse('entry/comment/edit.html.twig', $criteria, $form, $request, $comment);
    }

    private function getForm(EntryCommentDto $dto, EntryComment $comment): FormInterface
    {
        return $this->createForm(
            EntryCommentType::class,
            $dto,
            [
                'action' => $this->generateUrl(
                    'entry_comment_edit',
                    [
                        'magazine_name' => $comment->magazine->name,
                        'entry_id' => $comment->entry->getId(),
                        'comment_id' => $comment->getId(),
                    ]
                ),
            ]
        );
    }

    private function handleValidRequest(EntryCommentDto $dto, EntryComment $comment, Request $request): Response
    {
        $comment = $this->manager->edit($comment, $dto);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonCommentSuccessResponse($comment);
        }

        return $this->redirectToEntry($comment->entry);
    }
}
