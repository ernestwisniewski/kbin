<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\DataTransformer\UserTransformer;
use Symfony\Component\Form\AbstractType;
use App\Repository\UserRepository;
use App\DTO\ModeratorDto;

class ModeratorType extends AbstractType
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user')
            ->add('submit', SubmitType::class);

        $builder->get('user')->addModelTransformer(
            new UserTransformer($this->userRepository)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ModeratorDto::class,
            ]
        );
    }
}
