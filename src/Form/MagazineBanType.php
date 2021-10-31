<?php declare(strict_types = 1);

namespace App\Form;

use App\DTO\MagazineBanDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'yyyy-MM-dd HH:mm',
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
