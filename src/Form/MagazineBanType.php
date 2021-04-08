<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use App\DTO\MagazineBanDto;

class MagazineBanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reason', TextareaType::class)
            ->add(
                'expiredAt',
                DateTimeType::class,
                [
                    'widget'      => 'single_text',
                    'html5'       => false,
                    'format'      => 'yyyy-MM-dd HH:mm',
                    'placeholder' => 'Select a value',
                ]
            )
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => MagazineBanDto::class,
            ]
        );
    }
}
