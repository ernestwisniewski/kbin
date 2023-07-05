<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Service\PostManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostDeleteController extends AbstractController
{
    public function __construct(private readonly PostManager $manager)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'post')]
    public function delete(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['post_id' => 'id'])]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('post_delete', $request->request->get('token'));

        $this->manager->delete($this->getUserOrThrow(), $post);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('delete', subject: 'post')]
    public function restore(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['post_id' => 'id'])]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('post_restore', $request->request->get('token'));

        $this->manager->restore($this->getUserOrThrow(), $post);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_USER')]
    #[IsGranted('purge', subject: 'post')]
    public function purge(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['post_id' => 'id'])]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('post_purge', $request->request->get('token'));

        $this->manager->purge($post);

        return $this->redirectToMagazine($magazine);
    }
}
