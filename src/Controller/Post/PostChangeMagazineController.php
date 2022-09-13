<?php declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Repository\MagazineRepository;
use App\Service\PostManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostChangeMagazineController extends AbstractController
{
    public function __construct(
        private PostManager $manager,
        private MagazineRepository $repository
    ) {
    }

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('post', options: ['mapping' => ['post_id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Magazine $magazine, Post $post, Request $request): Response
    {
        $this->validateCsrf('change_magazine', $request->request->get('token'));

        $newMagazine = $this->repository->findOneByName($request->get('new_magazine'));

        $this->manager->changeMagazine($post, $newMagazine);

        return $this->redirectToRefererOrHome($request);
    }
}
