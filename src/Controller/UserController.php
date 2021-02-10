<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\EntryCommentRepository;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\PageView\EntryCommentPageView;
use App\PageView\EntryPageView;
use App\Repository\EntryRepository;
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
        $criteria = (new EntryPageView((int) $request->get('strona', 1)))->showUser($user);

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
        $criteria = (new EntryCommentPageView((int) $request->get('strona', 1)))->showUser($user)->showOnlyParents(false);

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

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("follow", subject="following")
     */
    public function follow(User $following, UserManager $userManager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $userManager->follow($this->getUserOrThrow(), $following);

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("follow", subject="following")
     */
    public function unfollow(User $following, UserManager $userManager, Request $request): Response
    {
        $this->validateCsrf('follow', $request->request->get('token'));

        $userManager->unfollow($this->getUserOrThrow(), $following);

        return $this->redirectToRefererOrHome($request);
    }
}
