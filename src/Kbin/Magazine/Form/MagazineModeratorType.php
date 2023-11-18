<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Kbin\Magazine\Form;

use App\Form\DataTransformer\UserTransformer;
use App\Kbin\Magazine\DTO\MagazineModeratorDto;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagazineModeratorType extends AbstractType
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', options: [
                'attr' => ['autocomplete' => 'new-password'],
            ])
            ->add('submit', SubmitType::class);

        $builder->get('user')->addModelTransformer(
            new UserTransformer($this->userRepository)
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MagazineModeratorDto::class,
            ]
        );
    }
}
