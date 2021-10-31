<?php declare(strict_types = 1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Form\PostType;
use Symfony\Component\HttpFoundation\Response;

class PostFormController extends AbstractController
{
    public function __invoke(string $magazineName): Response
    {
        $form = $this->createForm(PostType::class, null, ['action' => $this->generateUrl('post_create', ['name' => $magazineName])]);

        return $this->render(
            'post/_form.html.twig',
            [
                'form' => $form->createView(),
            ],
            new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200)
        );
    }
}
