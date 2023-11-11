<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\ActivityPubManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ApResendContentController extends AbstractController
{
    public function __construct(private readonly ActivityPubManager $manager)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    public function entry(
        #[MapEntity(id: 'entry_id')]
        Entry $entry,
        Request $request
    ): Response {
        $this->validateCsrf('entry_ap_resend', $request->request->get('token'));

        $this->manager->resend($entry);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function entryComment(
        #[MapEntity(id: 'comment_id')]
        EntryComment $comment,
        Request $request
    ): Response {
        $this->validateCsrf('entry_comment_ap_resend', $request->request->get('token'));

        $this->manager->resend($comment);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function post(
        #[MapEntity(id: 'post_id')]
        Post $post,
        Request $request
    ): Response {
        $this->validateCsrf('post_ap_resend', $request->request->get('token'));

        $this->manager->resend($post);

        return $this->redirectToRefererOrHome($request);
    }

    #[IsGranted('ROLE_ADMIN')]
    public function postComment(
        #[MapEntity(id: 'comment_id')]
        PostComment $comment,
        Request $request
    ): Response {
        $this->validateCsrf('post_comment_ap_resend', $request->request->get('token'));

        $this->manager->resend($comment);

        return $this->redirectToRefererOrHome($request);
    }
}
