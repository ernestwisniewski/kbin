<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts\FavouriteInterface;
use App\Service\FavouriteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FavouriteController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    public function __invoke(FavouriteInterface $subject, Request $request, FavouriteManager $manager): Response
    {
        $this->validateCsrf('favourite', $request->request->get('token'));

        $favourite = $manager->toggle($this->getUserOrThrow(), $subject);
        $isFavored = false;

        if($this->getUser()) {
            $isFavored = $subject->isFavored($this->getUser());
        }
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'count' => $subject->favouriteCount,
                    'isFavored' => $isFavored
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
