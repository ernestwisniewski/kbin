<?php

declare(strict_types=1);

namespace App\Kbin\User\Form;

use App\Kbin\User\DTO\UserDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                    'label' => 'current_password',
                    'mapped' => false,
                    'row_attr' => [
                        'class' => 'password-preview',
                        'data-controller' => 'password-preview',
                    ],
                ])
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'required' => true,
                    'first_options' => [
                        'label' => 'new_password',
                        'row_attr' => [
                            'class' => 'password-preview',
                            'data-controller' => 'password-preview',
                        ],
                    ],
                    'second_options' => [
                        'label' => 'new_password_repeat',
                        'row_attr' => [
                            'class' => 'password-preview',
                            'data-controller' => 'password-preview',
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => UserDto::class,
            ]
        );
    }
}
