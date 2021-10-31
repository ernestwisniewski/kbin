<?php declare(strict_types = 1);

namespace App\Form;

use App\DTO\MagazineDto;
use App\Form\EventListener\DisableFieldsOnMagazineEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagazineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('title')
            ->add('description', TextareaType::class)
            ->add('rules', TextareaType::class)
            ->add('isAdult', CheckboxType::class)
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber(new DisableFieldsOnMagazineEdit());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MagazineDto::class,
            ]
        );
    }
}
