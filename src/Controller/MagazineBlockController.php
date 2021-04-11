<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MagazineRepository;
use App\Repository\EntryRepository;
use Pagerfanta\PagerfantaInterface;
use App\Service\MagazineManager;
use App\PageView\EntryPageView;
use App\Repository\Criteria;
use App\Form\MagazineType;
use App\Entity\Magazine;
use App\DTO\MagazineDto;

class MagazineBlockController extends AbstractController
{
    public function __construct(
        private MagazineManager $manager,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("block", subject="magazine")
     */
    public function block(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $this->manager->block($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBlocked' => true,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("block", subject="magazine")
     */
    public function unblock(Magazine $magazine, Request $request): Response
    {
        $this->validateCsrf('block', $request->request->get('token'));

        $this->manager->unblock($magazine, $this->getUserOrThrow());

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'isBlocked' => false,
                ]
            );
        }

        return $this->redirectToRefererOrHome($request);
    }
}
