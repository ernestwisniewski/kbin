<?php

declare(strict_types=1);

namespace App\Kbin\User\Form;

use App\Form\EventListener\AddFieldsOnUserEdit;
use App\Form\EventListener\AvatarListener;
use App\Form\EventListener\DisableFieldsOnUserEdit;
use App\Form\EventListener\ImageListener;
use App\Kbin\User\DTO\UserDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserBasicType extends AbstractType
{
    public function __construct(
        private readonly ImageListener $imageListener,
        private readonly AvatarListener $avatarListener,
        private readonly AddFieldsOnUserEdit $addAvatarFieldOnUserEdit,
        private readonly DisableFieldsOnUserEdit $disableUsernameFieldOnUserEdit
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['required' => false])
            ->add('about', TextareaType::class, ['required' => false])
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber($this->disableUsernameFieldOnUserEdit);
        $builder->addEventSubscriber($this->addAvatarFieldOnUserEdit);
        $builder->addEventSubscriber($this->avatarListener->setFieldName('avatar'));
        $builder->addEventSubscriber($this->imageListener->setFieldName('cover'));
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
