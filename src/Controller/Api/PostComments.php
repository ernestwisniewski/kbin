<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\ApiDataProvider\DtoPaginator;
use App\Controller\AbstractController;
use App\Entity\Post;
use App\Factory\PostCommentFactory;
use App\PageView\PostCommentPageView;
use App\Repository\PostCommentRepository;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

class PostComments extends AbstractController
{
    public function __construct(
        private PostCommentRepository $repository,
        private PostCommentFactory $factory,
        private RequestStack $request
    ) {
    }

    public function __invoke(Post $post)
    {
        try {
            $criteria              = new PostCommentPageView((int) $this->request->getCurrentRequest()->get('p', 1));
            $criteria->post        = $post;
            $criteria->onlyParents = false;

            $comments = $this->repository->findByCriteria($criteria);

            $this->repository->hydrate(...$comments);
        } catch (Exception $e) {
            return [];
        }

        $dtos = array_map(fn($comment) => $this->factory->createDto($comment), (array) $comments->getCurrentPageResults());

        return new DtoPaginator($dtos, 0, PostCommentRepository::PER_PAGE, $comments->getNbResults());
    }
}
