<?php

declare(strict_types=1);

namespace App\Form;

use App\PageView\MagazinePageView;
use App\Repository\Criteria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagazinePageViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextType::class, [
                'attr' => [
                    'placeholder' => 'type_search_term',
                ],
                'required' => false,
            ])
            ->add('fields', ChoiceType::class, [
                'choices' => [
                    'filter.fields.only_names' => MagazinePageView::FIELDS_NAMES,
                    'filter.fields.names_and_descriptions' => MagazinePageView::FIELDS_NAMES_DESCRIPTIONS,
                ],
            ])
            ->add('federation', ChoiceType::class, [
                'choices' => [
                    'local_and_federated' => Criteria::AP_ALL,
                    'local' => Criteria::AP_LOCAL,
                ],
            ])
            ->add('adult', ChoiceType::class, [
                'choices' => [
                    'filter.adult.hide' => MagazinePageView::ADULT_HIDE,
                    'filter.adult.show' => MagazinePageView::ADULT_SHOW,
                    'filter.adult.only' => MagazinePageView::ADULT_ONLY,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MagazinePageView::class,
                'csrf_protection' => false,
                'method' => 'GET',
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
