<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\EntryDto;
use App\Form\Autocomplete\MagazineAutocompleteField;
use App\Form\Constraint\ImageConstraint;
use App\Form\DataTransformer\TagTransformer;
use App\Form\EventListener\DisableFieldsOnEntryEdit;
use App\Form\EventListener\ImageListener;
use App\Form\EventListener\RemoveFieldsOnEntryImageEdit;
use App\Form\Type\BadgesType;
use App\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryImageType extends AbstractType
{
    public function __construct(
        private readonly ImageListener $imageListener,
        private readonly SettingsManager $settingsManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextareaType::class)
            ->add('magazine', MagazineAutocompleteField::class)
            ->add('tags', TextType::class, [
                'required' => false,
                'autocomplete' => true,
                'tom_select_options' => [
                    'create' => true,
                    'createOnBlur' => true,
                    'delimiter' => ',',
                ],
            ])
            ->add(
                'badges',
                BadgesType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'image',
                FileType::class,
                [
                    'constraints' => ImageConstraint::default(),
                    'mapped' => false,
                ]
            )
            ->add('imageAlt', TextareaType::class)
            ->add('isAdult', CheckboxType::class, [
                'required' => false,
            ])
            ->add('isEng', CheckboxType::class, [
                'required' => false,
            ])
            ->add('isOc', CheckboxType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class);

        $builder->get('tags')->addModelTransformer(
            new TagTransformer()
        );
        $builder->addEventSubscriber(new RemoveFieldsOnEntryImageEdit());
        $builder->addEventSubscriber(new DisableFieldsOnEntryEdit());
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
