<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\Criteria;
use App\Repository\EntryCommentRepository;
use App\Repository\EntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Entity\User;

class UserController extends AbstractController
{
    public function front(User $user, Request $request, UserRepository $userRepository): Response
    {
        return $this->render(
            'user/front.html.twig',
            [
                'user'    => $user,
                'entries' => $userRepository->findPublicActivity((int) $request->get('strona', 1), $user),
            ]
        );
    }

    public function entries(User $user, Request $request, EntryRepository $entryRepository): Response
    {
        $criteria = (new Criteria((int) $request->get('strona', 1)))->setUser($user);

        return $this->render(
            'user/front.html.twig',
            [
                'user'    => $user,
                'entries' => $entryRepository->findByCriteria($criteria),
            ]
        );
    }

    public function comments(User $user, Request $request, EntryCommentRepository $commentRepository): Response
    {
        $criteria = (new Criteria((int) $request->get('strona', 1)))->setUser($user)->setOnlyParent(false);

        $comments = $commentRepository->findByCriteria($criteria);

        $commentRepository->hydrate(...$comments);

        return $this->render(
            'user/comments.html.twig',
            [
                'user'     => $user,
                'comments' => $comments,
            ]
        );
    }
}
