<?php declare(strict_types=1);

namespace App\Form;

use App\DTO\CardanoWalletAddressDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardanoTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mnemonic')
            ->add('walletId')
            ->add('asset', ChoiceType::class, [
                'choices' => [
                    'ADA'=>'ADA',
                ],
            ])
            ->add('amount', NumberType::class)
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'walletId' => null,
                'data_class' => CardanoWalletAddressDto::class,
            ]
        );
    }
}
