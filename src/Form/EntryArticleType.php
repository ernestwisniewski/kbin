<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\EventListener\DisableFieldsOnEntryEdit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use App\Form\Type\BadgesType;
use App\Entity\Magazine;
use App\DTO\EntryDto;

class EntryArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextareaType::class)
            ->add('body', TextareaType::class)
            ->add(
                'badges',
                BadgesType::class,
                [
                    'label' => 'Etykiety',
                ]
            )
            ->add(
                'magazine',
                EntityType::class,
                [
                    'class'        => Magazine::class,
                    'choice_label' => 'name',
                ]
            )
            ->add('isAdult', CheckboxType::class)
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber(new DisableFieldsOnEntryEdit());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => EntryDto::class,
            ]
        );
    }
}
