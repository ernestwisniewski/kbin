<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\Form;

use App\Form\EventListener\DisableFieldsOnMagazineEdit;
use App\Kbin\Magazine\DTO\MagazineDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagazineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('title', TextType::class, ['required' => true])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('rules', TextareaType::class, ['required' => false])
            ->add('isAdult', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber(new DisableFieldsOnMagazineEdit());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MagazineDto::class,
            ]
        );
    }
}
