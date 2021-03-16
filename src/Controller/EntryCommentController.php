<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\PageView\EntryCommentPageView;
use App\Service\EntryCommentManager;
use App\Form\EntryCommentType;
use App\DTO\EntryCommentDto;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Entity\Entry;

class EntryCommentController extends AbstractController
{
    private EntryCommentManager $commentManager;
    private EntityManagerInterface $entityManager;
    private EntryCommentRepository $commentRepository;

    public function __construct(EntryCommentManager $commentManager, EntryCommentRepository $commentRepository, EntityManagerInterface $entityManager)
    {
        $this->commentManager    = $commentManager;
        $this->entityManager     = $entityManager;
        $this->commentRepository = $commentRepository;
    }

    public function front(?Magazine $magazine, ?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = (new EntryCommentPageView((int) $request->get('strona', 1)));

        if ($magazine) {
            $params['magazine'] = $magazine;
            $criteria->showMagazine($magazine);
        }

        if ($time) {
            $criteria->setTime($criteria->translateTime($time));
        }

        $criteria->showSortOption($sortBy);

        $params['comments'] = $this->commentRepository->findByCriteria($criteria);

        $this->commentRepository->hydrate(...$params['comments']);
        $this->commentRepository->hydrateChildren(...$params['comments']);

        return $this->render(
            'entry/comment/front.html.twig',
            $params
        );
    }

    public function subscribed(?string $sortBy, ?string $time, Request $request): Response
    {
        $params   = [];
        $criteria = (new EntryCommentPageView((int) $request->get('strona', 1)));

        if ($time) {
            $criteria->setTime($criteria->translateTime($time));
        }

        $criteria->showSubscribed();

        $criteria->showSortOption($sortBy);

        $params['comments'] = $this->commentRepository->findByCriteria($criteria);

        $this->commentRepository->hydrate(...$params['comments']);
        $this->commentRepository->hydrateChildren(...$params['comments']);

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
        EntryCommentRepository $commentRepository
    ): Response {
        $commentDto = (new EntryCommentDto())->createWithParent($entry, $parent);

        $form = $this->createForm(
            EntryCommentType::class,
            $commentDto,
            [
                'action' => $this->generateUrl(
                    'entry_comment_create',
                    ['magazine_name' => $magazine->getName(), 'entry_id' => $entry->getId(), 'parent_comment_id' => $parent ? $parent->getId() : null]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $this->commentManager->create($commentDto, $this->getUserOrThrow());

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(
                    [
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

            return $this->redirectToRoute(
                'entry_single',
                [
                    'magazine_name' => $magazine->getName(),
                    'entry_id'      => $entry->getId(),
                ]
            );
        }

        $criteria = (new EntryCommentPageView((int) $request->get('strona', 1)))
            ->showEntry($entry);

        $comments = $commentRepository->findByCriteria($criteria);

        $commentRepository->hydrate(...$comments);
        $commentRepository->hydrateChildren(...$comments);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'form' => $this->renderView(
                        'entry/comment/_form.html.twig',
                        [
                            'form' => $form->createView(),
                        ]
                    ),
                ]
            );
        }

        return $this->render(
            'entry/comment/create.html.twig',
            [
                'magazine' => $magazine,
                'entry'    => $entry,
                'comments' => $comments,
                'parent'   => $parent,
                'form'     => $form->createView(),
            ]
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
        EntryCommentRepository $commentRepository
    ): Response {
        $commentDto = $this->commentManager->createDto($comment);

        $form = $this->createForm(EntryCommentType::class, $commentDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentManager->edit($comment, $commentDto);

            return $this->redirectToRoute(
                'entry_single',
                [
                    'magazine_name' => $magazine->getName(),
                    'entry_id'      => $entry->getId(),
                ]
            );
        }

        $criteria = (new EntryCommentPageView((int) $request->get('strona', 1)))
            ->showEntry($entry);

        $comments = $commentRepository->findByCriteria($criteria);

        $commentRepository->hydrate(...$comments);
        $commentRepository->hydrateChildren(...$comments);

        return $this->render(
            'entry/comment/edit.html.twig',
            [
                'magazine' => $magazine,
                'entry'    => $entry,
                'comments' => $comments,
                'comment'  => $comment,
                'form'     => $form->createView(),
            ]
        );
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

        $this->commentManager->delete($comment, !$comment->isAuthor($this->getUserOrThrow()));

        return $this->redirectToRoute(
            'entry_single',
            [
                'magazine_name' => $magazine->getName(),
                'entry_id'      => $entry->getId(),
            ]
        );
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

        $this->commentManager->purge($comment);

        return $this->redirectToRoute(
            'entry_single',
            [
                'magazine_name' => $magazine->getName(),
                'entry_id'      => $entry->getId(),
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

        $form = $this->createForm(EntryCommentType::class, null, ['action' => $this->generateUrl('entry_comment_create', $routeParams)]);

        return $this->render(
            'entry/comment/_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
