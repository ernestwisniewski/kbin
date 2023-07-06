<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\CardanoTransactionDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardanoTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('walletAddress')
            ->add('asset', ChoiceType::class, [
                'choices' => [
                    'ADA' => 'ADA',
                ],
            ])
            ->add('amount', NumberType::class)
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'walletAddress' => null,
                'data_class' => CardanoTransactionDto::class,
            ]
        );
    }
}
