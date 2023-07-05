<?php

declare(strict_types=1);

namespace App\Controller\Entry\Comment;

use App\Controller\AbstractController;
use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Magazine;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryCommentFavouriteController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['magazine_name' => 'name'])]
        Magazine $magazine,
        #[MapEntity(mapping: ['entry_id' => 'id'])]
        Entry $entry,
        #[MapEntity(mapping: ['comment_id' => 'id'])]
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
