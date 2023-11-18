<?php

// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\DataTransformer\BadgeCollectionToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class BadgesType extends AbstractType
{
    public function __construct(private readonly BadgeCollectionToStringTransformer $badgeArrayToStringTransformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->badgeArrayToStringTransformer);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
