<?php declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Service\PostManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostDeleteController extends AbstractController
{
    public function __construct(
        private PostManager $manager,
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="post")
     */
    public function delete(Magazine $magazine, Post $post, Request $request): Response
    {
        $this->validateCsrf('post_delete', $request->request->get('token'));

        $this->manager->delete($this->getUserOrThrow(), $post);

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="post")
     */
    public function restore(Magazine $magazine, Post $post, Request $request): Response
    {
        $this->validateCsrf('post_restore', $request->request->get('token'));

        $this->manager->restore($this->getUserOrThrow(), $post);

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="post")
     */
    public function purge(Magazine $magazine, Post $post, Request $request): Response
    {
        $this->validateCsrf('post_purge', $request->request->get('token'));

        $this->manager->purge($post);

        return $this->redirectToMagazine($magazine);
    }
}
