<?php declare(strict_types=1);

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentEditController extends AbstractController
{
    use CommentResponseTrait;

    public function __construct(
        private EntryCommentManager $manager,
        private EntryCommentRepository $repository,
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="comment")
     */
    public function __invoke(
        Magazine $magazine,
        Entry $entry,
        EntryComment $comment,
        Request $request,
    ): Response {
        $dto = $this->manager->createDto($comment);

        $form = $this->getForm($dto, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleValidRequest($dto, $comment, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'entry/comment/_form.html.twig', ['comment' => $comment]);
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
                        'entry_id'      => $comment->entry->getId(),
                        'comment_id'    => $comment->getId(),
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
