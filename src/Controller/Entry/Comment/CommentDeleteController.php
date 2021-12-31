<?php declare(strict_types = 1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use App\Service\EntryCommentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentDeleteController extends AbstractController
{
    public function __construct(
        private EntryCommentManager $manager,
    ) {
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
     * @IsGranted("delete", subject="comment")
     */
    public function restore(Magazine $magazine, Entry $entry, EntryComment $comment, Request $request): Response
    {
        $this->validateCsrf('entry_comment_restore', $request->request->get('token'));

        $this->manager->restore($this->getUserOrThrow(), $comment);

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

        $this->manager->purge($comment);

        return $this->redirectToEntry($entry);
    }
}
