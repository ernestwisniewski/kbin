<?php declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\DTO\PostDto;
use App\Entity\Magazine;
use App\Form\PostType;
use App\Service\PostManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PostCreateController extends AbstractController
{
    public function __construct(
        private PostManager $manager,
    ) {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("create_content", subject="magazine")
     */
    public function __invoke(Magazine $magazine, Request $request): Response
    {
        $dto           = new PostDto();
        $dto->magazine = $magazine;
        $dto->user     = $this->getUserOrThrow();
        $dto->ip       = $request->getClientIp();

        $form = $this->createForm(PostType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('create_content', $magazine)) {
                throw new AccessDeniedHttpException();
            }

            $post = $this->manager->create($dto, $this->getUserOrThrow());

            return $this->redirectToPost($post);
        }

        return $this->redirectToRefererOrHome($request);
    }
}
