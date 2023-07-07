<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\EntryDto;
use App\Form\Constraint\ImageConstraint;
use App\Form\DataTransformer\TagTransformer;
use App\Form\EventListener\DefaultLanguage;
use App\Form\EventListener\DisableFieldsOnEntryEdit;
use App\Form\EventListener\ImageListener;
use App\Form\Type\BadgesType;
use App\Form\Type\LanguageType;
use App\Form\Type\MagazineAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryLinkType extends AbstractType
{
    public function __construct(
        private readonly ImageListener $imageListener,
        private readonly DefaultLanguage $defaultLanguage,
        private readonly DisableFieldsOnEntryEdit $disableFieldsOnEntryEdit,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class)
            ->add('title', TextareaType::class, [
                'required' => true,
            ])
            ->add('tags', TextType::class, [
                'autocomplete' => true,
                'required' => false,
                'tom_select_options' => [
                    'create' => true,
                    'createOnBlur' => true,
                    'delimiter' => ',',
                ],
            ])
            ->add('body', TextareaType::class, [
                'required' => false,
            ])
            ->add(
                'badges',
                BadgesType::class,
                [
                    'required' => false,
                ]
            )
            ->add('magazine', MagazineAutocompleteType::class)
            ->add(
                'image',
                FileType::class,
                [
                    'required' => false,
                    'constraints' => ImageConstraint::default(),
                    'mapped' => false,
                ]
            )
            ->add('imageUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('imageAlt', TextType::class, [
                'required' => false,
            ])
            ->add('isAdult', CheckboxType::class, [
                'required' => false,
            ])
            ->add('lang', LanguageType::class)
            ->add('isOc', CheckboxType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class);

        $builder->get('tags')->addModelTransformer(
            new TagTransformer()
        );

        $builder->addEventSubscriber($this->defaultLanguage);
        $builder->addEventSubscriber($this->disableFieldsOnEntryEdit);
        $builder->addEventSubscriber($this->imageListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => EntryDto::class,
            ]
        );
    }
}
