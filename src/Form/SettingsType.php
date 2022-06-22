<?php declare(strict_types=1);

namespace App\Form;

use App\DTO\SettingsDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('KBIN_DOMAIN')
            ->add('KBIN_CONTACT_EMAIL')
            ->add('KBIN_META_TITLE')
            ->add('KBIN_META_DESCRIPTION')
            ->add('KBIN_META_KEYWORDS')
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => SettingsDto::class,
            ]
        );
    }
}
