<?php

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentChangeAdultController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('entry', options: ['mapping' => ['entry_id' => 'id']])]
    #[ParamConverter('comment', options: ['mapping' => ['comment_id' => 'id']])]
    #[IsGranted('moderate', subject: 'comment')]
    public function __invoke(Magazine $magazine, Entry $entry, EntryComment $comment, Request $request): Response
    {
        $this->validateCsrf('change_adult', $request->request->get('token'));

        $comment->isAdult = 'on' === $request->get('adult');

        $this->entityManager->flush();

        return $this->redirectToRefererOrHome($request);
    }
}
