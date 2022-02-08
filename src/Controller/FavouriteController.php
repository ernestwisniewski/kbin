<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts\FavouriteInterface;
use App\Service\FavouriteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FavouriteController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(FavouriteInterface $subject, Request $request, FavouriteManager $manager): Response
    {
        $this->validateCsrf('favourite', $request->request->get('token'));

        $manager->toggle($this->getUserOrThrow(), $subject);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonSuccessResponse();
        }

        return $this->redirectToRefererOrHome($request);
    }
}
