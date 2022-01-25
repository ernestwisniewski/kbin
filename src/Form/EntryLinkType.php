<?php declare(strict_types=1);

namespace App\Form;

use App\DTO\EntryDto;
use App\Entity\Magazine;
use App\Form\Constraint\ImageConstraint;
use App\Form\DataTransformer\TagTransformer;
use App\Form\EventListener\DisableFieldsOnEntryEdit;
use App\Form\EventListener\ImageListener;
use App\Form\EventListener\RemoveFieldsOnEntryLinkCreate;
use App\Form\Type\BadgesType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryLinkType extends AbstractType
{
    public function __construct(private ImageListener $imageListener)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class)
            ->add('title', TextareaType::class, [
                'required' => true,
            ])
            ->add('tags')
            ->add('comment', TextareaType::class, [
                'mapped'   => false,
                'required' => false,
            ])
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
            ->add(
                'image',
                FileType::class,
                [
                    'constraints' => ImageConstraint::default(),
                    'mapped'      => false,
                ]
            )
            ->add('isAdult', CheckboxType::class)
            ->add('isEng', CheckboxType::class)
            ->add('isOc', CheckboxType::class)
            ->add('submit', SubmitType::class);

        $builder->get('tags')->addModelTransformer(
            new TagTransformer()
        );
        $builder->addEventSubscriber(new RemoveFieldsOnEntryLinkCreate());
        $builder->addEventSubscriber(new DisableFieldsOnEntryEdit());
        $builder->addEventSubscriber($this->imageListener);
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
