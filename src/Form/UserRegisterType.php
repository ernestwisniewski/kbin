<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\UserDto;
use App\Form\EventListener\AddFieldsOnUserEdit;
use App\Form\EventListener\CaptchaListener;
use App\Form\EventListener\DisableFieldsOnUserEdit;
use App\Form\EventListener\ImageListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegisterType extends AbstractType
{
    public function __construct(
        private readonly ImageListener $imageListener,
        private readonly AddFieldsOnUserEdit $addAvatarFieldOnUserEdit,
        private readonly DisableFieldsOnUserEdit $disableUsernameFieldOnUserEdit,
        private readonly CaptchaListener $captchaListener,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('email', EmailType::class)
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'required' => true,
                    'first_options' => [
                        'label' => 'password',
                        'row_attr' => [
                            'class' => 'password-preview',
                            'data-controller' => 'password-preview',
                        ],
                    ],
                    'second_options' => [
                        'label' => 'repeat_password',
                        'row_attr' => [
                            'class' => 'password-preview',
                            'data-controller' => 'password-preview',
                        ],
                    ],
                ]
            )
            ->add(
                'agreeTerms',
                CheckboxType::class,
                [
                    'label_html' => true,
                ]
            )
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber($this->disableUsernameFieldOnUserEdit);
        $builder->addEventSubscriber($this->captchaListener);
        $builder->addEventSubscriber($this->addAvatarFieldOnUserEdit);
        $builder->addEventSubscriber($this->imageListener->setFieldName('avatar'));
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
