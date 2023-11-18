<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Post\Form;

use App\Form\Constraint\ImageConstraint;
use App\Form\EventListener\DefaultLanguage;
use App\Form\EventListener\ImageListener;
use App\Form\Type\LanguageType;
use App\Form\Type\MagazineAutocompleteType;
use App\Kbin\Post\DTO\PostDto;
use App\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function __construct(
        private readonly ImageListener $imageListener,
        private readonly DefaultLanguage $defaultLanguage,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('body', TextareaType::class, ['required' => false, 'empty_data' => ''])
            ->add(
                'image',
                FileType::class,
                [
                    'constraints' => ImageConstraint::default(),
                    'mapped' => false,
                    'required' => false,
                ]
            )
            ->add('magazine', MagazineAutocompleteType::class)
            ->add('lang', LanguageType::class)
            ->add('imageUrl', UrlType::class, ['required' => false])
            ->add('imageAlt', TextareaType::class, ['required' => false])
            ->add('isAdult', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber($this->defaultLanguage);
        $builder->addEventSubscriber($this->imageListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => PostDto::class,
            ]
        );
    }
}
