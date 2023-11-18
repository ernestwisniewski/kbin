<?php

declare(strict_types=1);

namespace App\Kbin\User\Form;

use App\Kbin\User\DTO\UserDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserTwoFactorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'totpCode',
                TextType::class,
                [
                    'label' => '2fa.verify_authentication_code.label',
                    'mapped' => false,
                    'attr' => [
                        'autocomplete' => 'one-time-code',
                        'inputmode' => 'numeric',
                        'pattern' => '[0-9]*',
                    ],
                ],
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
