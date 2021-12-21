<?php declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Event\Post\PostHasBeenSeenEvent;
use App\PageView\PostCommentPageView;
use App\Repository\Criteria;
use App\Repository\PostCommentRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostSingleController extends AbstractController
{
    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     */
    public function __invoke(
        Magazine $magazine,
        Post $post,
        PostCommentRepository $repository,
        EventDispatcherInterface $dispatcher,
        Request $request
    ): Response {
        $criteria = new PostCommentPageView($this->getPageNb($request));
        $criteria->sortOption = Criteria::SORT_NEW;
        $criteria->post = $post;

        $dispatcher->dispatch((new PostHasBeenSeenEvent($post)));

        return $this->render(
            'post/single.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $repository->findByCriteria($criteria),
            ]
        );
    }

}
