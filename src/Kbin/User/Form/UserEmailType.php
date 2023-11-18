<?php

declare(strict_types=1);

namespace App\Kbin\User\Form;

use App\Kbin\User\DTO\UserDto;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEmailType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                TextType::class,
                ['mapped' => false]
            )
            ->add('newEmail', RepeatedType::class, [
                'type' => EmailType::class,
                'mapped' => false,
                'required' => true,
                'first_options' => ['label' => 'new_email'],
                'second_options' => ['label' => 'new_email_repeat'],
            ])
            ->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'row_attr' => [
                    'class' => 'password-preview',
                    'data-controller' => 'password-preview',
                ],
                ])
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
