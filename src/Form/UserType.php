<?php

namespace App\Form;

use App\DTO\Contracts\UserDtoInterface;
use App\Form\EventListener\AddFieldsOnUserEdit;
use App\Form\EventListener\DisableFieldsOnUserEdit;
use App\Form\EventListener\ImageListener;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class UserType extends AbstractType
{
    private ImageListener $imageListener;
    private AddFieldsOnUserEdit $addAvatarFieldOnUserEdit;
    private DisableFieldsOnUserEdit $disableUsernameFieldOnUserEdit;

    public function __construct(
        ImageListener $imageListener,
        AddFieldsOnUserEdit $addAvatarFieldOnUserEdit,
        DisableFieldsOnUserEdit $disableUsernameFieldOnUserEdit
    ) {
        $this->imageListener            = $imageListener;
        $this->addAvatarFieldOnUserEdit = $addAvatarFieldOnUserEdit;
        $this->disableUsernameFieldOnUserEdit = $disableUsernameFieldOnUserEdit;
    }

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

        $builder->addEventSubscriber($this->disableUsernameFieldOnUserEdit);
        $builder->addEventSubscriber($this->addAvatarFieldOnUserEdit);
        $builder->addEventSubscriber($this->imageListener->setFieldName('avatar'));
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
