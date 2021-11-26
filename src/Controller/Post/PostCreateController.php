<?php declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Form\PostType;
use App\Service\CloudflareIpResolver;
use App\Service\PostManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PostCreateController extends AbstractController
{
    public function __construct(
        private PostManager $manager,
        private CloudflareIpResolver $ipResolver
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("create_content", subject="magazine")
     */
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $form = $this->createForm(PostType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto           = $form->getData();
            $dto->magazine = $magazine;
            $dto->ip       = $this->ipResolver->resolve();

            if (!$this->isGranted('create_content', $dto->magazine)) {
                throw new AccessDeniedHttpException();
            }

            $post = $this->manager->create($dto, $this->getUserOrThrow());

            return $this->redirectToPost($post);
        }

        return $this->redirectToRefererOrHome($request);
    }
}
