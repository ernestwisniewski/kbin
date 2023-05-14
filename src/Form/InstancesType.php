<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\InstancesDto;
use App\Form\DataTransformer\TagTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstancesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('submit', SubmitType::class)
            ->add('instances', TextType::class, [
                'autocomplete' => true,
                'tom_select_options' => [
                    'create' => true,
                    'createOnBlur' => true,
                    'delimiter' => ',',
                ],
            ]);

        $builder->get('instances')->addModelTransformer(
            new TagTransformer()
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => InstancesDto::class,
            ]
        );
    }
}
