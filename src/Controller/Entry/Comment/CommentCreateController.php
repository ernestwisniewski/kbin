<?php declare(strict_types = 1);

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

class CommentCreateController extends AbstractController
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
     * @ParamConverter("parent", options={"mapping": {"parent_comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("comment", subject="entry")
     */
    public function __invoke(
        Magazine $magazine,
        Entry $entry,
        ?EntryComment $parent,
        Request $request,
    ): Response {
        $dto           = (new EntryCommentDto())->createWithParent($entry, $parent);
        $dto->magazine = $magazine;
        $dto->ip       = $request->getClientIp();

        $form = $this->getCreateForm($dto, $parent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleValidCreateRequest($dto, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'entry/comment/_form.html.twig');
        }

        $criteria        = new EntryCommentPageView($this->getPageNb($request));
        $criteria->entry = $entry;

        return $this->getEntryCommentPageResponse('entry/comment/create.html.twig', $criteria, $form, $request, $parent);
    }

    private function getCreateForm(EntryCommentDto $dto, ?EntryComment $parent): FormInterface
    {
        $entry = $dto->entry;

        return $this->createForm(
            EntryCommentType::class,
            $dto,
            [
                'action' => $this->generateUrl(
                    'entry_comment_create',
                    [
                        'magazine_name'     => $entry->magazine->name,
                        'entry_id'          => $entry->getId(),
                        'parent_comment_id' => $parent?->getId(),
                    ]
                ),
            ]
        );
    }

    private function handleValidCreateRequest(EntryCommentDto $dto, Request $request): Response
    {
        $comment = $this->manager->create($dto, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonCommentSuccessResponse($comment);
        }

        return $this->redirectToEntry($comment->entry);
    }
}
