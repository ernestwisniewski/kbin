<?php

namespace App\Form;

use App\Form\DataTransformer\UserTransformer;
use App\Repository\UserRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use App\DTO\ModeratorDto;

class ModeratorType extends AbstractType
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
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
