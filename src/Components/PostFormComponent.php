<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Magazine;
use App\Form\PostType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('post_form')]
class PostFormComponent
{
    public Magazine $magazine;

    public function __construct(private FormFactoryInterface $factory, private UrlGeneratorInterface $router)
    {
    }

    public function getForm(): FormView
    {
        $form = $this->factory->create(
            PostType::class,
            null,
            ['action' => $this->router->generate('post_create', ['name' => $this->magazine->name])]
        );

        return $form->createView();
    }
}
