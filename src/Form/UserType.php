<?php

namespace App\Form;

use App\DTO\UserDtoInterface;
use App\Form\EventListener\DisableUsernameFieldOnEdit;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('email')
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type'            => PasswordType::class,
                    'invalid_message' => 'Hasło musi być identyczne.',
                    'required'        => true,
                    'first_options'   => ['label' => 'password'],
                    'second_options'  => ['label' => 'repeat password'],
                ]
            )
            ->add(
                'agreeTerms',
                CheckboxType::class
            )
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber(new DisableUsernameFieldOnEdit());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserDtoInterface::class,
            ]
        );
    }
}
