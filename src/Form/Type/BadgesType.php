<?php

namespace App\Form\Type;

use App\Form\DataTransformer\BadgeCollectionToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class BadgesType extends AbstractType
{
    private BadgeCollectionToStringTransformer $badgeArrayToStringTransformer;

    public function __construct(BadgeCollectionToStringTransformer $badgeArrayToStringTransformer)
    {
        $this->badgeArrayToStringTransformer = $badgeArrayToStringTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->badgeArrayToStringTransformer);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
