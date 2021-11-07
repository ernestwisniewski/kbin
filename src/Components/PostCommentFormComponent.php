<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Post;
use App\Form\PostCommentType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post_comment_form')]
class PostCommentFormComponent
{
    public Post $post;

    public function __construct(private FormFactoryInterface $factory, private UrlGeneratorInterface $router)
    {
    }

    public function getForm(): FormView
    {
        return $this->factory->create(
            PostCommentType::class,
            null,
            [
                'action' => $this->router->generate(
                    'post_comment_create',
                    ['magazine_name' => $this->post->magazine->name, 'post_id' => $this->post->getId()]
                ),
            ]
        )->createView();
    }
}
