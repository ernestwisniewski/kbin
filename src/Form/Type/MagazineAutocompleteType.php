<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Contracts\VisibilityInterface;
use App\Entity\Magazine;
use App\Entity\MagazineBlock;
use App\Kbin\SpamProtection\SpamProtectionCheck;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class MagazineAutocompleteType extends AbstractType
{
    public function __construct(private readonly Security $security, private SpamProtectionCheck $spamProtectionCheck)
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

                $qb->andWhere('LOWER(entity.name) LIKE :filter OR LOWER(entity.title) LIKE :filter')
                    ->andWhere('entity.visibility = :visibility');

                if (($this->spamProtectionCheck)($currentUser, false)) {
                    $qb->orderBy('entity.apId', 'DESC');
                } else {
                    $qb->andWhere('entity.apId IS NULL');
                }

                $qb->setParameter('filter', '%'.$query.'%')
                    ->setParameter('visibility', VisibilityInterface::VISIBILITY_VISIBLE);
            },
        ]);
    }

    public function getParent(): string
    {
        return ParentEntityAutocompleteType::class;
    }
}
