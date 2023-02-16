<?php

namespace App\Form\Autocomplete;

use App\Entity\Magazine;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class MagazineAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Magazine::class,
            'choice_label' => 'name',
            'placeholder' => 'select_magazine',
            'filter_query' => function (QueryBuilder $qb, string $query) {
                if (!$query) {
                    return;
                }

                $qb->andWhere('entity.name LIKE :filter OR entity.title LIKE :filter')
                    ->andWhere('entity.apId IS NULL')
                    ->setParameter('filter', '%'.$query.'%');
            },
        ]);
    }

    public function getParent(): string
    {
        return ParentEntityAutocompleteType::class;
    }
}
