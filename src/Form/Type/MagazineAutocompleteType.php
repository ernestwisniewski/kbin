<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class MagazineAutocompleteType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Magazine::class,
            'choice_label' => 'name',
            'placeholder' => 'select_magazine',
            'filter_query' => function (QueryBuilder $qb, string $query) {
                if ($currentUser = $this->security->getUser()) {
                    $qb
                        ->andWhere(
                            sprintf(
                                'entity.id NOT IN (SELECT IDENTITY(mb.magazine) FROM %s mb WHERE mb.user = :user)',
                                MagazineBlock::class,
                            )
                        )
                        ->setParameter('user', $currentUser);
                }

                if (!$query) {
                    return;
                }

                $qb->andWhere('entity.name LIKE :filter OR entity.title LIKE :filter')
                    ->andWhere('entity.apId IS NULL')
                    ->andWhere('entity.visibility = :visibility')
                    ->setParameter('filter', '%'.$query.'%')
                    ->setParameter('visibility', VisibilityInterface::VISIBILITY_VISIBLE)
                ;
            },
        ]);
    }

    public function getParent(): string
    {
        return ParentEntityAutocompleteType::class;
    }
}
