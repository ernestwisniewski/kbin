<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Contracts\VoteInterface;
use App\Service\VoteManager;

class VoteController extends AbstractController
{
    private VoteManager $voteManager;

    public function __construct(VoteManager $voteManager)
    {
        $this->voteManager = $voteManager;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @isGranted("vote", subject="votable")
     */
    public function __invoke(VoteInterface $votable, int $choice, Request $request): Response
    {
        $this->validateCsrf('vote', $request->request->get('token'));

        $vote = $this->voteManager->vote($choice, $votable, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'choice' => $vote->getChoice(),
                    'upVotes' => $votable->countUpVotes(),
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
