<?php declare(strict_types=1);

namespace App\Controller\Entry;

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentController extends AbstractController
{
    public function __construct(
        private EntryCommentManager $manager,
        private EntryCommentRepository $repository,
    ) {
    }

    public function front(?Magazine $magazine, ?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));

        if ($magazine) {
            $criteria->magazine = $params['magazine'] = $magazine;
        }

        $params['comments'] = $this->repository->findByCriteria($criteria);

        $this->repository->hydrate(...$params['comments']);
        $this->repository->hydrateChildren(...$params['comments']);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function subscribed(?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));
        $criteria->subscribed = true;

        $params['comments'] = $this->repository->findByCriteria($criteria);

        $this->repository->hydrate(...$params['comments']);
        $this->repository->hydrateChildren(...$params['comments']);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function moderated(?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($criteria->resolveSort($sortBy))
            ->setTime($criteria->resolveTime($time));
        $criteria->moderated = true;

        $params['comments'] = $this->repository->findByCriteria($criteria);

        $this->repository->hydrate(...$params['comments']);
        $this->repository->hydrateChildren(...$params['comments']);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("parent", options={"mapping": {"parent_comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("comment", subject="entry")
     */
    public function create(
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

    private function getJsonCommentSuccessResponse(EntryComment $comment): Response
    {
        return new JsonResponse(
            [
                'id'   => $comment->getId(),
                'html' => $this->renderView(
                    'entry/comment/_comment.html.twig',
                    [
                        'extra_classes' => 'kbin-comment',
                        'with_parent'   => false,
                        'comment'       => $comment,
                        'level'         => 1,
                        'nested'        => false,
                    ]
                ),
            ]
        );
    }

    private function getEntryCommentPageResponse(
        string $template,
        EntryCommentPageView $criteria,
        FormInterface $form,
        Request $request,
        ?EntryComment $parent = null,

    ): Response {
        if ($request->isXmlHttpRequest()) {
            $this->getJsonFormResponse($form, 'entry/comment/_form.html.twig');
        }

        $comments = $this->repository->findByCriteria($criteria);
        $this->repository->hydrate(...$comments);
        $this->repository->hydrateChildren(...$comments);

        return $this->render(
            $template,
            [
                'magazine' => $criteria->entry->magazine,
                'entry'    => $criteria->entry,
                'comments' => $comments,
                'parent'   => $parent,
                'comment'  => $parent,
                'form'     => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="comment")
     */
    public function edit(
        Magazine $magazine,
        Entry $entry,
        EntryComment $comment,
        Request $request,
    ): Response {
        $dto = $this->manager->createDto($comment);

        $form = $this->getEditForm($dto, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleValidEditRequest($dto, $comment, $request);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonFormResponse($form, 'entry/comment/_form.html.twig');
        }

        $criteria        = new EntryCommentPageView($this->getPageNb($request));
        $criteria->entry = $entry;

        return $this->getEntryCommentPageResponse('entry/comment/edit.html.twig', $criteria, $form, $request, $comment);
    }

    private function getEditForm(EntryCommentDto $dto, EntryComment $comment): FormInterface
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

    private function handleValidEditRequest(EntryCommentDto $dto, EntryComment $comment, Request $request): Response
    {
        $comment = $this->manager->edit($comment, $dto);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonCommentSuccessResponse($comment);
        }

        return $this->redirectToEntry($comment->entry);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="comment")
     */
    public function delete(Magazine $magazine, Entry $entry, EntryComment $comment, Request $request): Response
    {
        $this->validateCsrf('entry_comment_delete', $request->request->get('token'));

        $this->manager->delete($this->getUserOrThrow(), $comment);

        return $this->redirectToEntry($entry);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"comment_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="comment")
     */
    public function purge(Magazine $magazine, Entry $entry, EntryComment $comment, Request $request): Response
    {
        $this->validateCsrf('entry_comment_purge', $request->request->get('token'));

        $this->manager->purge($this->getUserOrThrow(), $comment);

        return $this->redirectToEntry($entry);
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

        $form = $this->createForm(EntryCommentType::class, null, ['action' => $this->generateUrl('entry_comment_create', $routeParams)]);

        return $this->render(
            'entry/comment/_form.html.twig',
            [
                'form' => $form->createView(),
                new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200),
            ]
        );
    }
}
