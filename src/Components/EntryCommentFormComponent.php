<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Form\EntryCommentType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('entry_comment_form')]
class EntryCommentFormComponent
{
    public Entry $entry;
    public ?EntryComment $comment = null;

    public function __construct(private FormFactoryInterface $factory, private UrlGeneratorInterface $router)
    {
    }

    public function getForm(): FormView
    {
        $routeParams = [
            'magazine_name' => $this->entry->magazine->name,
            'entry_id'      => $this->entry->getId(),
        ];

        if ($this->comment !== null) {
            $routeParams['comment_id'] = $this->comment->getId();
        }

        return $this->factory->create(
            EntryCommentType::class,
            null,
            ['action' => $this->router->generate('entry_comment_create', $routeParams)]
        )->createView();
    }
}
