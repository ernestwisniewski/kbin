<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Contracts\VoteInterface;
use App\Service\VoteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VoteController extends AbstractController
{
    public function __construct(private VoteManager $manager)
    {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @isGranted("vote", subject="votable")
     */
    public function __invoke(VoteInterface $votable, int $choice, Request $request): Response
    {
        $this->validateCsrf('vote', $request->request->get('token'));

        $vote = $this->manager->vote($choice, $votable, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'choice'    => $vote->choice,
                    'upVotes'   => $votable->countUpVotes(),
                    'downVotes' => $votable->countDownVotes(),
                ]
            );
        }

        if (!$request->headers->has('Referer')) {
            return $this->redirectToRoute('front');
        }

        return $this->redirect($request->headers->get('Referer').'#'.$votable->getId());
    }
}
