<?php

declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostChangeLangController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[ParamConverter('magazine', options: ['mapping' => ['magazine_name' => 'name']])]
    #[ParamConverter('post', options: ['mapping' => ['post_id' => 'id']])]
    #[IsGranted('moderate', 'post')]
    public function __invoke(Magazine $magazine, Post $post, Request $request): Response
    {
        $post->lang = $request->get('lang')['lang'];

        $this->entityManager->flush();

        return $this->redirectToRefererOrHome($request);
    }
}
