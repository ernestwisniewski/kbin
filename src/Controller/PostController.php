<?php declare(strict_types=1);

namespace App\Controller;

use App\DTO\PostDto;
use App\Entity\Post;
use App\Form\PostType;
use App\PageView\PostCommentPageView;
use App\PageView\PostPageView;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Service\PostManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\EntryArticleType;
use App\Form\EntryLinkType;
use App\Entity\Magazine;
use App\DTO\EntryDto;
use App\Entity\Entry;

class PostController extends AbstractController
{
    private PostManager $postManager;
    private EntityManagerInterface $entityManager;

    public function __construct(PostManager $entryManager, EntityManagerInterface $entityManager)
    {
        $this->postManager   = $entryManager;
        $this->entityManager = $entityManager;
    }


    public function front(?string $sortBy, ?string $time, PostRepository $postRepository, Request $request): Response
    {
        $criteria = (new PostPageView((int) $request->get('strona', 1)));

        if ($sortBy) {
            $criteria->showSortOption($sortBy);
        }

        $posts = $postRepository->findByCriteria($criteria);

        return $this->render(
            'post/front.html.twig',
            [
                'posts' => $posts,
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     */
    public function single(Magazine $magazine, Post $post, PostCommentRepository $commentRepository, Request $request): Response
    {
        $criteria = (new PostCommentPageView((int) $request->get('strona', 1)))->showPost($post);

        return $this->render(
            'post/single.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $commentRepository->findByCriteria($criteria),
            ]
        );
    }

    public function magazine(Magazine $magazine, Post $post, ?string $sortBy, PostCommentRepository $commentRepository, Request $request): Response
    {
        $criteria = (new PostCommentPageView((int) $request->get('strona', 1)))
            ->showMagazine($magazine);

        if ($sortBy) {
            $criteria->showSortOption($sortBy);
        }

//        $posts = $commentRepository->findByCriteria($criteria);

//        $commentRepository->hydrate(...$comments);

        return $this->render(
            'post/front.html.twig',
            [
                'magazine' => $magazine,
                'posts'    => [],
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function create(Magazine $magazine, Request $request): Response
    {
        $postDto = new PostDto();

        $form = $this->createForm(PostType::class, $postDto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $this->postManager->create($postDto, $this->getUserOrThrow());

            return $this->redirectToRoute(
                'post',
                [
                    'magazine_name' => $post->getMagazine()->getName(),
                    'post_id'       => $post->getId(),
                ]
            );
        }

        return $this->redirectToRoute(
            'front_post'
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="entry")
     */
    public function edit(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $entryDto = $this->postManager->createDto($entry);

        $form = $this->createFormByType($entryDto, $entryDto->getType());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->postManager->edit($entry, $entryDto);

            return $this->redirectToRoute(
                'entry_single',
                [
                    'magazine_name' => $magazine->getName(),
                    'entry_id'      => $entry->getId(),
                ]
            );
        }

        return $this->render(
            $this->getTemplateName($entryDto->getType(), true),
            [
                'magazine' => $magazine,
                'entry'    => $entry,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="entry")
     */
    public function delete(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_delete', $request->request->get('token'));

        $this->postManager->delete($entry);

        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->getName(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="entry")
     */
    public function purge(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_purge', $request->request->get('token'));

        $this->postManager->purge($entry);

        return $this->redirectToRoute(
            'front_magazine',
            [
                'name' => $magazine->getName(),
            ]
        );
    }

    private function createFormByType(EntryDto $entryDto, ?string $type): FormInterface
    {
        switch ($type) {
            case Entry::ENTRY_TYPE_ARTICLE:
                return $this->createForm(EntryArticleType::class, $entryDto);
            default:
                return $this->createForm(EntryLinkType::class, $entryDto);
        }
    }
}
