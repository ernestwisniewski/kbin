<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\MagazineThemeDto;
use App\Form\Constraint\ImageConstraint;
use App\Form\EventListener\ImageListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagazineThemeType extends AbstractType
{
    public function __construct(private readonly ImageListener $imageListener)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'icon',
                FileType::class,
                [
                    'constraints' => ImageConstraint::default(),
                    'mapped' => false,
                    'required' => false,
                ]
            )
            ->add('customCss', TextareaType::class, ['required' => false])
            ->add('customJs', TextareaType::class, ['required' => false])
            ->add('backgroundImage', ChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'data' => 'none',
                'choices' => [
                    'none' => 'none',
                    'shape1' => 'shape1',
                    'shape2' => 'shape2',
                    'url' => 'url',
                ],
            ])
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber($this->imageListener->setFieldName('icon'));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MagazineThemeDto::class,
            ]
        );
    }
}
