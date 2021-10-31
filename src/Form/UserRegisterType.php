<?php declare(strict_types = 1);

namespace App\Form;

use App\DTO\UserDto;
use App\Form\EventListener\AddFieldsOnUserEdit;
use App\Form\EventListener\DisableFieldsOnUserEdit;
use App\Form\EventListener\ImageListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegisterType extends AbstractType
{
    public function __construct(
        private ImageListener $imageListener,
        private AddFieldsOnUserEdit $addAvatarFieldOnUserEdit,
        private DisableFieldsOnUserEdit $disableUsernameFieldOnUserEdit
    ) {
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
                    'type' => PasswordType::class,
                    'invalid_message' => 'Hasło musi być identyczne.',
                    'required' => true,
                    'first_options' => ['label' => 'password'],
                    'second_options' => ['label' => 'repeat password'],
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
                'data_class' => UserDto::class,
            ]
        );
    }
}
