<?php

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentFavouriteController extends AbstractController
{
    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('entry', options: ['mapping' => ['entry_id' => 'id']])]
    #[ParamConverter('comment', options: ['mapping' => ['comment_id' => 'id']])]
    public function __invoke(
        Magazine $magazine,
        Entry $entry,
        EntryComment $comment,
        Request $request
    ): Response {
        return $this->render('entry/comment/favourites.html.twig', [
            'magazine' => $magazine,
            'entry' => $entry,
            'comment' => $comment,
            'favourites' => $comment->favourites,
        ]);
    }
}
